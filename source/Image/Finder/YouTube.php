<?php

namespace ic\Framework\Image\Finder;

use ic\Framework\Api\Client\YouTubeClient;
use ic\Framework\Support\Arr;

/**
 * Class YouTube
 *
 * @package ic\Framework\Image\Finder
 */
class YouTube extends Finder
{

    /**
     * @var YouTubeClient
     */
    protected $api;

    /**
     * @inheritdoc
     */
    protected function getRegex(): string
    {
        // http://stackoverflow.com/questions/5830387/php-regex-find-all-youtube-video-ids-in-string
        return '@
        (?:https?://)?
        (?:[0-9A-Z-]+\.)?
        (?:youtu\.be/|youtube(?:-nocookie)?\.com\S*?[^\w\s-])
        ([\w\-]{11})
        (?=[^\w-]|$)
        (?![?=&+%\w.-]*(?:[\'"][^<>]*>|</a>))
        [?=&+%\w.-]*
        @ix';
    }

    /**
     * @inheritdoc
     */
    protected function getImage($id): array
    {
        $data = $this->api->getVideo($id);

        if (!\is_object($data) || !isset($data->items[0]->snippet)) {
            return [];
        }

        $video   = $data->items[0]->snippet;
        $urls    = Arr::pluck($video->thumbnails, 'url', 'width');
        $heights = Arr::pluck($video->thumbnails, 'height', 'width');
        $width   = static::closest(array_keys($heights), $this->width);

        return [
            'src'    => $urls[$width],
            'alt'    => $video->title,
            'width'  => $width,
            'height' => (int)$heights[$width],
        ];
    }

	protected function getApi()
	{
		if ($this->api === null) {
			$this->api = new YouTubeClient();
		}

		return $this->api;
	}

}