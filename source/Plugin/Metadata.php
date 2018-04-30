<?php

namespace ic\Framework\Plugin;

use ic\Framework\Support\Store;

/**
 * Class Metadata
 *
 * @package ic\Framework\Plugin
 */
class Metadata extends Store
{

	/**
	 * Metadata constructor.
	 *
	 * @param string $filename
	 *
	 * @throws \RuntimeException
	 */
	public function __construct(string $filename)
	{
		$defaults = [
			'id'        => 'Text Domain',
			'name'      => 'Plugin Name',
			'version'   => 'Version',
			'languages' => 'Domain Path',
		];

		$metadata = get_file_data($filename, $defaults, 'plugin');
		$metadata = array_filter($metadata);

		if (\count($metadata) === 0) {
			throw new \RuntimeException(sprintf('The plugin metadata is missing, Not found in "%s".', $filename));
		}

		if (\count($metadata) < 4) {
			$keys = implode('", "', array_diff_key($defaults, $metadata));

			throw new \RuntimeException(sprintf('The plugin metadata is incomplete. Missing value(s): "%s".', $keys));
		}

		$this->fill(array_merge([
			'id'        => 'ic-unknown',
			'name'      => 'Unknown',
			'version'   => '0.0.0',
			'languages' => 'languages',
		], $metadata));
	}

	/**
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function __get(string $key): ?string
	{
		return $this->get($key);
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function __set(string $key, string $value)
	{
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function __isset(string $key): bool
	{
		return $this->has($key);
	}

}