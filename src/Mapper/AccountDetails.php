<?php
/**
 * Created for IG Client.
 * User: jakim <pawel@jakimowski.info>
 * Date: 14.03.2018
 */

namespace Jakim\Mapper;


use Jakim\Helper\ArrayHelper;
use Jakim\Model\Account;
use Jakim\Model\Post;

class AccountDetails extends MediaDetails
{
    protected function map(): array
    {
        $class = Post::class;
        $postMap = ArrayHelper::getValue(parent::map(), "$class.item");
        array_walk($postMap, function (&$item) {
            $item = "node.{$item}";
        });

        return [
            Account::class => [
                'envelope' => 'entry_data.ProfilePage.0.graphql.user',
                'item' => [
                    'biography' => 'biography',
                    'externalUrl' => 'external_url',
                    'followedBy' => 'edge_followed_by.count',
                    'follows' => 'edge_follow.count',
                    'fullName' => 'full_name',
                    'id' => 'id',
                    'isPrivate' => 'is_private',
                    'profilePicUrl' => 'profile_pic_url_hd',
                    'username' => 'username',
                    'media' => 'edge_owner_to_timeline_media.count',
                    'isVerified' => 'is_verified',
                    'isBusiness' => 'is_business_account',
                    'businessCategory' => 'business_category_name',
                ],
            ],
            Post::class => [
                'envelope' => 'entry_data.ProfilePage.0.graphql.user.edge_owner_to_timeline_media.edges',
                'item' => $postMap,
            ],
        ];
    }
}