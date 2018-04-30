<?php

namespace ic\Framework\Http;

use ic\Framework\Support\Store;

/**
 * Class InputStore
 *
 * @package ic\Framework\Http
 */
class InputStore extends Store
{

	/**
	 * InputStore constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		$this->fill($items);
	}

	/**
	 * Returns the alphabetic characters of the parameter value.
	 *
	 * @param string $key     The parameter key
	 * @param string $default The default value if the parameter key does not
	 *                        exist
	 *
	 * @return string The filtered value
	 */
	public function getAlpha(string $key, string $default = ''): string
	{
		return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
	}

	/**
	 * Returns the alphabetic characters and digits of the parameter value.
	 *
	 * @param string $key     The parameter key
	 * @param string $default The default value if the parameter key does not
	 *                        exist
	 *
	 * @return string The filtered value
	 */
	public function getAlnum(string $key, string $default = ''): string
	{
		return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default));
	}

	/**
	 * Returns the digits of the parameter value.
	 *
	 * @param string $key     The parameter key
	 * @param string $default The default value if the parameter key does not
	 *                        exist
	 *
	 * @return string The filtered value
	 */
	public function getDigits(string $key, string $default = ''): string
	{
		// we need to remove - and + because they're allowed in the filter
		return str_replace([
			'-',
			'+',
		], '', $this->filter($key, $default, FILTER_SANITIZE_NUMBER_INT));
	}

	/**
	 * Returns the parameter value converted to integer.
	 *
	 * @param string $key     The parameter key
	 * @param int    $default The default value if the parameter key does not
	 *                        exist
	 *
	 * @return int The filtered value
	 */
	public function getInt(string $key, $default = 0): int
	{
		return (int) $this->get($key, $default);
	}

	/**
	 * Returns the parameter value converted to boolean.
	 *
	 * @param string $key     The parameter key
	 * @param mixed  $default The default value if the parameter key does not
	 *                        exist
	 *
	 * @return bool The filtered value
	 */
	public function getBoolean(string $key, $default = false): bool
	{
		return $this->filter($key, $default, FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Filter key.
	 *
	 * @param string $key     Key
	 * @param mixed  $default Default = null
	 * @param int    $filter  FILTER_* constant
	 * @param mixed  $options Filter options
	 *
	 * @see http://php.net/manual/en/function.filter-var.php
	 *
	 * @return mixed
	 */
	public function filter(string $key, $default = null, int $filter = FILTER_DEFAULT, array $options = [])
	{
		$value = $this->get($key, $default);
		// Always turn $options into an array - this allows filter_var option shortcuts.
		if (!\is_array($options) && $options) {
			$options = ['flags' => $options];
		}
		// Add a convenience check for arrays.
		if (\is_array($value) && !isset($options['flags'])) {
			$options['flags'] = FILTER_REQUIRE_ARRAY;
		}

		return filter_var($value, $filter, $options);
	}

}