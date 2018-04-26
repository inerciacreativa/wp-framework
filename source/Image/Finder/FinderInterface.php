<?php

namespace ic\Framework\Image\Finder;

use ic\Framework\Image\ImageCollection;

/**
 * Interface FinderInterface
 *
 * @package ic\Framework\Image\Finder
 */
interface FinderInterface
{

    /**
     * @param string               $html
     * @param ImageCollection|null $collection
     * @param int                  $limit
     * @param int                  $width
     *
     * @return ImageCollection
     */
    public function search($html, ImageCollection $collection = null, $limit = 0, $width = 720);

}