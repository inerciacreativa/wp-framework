<?php

namespace ic\Framework\Data;

use InvalidArgumentException;
use ic\Framework\Support\Arr;
use ic\Framework\Support\Data;

/**
 * Class JsonFile
 *
 * @package ic\Framework\Data
 */
class JsonFile
{

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * JsonFile constructor.
	 *
	 * @param string $file
	 */
	public function __construct(string $file)
	{
		if (file_exists($file) && is_file($file)) {
			try {
				$this->data = Data::decode(file_get_contents($file), true);
			} catch (InvalidArgumentException $exception) {
				$this->data = [];
			}
		}
	}

	/**
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		if (empty($this->data)) {
			return $default;
		}

		return $this->data[$key] ?? Arr::get($this->data, $key, $default);
	}

	/**
	 * @return array|object
	 */
	public function all(): array
	{
		return $this->data;
	}

}
