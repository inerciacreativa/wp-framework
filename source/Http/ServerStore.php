<?php

namespace ic\Framework\Http;

/**
 * Class ServerStore
 *
 * @package ic\Framework\Http
 */
class ServerStore extends InputStore
{

	/**
	 * @return array
	 */
	public function getHeaders(): array
	{
		$headers        = [];
		$contentHeaders = [
			'CONTENT_LENGTH' => true,
			'CONTENT_MD5'    => true,
			'CONTENT_TYPE'   => true,
		];

		foreach ($this->all() as $key => $value) {
			if (0 === strpos($key, 'HTTP_')) {
				$headers[substr($key, 5)] = $value;
			} else if (isset($contentHeaders[$key])) {
				// CONTENT_* are not prefixed with HTTP_
				$headers[$key] = $value;
			}
		}

		return $headers;
	}

}