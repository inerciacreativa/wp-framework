<?php

namespace ic\Framework\Image\Finder;

use ic\Framework\Image\ImageCollection;

/**
 * Class Finder
 *
 * @package ic\Framework\Image\Finder
 */
abstract class Finder implements FinderInterface
{

    /**
     * @var int
     */
    protected $width;

    /**
     * @inheritdoc
     */
    public function search($html, ImageCollection $collection = null, $limit = 0, $width = 720)
    {
        $this->width = $width;

        if ($collection === null) {
            $collection = new ImageCollection();
        }

        if (!preg_match_all($this->getRegex(), $html, $patterns)) {
            return $collection;
        }

        $images = array_unique($patterns[1]);

        foreach ($images as $id) {
            $image = $this->getImage($id);

            if (empty($image)) {
                continue;
            }

            $collection->append($image);

            if ($limit > 0 && $collection->count() === $limit) {
                break;
            }
        }

        return $collection;
    }

    /**
     * @return string
     */
    abstract protected function getRegex(): string;

    /**
     * @param string $id
     *
     * @return array
     */
    abstract protected function getImage($id): array;

    /**
     * @param array $widths
     * @param int   $search
     *
     * @return int
     */
    protected static function closest(array $widths, $search): int
    {
        $closest = null;
        foreach ($widths as $width) {
            if ($closest === null || abs($search - $closest) > abs($width - $search)) {
                $closest = $width;
            }
        }

        return $closest;
    }

}