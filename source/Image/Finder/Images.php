<?php

namespace ic\Framework\Image\Finder;

use ic\Framework\Image\ImageCollection;
use ic\Framework\Support\Http;

/**
 * Class Images
 *
 * @package ic\Framework\Image\Finder
 */
class Images implements FinderInterface
{

    /**
     * @var array
     */
    protected static $forbidenSources = [
        'feeds.feedburner.com',
        'blogger.googleusercontent.com',
        'feedads.g.doubleclick.net',
        'stats.wordpress.com',
        'feeds.wordpress.com',
    ];

    /**
     * @var Http
     */
    protected $home;

    /**
     * Images constructor.
     */
    public function __construct()
    {
        $this->home = Http::home();
    }

    /**
     * @inheritdoc
     */
    public function search($html, ImageCollection $collection = null, $limit = 0, $width = 720)
    {
        if ($collection === null) {
            $collection = new ImageCollection();
        }

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        /** @var $image \DOMElement */
        foreach ($dom->getElementsByTagName('img') as $image) {
            if (!$image->hasAttribute('src')) {
                continue;
            }

            $source = Http::make($image->getAttribute('src'));

            if (\in_array($source->host, static::$forbidenSources, false)) {
                continue;
            }

            $collection->append([
                'src'    => $source->get(),
                'id'     => $this->getId($image, $source),
                'alt'    => $this->getAttribute($image, 'alt', ''),
                'width'  => $this->getAttribute($image, 'width', 0),
                'height' => $this->getAttribute($image, 'height', 0),
            ]);

            if ($limit > 0 && $collection->count() === $limit) {
                break;
            }
        }

        return $collection;
    }

    /**
     * @param \DOMElement $image
     * @param Http        $source
     *
     * @return int
     */
    protected function getId(\DOMElement $image, Http $source)
    {
        if (!$image->hasAttribute('class')) {
            return 0;
        }

        if (empty($source->host) || $source->host === $this->home->host) {
            if (preg_match('/wp-image-([\d]*)/i', $image->getAttribute('class'), $id)) {
                return (int)$id[1];
            }
        }

        return 0;
    }

    /**
     * @param \DOMElement $image
     * @param string      $attribute
     * @param mixed       $default
     *
     * @return mixed
     */
    protected function getAttribute(\DOMElement $image, $attribute, $default)
    {
        $result = $default;

        if ($image->hasAttribute($attribute)) {
            $result = $image->getAttribute($attribute);
            settype($result, \gettype($default));
        }

        return $result;
    }

}