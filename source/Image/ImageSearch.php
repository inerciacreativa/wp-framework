<?php

namespace ic\Framework\Image;

use ic\Framework\Image\Finder\FinderInterface;
use ic\Framework\Image\Finder\Images;
use ic\Framework\Image\Finder\Ted;
use ic\Framework\Image\Finder\Vimeo;
use ic\Framework\Image\Finder\YouTube;

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
		Ted::class,
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
	public function __construct(string $html, bool $videos = false)
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
	public static function make(string $html, bool $videos = false): ImageCollection
	{
		$search = new static($html, $videos);

		return $search->get();
	}

	/**
	 * @return ImageCollection
	 */
	public function get(): ImageCollection
	{
		$collection = new ImageCollection();

		foreach ($this->finders as $finder) {
			/** @var FinderInterface $finder */
			$finder     = new $finder;
			$collection = $finder->search($this->html, $collection);

			if (!$this->videos && $collection->count() > 0) {
				break;
			}
		}

		return $collection;
	}

}