<?php

namespace ic\Framework\Api\Client;

use ic\Framework\Api\Auth\OAuthToken;
use ic\Framework\Framework;
use ic\Framework\Support\Collection;

/**
 * Class VimeoClient
 *
 * @package ic\Framework\Api\Client
 */
class VimeoClient extends Client
{

    /**
     * @inheritdoc
     */
    public function getAuth()
    {
        $options = Framework::getInstance()->getOptions();

        return new OAuthToken(
            $this->getEndpoint() . '/oauth/authorize/client',
            $options->get('vimeo.credentials.id'),
            $options->get('vimeo.credentials.secret'),
            ['Accept' => 'application/vnd.vimeo.*+json; version=' . $this->getVersion()]
        );
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Vimeo';
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return '3.2';
    }

    /**
     * @inheritdoc
     */
    public function getDomain()
    {
        return 'https://vimeo.com';
    }

    /**
     * @inheritdoc
     */
    public function getEndpoint()
    {
        return 'https://api.vimeo.com';
    }

    /**
     * @inheritdoc
     */
    public function getMethods()
    {
        return Collection::make([
            ['name' => 'video', 'type' => 'video', 'label' => __('Video', 'ic-framework'), 'callback' => 'getVideo'],
            ['name' => 'user', 'type' => 'info', 'label' => __('User', 'ic-framework'), 'callback' => 'getUser'],
            ['name' => 'userVideos', 'type' => 'video', 'label' => __('User Videos', 'ic-framework'), 'callback' => 'getUserVideos'],
            ['name' => 'channel', 'type' => 'info', 'label' => __('Channel', 'ic-framework'), 'callback' => 'getChannel'],
            ['name' => 'channelVideos', 'type' => 'video', 'label' => __('Channel Videos', 'ic-framework'), 'callback' => 'getChannelVideos'],
            ['name' => 'group', 'type' => 'info', 'label' => __('Group', 'ic-framework'), 'callback' => 'getGroup'],
            ['name' => 'groupVideos', 'type' => 'video', 'label' => __('Group Videos', 'ic-framework'), 'callback' => 'getGroupVideos'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getUrls()
    {
        return [
            'video'         => '/videos/#ID#',
            'userVideos'    => '/#ID#',
            'channelVideos' => '/channels/#ID#',
            'groupVideos'   => '/groups/#ID#',
            'embed'         => 'https://player.vimeo.com/video/#ID#',
        ];
    }

    /**
     * @param string $videoId
     *
     * @return null|\stdClass
     */
    public function getVideo($videoId)
    {
        return $this->api()->get("videos/$videoId");
    }

    /**
     * @param string $userId
     *
     * @return null|\stdClass
     */
    public function getUser($userId)
    {
        return $this->api()->get("users/$userId");
    }

    /**
     * @param string $userId
     * @param int    $maxResults
     *
     * @return null|\stdClass
     */
    public function getUserVideos($userId, $maxResults = 10)
    {
        return $this->api()->get("users/$userId/videos", ['per_page' => $maxResults]);
    }

    /**
     * @param string $channelId
     *
     * @return null|\stdClass
     */
    public function getChannel($channelId)
    {
        return $this->api()->get("channels/$channelId");
    }

    /**
     * @param string $channelId
     * @param int    $maxResults
     *
     * @return null|\stdClass
     */
    public function getChannelVideos($channelId, $maxResults = 10)
    {
        return $this->api()->get("channels/$channelId/videos", ['per_page' => $maxResults]);
    }

    /**
     * @param string $groupId
     *
     * @return null|\stdClass
     */
    public function getGroup($groupId)
    {
        return $this->api()->get("groups/$groupId");
    }

    /**
     * @param string $groupId
     * @param int    $maxResults
     *
     * @return null|\stdClass
     */
    public function getGroupVideos($groupId, $maxResults = 10)
    {
        return $this->api()->get("groups/$groupId/videos", ['per_page' => $maxResults]);
    }

}