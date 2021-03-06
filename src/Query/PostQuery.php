<?php
/**
 * Created for IG Client.
 * User: jakim <pawel@jakimowski.info>
 * Date: 23.03.2018
 */

namespace Jakim\Query;


use Jakim\Base\Query;
use jakim\ig\Endpoint;
use Jakim\Mapper\MediaDetails;
use Jakim\Model\Post;

class PostQuery extends Query
{
    protected $mediaDetailsMapper;

    public function __construct($httpClient, MediaDetails $mediaDetailsMapper = null)
    {
        parent::__construct($httpClient);
        $this->mediaDetailsMapper = $mediaDetailsMapper ?: new MediaDetails();
    }

    public function findOne(string $shortCode, $relations = false): Post
    {
        $url = Endpoint::mediaDetails($shortCode);
        $data = $this->fetchContentAsArray($url);

        $this->throwEmptyContentExceptionIfEmpty($data);

        $data = $this->mediaDetailsMapper->normalizeData(Post::class, $data);

        return $this->mediaDetailsMapper->populate(Post::class, $data, $relations);
    }
}