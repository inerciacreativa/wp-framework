<?php

namespace ic\Framework\Http;

/**
 * Class Referer
 *
 * @package ic\Framework\Http
 */
class Referer extends Request
{

	/**
	 * RefererStore constructor.
	 *
	 * @param $referer
	 */
	public function __construct(string $referer)
	{
		$items = [];

		if (!empty($referer)) {
			$query = @parse_url($referer, PHP_URL_QUERY);

			if (!empty($query)) {
				$items = wp_parse_args($query);
			}
		}

		parent::__construct($items);
	}

}