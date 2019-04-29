<?php

namespace ic\Framework\Image;

use ic\Framework\Data\CollectionIterator;

/**
 * Class ImageIterator
 *
 * @package ic\Framework\Image
 */
class ImageIterator extends CollectionIterator
{

	/**
	 * @return Image
	 */
	public function current(): Image
	{
		$image = parent::current();

		return new Image($image);
	}

	/**
	 *
	 */
	public function asort(): void
	{
		$this->uasort(static function ($a, $b) {
			$a = ($a['width'] * 10) + $a['height'];
			$b = ($b['width'] * 10) + $b['height'];

			if ($a === $b) {
				return 0;
			}

			return ($a > $b) ? -1 : 1;
		});
	}

}