<?php

namespace ic\Framework\Support;

/**
 * Class Cache
 *
 * @package ic\Framework\Support
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
	 *
	 * @return mixed
	 */
	public static function get(string $name)
	{
		return get_transient($name);
	}

	/**
	 * @param string    $name
	 * @param mixed     $value
	 * @param null|bool $autoload
	 *
	 * @return bool
	 */
	public static function push(string $name, $value, bool $autoload = null): bool
	{
		return update_option($name, $value, $autoload);
	}

	/**
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public static function pull(string $name, $default = false)
	{
		return get_option($name, $default);
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

}