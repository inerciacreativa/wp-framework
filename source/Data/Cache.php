<?php

namespace ic\Framework\Data;

/**
 * Class Cache
 *
 * @package ic\Framework\Data
 */
class Cache
{

	/**
	 * @param string $name
	 * @param mixed  $value
	 * @param int    $expiration
	 *
	 * @return bool
	 */
	public static function set(string $name, $value, int $expiration = 0): bool
	{
		return set_transient($name, $value, $expiration);
	}

	/**
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public static function get(string $name, $default = false)
	{
		$value = get_transient($name);

		return ($value === false) ? $default : $value;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function forget(string $name): bool
	{
		return delete_option($name);
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public static function has(string $key): bool
	{
		return self::get($key) !== false;
	}

}
