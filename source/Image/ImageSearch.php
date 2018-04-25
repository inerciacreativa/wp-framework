<?php

namespace ic\Framework\Image;

use ic\Framework\Image\Finder\FinderInterface;

use ic\Framework\Image\Finder\Images;
use ic\Framework\Image\Finder\YouTube;
use ic\Framework\Image\Finder\Vimeo;
use ic\Framework\Image\Finder\Ted;

/**
 * Class ImageSearch
 *
 * @package ic\Framework\Image
 */
class ImageSearch
{

    /**
     * @var FinderInterface[]
     */
    protected $finders = [
        Images::class,
        YouTube::class,
        Vimeo::class,
        Ted::class
    ];

    /**
     * @var string
     */
    protected $html;

    /**
     * @var bool
     */
    protected $videos;

    /**
     * @param string $html
     * @param bool   $videos
     */
    public function __construct($html, $videos = false)
    {
        $this->html   = $html;
        $this->videos = $videos;
    }

    /**
     * @param string $html
     * @param bool   $videos
     *
     * @return ImageCollection
     */
    public static function make($html, $videos = false)
    {
        $search = new static($html, $videos);

        return $search->get();
    }

    /**
     * @return ImageCollection
     */
    public function get()
    {
        $collection = new ImageCollection();

        foreach ($this->finders as $finder) {
            $collection = $this->finder($finder)->search($this->html, $collection);

            if (!$this->videos && $collection->count() > 0) {
                break;
            }
        }

        return $collection;
    }

    /**
     * @param $class
     *
     * @return FinderInterface
     */
    protected function finder($class)
    {
        return new $class;
    }

}