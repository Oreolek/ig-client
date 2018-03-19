<?php
/**
 * Created for IG Client.
 * User: jakim <pawel@jakimowski.info>
 * Date: 14.03.2018
 */

namespace Jakim\Query;


use Jakim\Base\Query;
use Jakim\Helper\JsonHelper;
use jakim\ig\Endpoint;
use Jakim\Mapper\AccountDetails;
use Jakim\Mapper\AccountMedia;
use Jakim\Model\Account;
use Jakim\Model\Post;

class AccountQuery extends Query
{
    const MAX_POSTS_PER_PAGE = 100;
    public $postsPerPage = 100;

    protected $accountDetailsMapper;
    protected $accountMediaMapper;

    public function __construct($httpClient, AccountDetails $accountDetailsMapper = null, AccountMedia $accountMediaMapper = null)
    {
        parent::__construct($httpClient);
        $this->accountDetailsMapper = $accountDetailsMapper ?? new AccountDetails();
        $this->accountMediaMapper = $accountMediaMapper ?? new AccountMedia();
    }

    public function findOne(string $username): Account
    {
        $url = Endpoint::accountDetails($username);

        $res = $this->httpClient->get($url);
        $content = $res->getBody()->getContents();

        $array = JsonHelper::decode($content);
        $data = $this->accountDetailsMapper->normalizeData(Account::class, $array);

        return $this->accountDetailsMapper->populate(Account::class, $data);
    }

    /**
     * @param string $username
     * @param int $limit Max 12, for more see findPosts()
     * @return \Generator
     *
     * @see \Jakim\Query\AccountQuery::findPosts
     */
    public function findLastPosts(string $username, int $limit = 12)
    {
        $url = Endpoint::accountDetails($username);

        $res = $this->httpClient->get($url);
        $content = $res->getBody()->getContents();
        $data = JsonHelper::decode($content);

        $items = $this->accountDetailsMapper->normalizeData(Post::class, $data);

        $n = 0;
        foreach ($items as $item) {
            $model = $this->accountDetailsMapper->populate(Post::class, $item);

            yield $model;

            if (++$n >= $limit) {
                break;
            }
        }
    }

    public function findPosts(string $username, int $limit = 100)
    {
        if ($limit <= 12) {
            return $this->findLastPosts($username, $limit);
        }

        $account = $this->findOne($username);

        $n = 0;
        $nextPage = '';
        $this->postsPerPage = (int)$this->postsPerPage > self::MAX_POSTS_PER_PAGE ? self::MAX_POSTS_PER_PAGE : $this->postsPerPage;

        while ($nextPage !== null) {
            $url = Endpoint::accountMedia($account->id, $this->postsPerPage, [
                'variables' => ['after' => $nextPage],
            ]);

            $res = $this->httpClient->get($url);
            $content = $res->getBody()->getContents();
            $data = JsonHelper::decode($content);

            $nextPage = $this->accountMediaMapper->nextPage($data);

            $items = $this->accountMediaMapper->normalizeData(Post::class, $data);

            foreach ($items as $item) {

                yield $this->accountMediaMapper->populate(Post::class, $item);

                if (++$n >= $limit) {
                    break 2;
                }
            }
        }
    }
}