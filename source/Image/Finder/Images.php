<?php

namespace ic\Framework\Image\Finder;

use ic\Framework\Dom\Document;
use ic\Framework\Dom\Element;
use ic\Framework\Image\ImageCollection;
use ic\Framework\Support\Url;

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
	 * @var Url
	 */
	protected $home;

	/**
	 * Images constructor.
	 */
	public function __construct()
	{
		$this->home = Url::parse(home_url());
	}

	/**
	 * @inheritdoc
	 */
	public function search(string $html, ImageCollection $collection = null, int $limit = 0, int $width = 720): ImageCollection
	{
		if ($collection === null) {
			$collection = new ImageCollection();
		}

		$dom = new Document();
		$dom->loadMarkup($html);

		/** @var $image Element */
		foreach ($dom->getElementsByTagName('img') as $image) {
			if (!$image->hasAttribute('src')) {
				continue;
			}

			$source = Url::parse($image->getAttribute('src'));

			if (in_array($source->host, static::$forbidenSources, false)) {
				continue;
			}

			$collection->append([
				'src'    => $source->render(),
				'id'     => $this->getId($image, $source),
				'alt'    => $image->getAttribute('alt'),
				'width'  => $image->getAttribute('width', 0),
				'height' => $image->getAttribute('height', 0),
			]);

			if ($limit > 0 && $collection->count() === $limit) {
				break;
			}
		}

		return $collection;
	}

	/**
	 * @param Element $image
	 * @param Url     $source
	 *
	 * @return int
	 */
	protected function getId(Element $image, Url $source): int
	{
		if (!$image->hasAttribute('class')) {
			return 0;
		}

		if (empty($source->host) || $source->host === $this->home->host) {
			if (preg_match('/wp-image-([\d]*)/i', $image->getAttribute('class'), $id)) {
				return (int) $id[1];
			}
		}

		return 0;
	}

}
