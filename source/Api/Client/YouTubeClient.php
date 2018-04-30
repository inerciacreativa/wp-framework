<?php

namespace ic\Framework\Api\Client;

use ic\Framework\Framework;
use ic\Framework\Api\Auth\OAuthKey;
use ic\Framework\Support\Collection;

/**
 * Class YouTubeClient
 *
 * @package ic\Framework\Api\Client
 */
class YouTubeClient extends Client
{

    /**
     * @inheritdoc
     */
    public function getAuth()
    {
        $options = Framework::instance()->getOptions();

        return new OAuthKey([
            'key'  => $options->get('youtube.credentials.key'),
            'part' => 'snippet',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'YouTube';
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return '3';
    }

    /**
     * @inheritdoc
     */
    public function getDomain()
    {
        return 'https://www.youtube.com';
    }

    /**
     * @inheritdoc
     */
    public function getEndpoint()
    {
        return 'https://www.googleapis.com/youtube/v' . $this->getVersion();
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
            ['name' => 'playlist', 'type' => 'info', 'label' => __('Playlist', 'ic-framework'), 'callback' => 'getPlaylist'],
            ['name' => 'playlistVideos', 'type' => 'video', 'label' => __('Playlist Videos', 'ic-framework'), 'callback' => 'getPlaylistVideos'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getUrls()
    {
        return [
            'video'          => '/watch?v=#ID#',
            'userVideos'     => '/user/#ID#',
            'channelVideos'  => '/channel/#ID#',
            'playlistVideos' => '/playlist?list=#ID#',
            'embed'          => '/embed/#ID#?version=' . $this->getVersion(),
        ];
    }

    /**
     * @param string $videoId
     *
     * @return null|\stdClass
     */
    public function getVideo($videoId)
    {
        return $this->api()->get('videos', ['id' => $videoId]);
    }

    /**
     * @param string $userId
     *
     * @return null|\stdClass
     */
    public function getUser($userId)
    {
        return $this->api()->get('channels', ['forUsername' => $userId]);
    }

    /**
     * @param string $userId
     * @param int    $maxResults
     *
     * @return null|\stdClass
     */
    public function getUserVideos($userId, $maxResults = 10)
    {
        $channel = $this->api()->get('channels', ['forUsername' => $userId, 'part' => 'contentDetails']);

        if (!$channel) {
            return null;
        }

        $playlistId = $channel->items[0]->contentDetails->relatedPlaylists->uploads;

        return $this->getPlaylistVideos($playlistId, $maxResults);
    }

    /**
     * @param string $channelId
     *
     * @return null|\stdClass
     */
    public function getChannel($channelId)
    {
        return $this->api()->get('channels', ['id' => $channelId]);
    }

    /**
     * @param string $channelId
     * @param int    $maxResults
     *
     * @return null|\stdClass
     */
    public function getChannelVideos($channelId, $maxResults = 10)
    {
        $channel = $this->api()->get('channels', ['id' => $channelId, 'part' => 'contentDetails']);

        if (!$channel) {
            return null;
        }

        $playlistId = $channel->items[0]->contentDetails->relatedPlaylists->uploads;

        return $this->getPlaylistVideos($playlistId, $maxResults);
    }

    /**
     * @param string $playlistId
     *
     * @return null|\stdClass
     */
    public function getPlaylist($playlistId)
    {
        return $this->api()->get('playlists', ['id' => $playlistId]);
    }

    /**
     * @param string $playlistId
     * @param int    $maxResults
     *
     * @return null|\stdClass
     */
    public function getPlaylistVideos($playlistId, $maxResults = 10)
    {
        $playlist = $this->api()->get('playlistItems', ['playlistId' => $playlistId, 'maxResults' => $maxResults]);

        if (!$playlist) {
            return null;
        }

        $videos = [];
        foreach ($playlist->items as $id => $item) {
            $videos[] = $item->snippet->resourceId->videoId;
        }

        if (!empty($videos)) {
            $durations = $this->api()->get('videos', ['id' => implode(',', $videos), 'part' => 'contentDetails']);

            if ($durations) {
                foreach ($playlist->items as $id => $item) {
                    $playlist->items[$id]->snippet->duration = $durations->items[$id]->contentDetails->duration;
                }
            }
        }

        return $playlist;
    }

}