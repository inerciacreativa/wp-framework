<?php

namespace ic\Framework\Image;

/**
 * Class ImageIterator
 *
 * @package ic\Framework\Image
 */
class ImageIterator extends \ArrayIterator
{

    /**
     * @return Image
     */
    public function current()
    {
        $image = parent::current();

        return new Image($image);
    }

    /**
     * 
     */
    public function asort()
    {
        $this->uasort(function($a, $b) {
            $a = ($a['width'] * 10) + $a['height'];
            $b = ($b['width'] * 10) + $b['height'];

            if ($a == $b) {
                return 0;
            }

            return ($a > $b) ? -1 : 1;
        });
    }

}