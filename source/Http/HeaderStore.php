<?php

namespace ic\Framework\Http;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Class HeaderStore
 *
 * @package ic\Framework\Http
 */
class HeaderStore implements Countable, IteratorAggregate
{

	/**
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Constructor.
	 *
	 * @param array $headers An array of HTTP headers
	 */
	public function __construct(array $headers = [])
	{
		foreach ($headers as $key => $values) {
			$this->set($key, $values);
		}
	}

	/**
	 * Returns the headers.
	 *
	 * @return array An array of headers
	 */
	public function all(): array
	{
		return $this->headers;
	}

	/**
	 * Returns the parameter keys.
	 *
	 * @return array An array of parameter keys
	 */
	public function keys(): array
	{
		return array_keys($this->headers);
	}

	/**
	 * Replaces the current HTTP headers by a new set.
	 *
	 * @param array $headers An array of HTTP headers
	 */
	public function replace(array $headers = [])
	{
		$this->headers = [];
		$this->add($headers);
	}

	/**
	 * Adds new headers the current HTTP headers set.
	 *
	 * @param array $headers An array of HTTP headers
	 */
	public function add(array $headers)
	{
		foreach ($headers as $key => $values) {
			$this->set($key, $values);
		}
	}

	/**
	 * Returns a header value by name.
	 *
	 * @param string $key     The header name
	 * @param mixed  $default The default value
	 * @param bool   $first   Whether to return the first value or all header
	 *                        values
	 *
	 * @return string|array The first header value if $first is true, an array
	 *                      of values otherwise
	 */
	public function get(string $key, $default = null, $first = true)
	{
		$key = str_replace('_', '-', strtolower($key));
		if (!array_key_exists($key, $this->headers)) {
			if (null === $default) {
				return $first ? null : [];
			}

			return $first ? $default : [$default];
		}
		if ($first) {
			return count($this->headers[$key]) ? $this->headers[$key][0] : $default;
		}

		return $this->headers[$key];
	}

	/**
	 * Sets a header by name.
	 *
	 * @param string       $key     The key
	 * @param string|array $values  The value or an array of values
	 * @param bool         $replace Whether to replace the actual value or not
	 *                              (true by default)
	 */
	public function set(string $key, $values, bool $replace = true): void
	{
		$key    = str_replace('_', '-', strtolower($key));
		$values = array_values((array) $values);

		if (true === $replace || !isset($this->headers[$key])) {
			$this->headers[$key] = $values;
		} else {
			$this->headers[$key] = array_merge($this->headers[$key], $values);
		}
	}

	/**
	 * Returns true if the HTTP header is defined.
	 *
	 * @param string $key The HTTP header
	 *
	 * @return bool true if the parameter exists, false otherwise
	 */
	public function has(string $key): bool
	{
		return array_key_exists(str_replace('_', '-', strtolower($key)), $this->headers);
	}

	/**
	 * Returns true if the given HTTP header contains the given value.
	 *
	 * @param string $key   The HTTP header name
	 * @param string $value The HTTP value
	 *
	 * @return bool true if the value is contained in the header, false
	 *              otherwise
	 */
	public function contains(string $key, string $value): bool
	{
		return in_array($value, $this->get($key, null, false), false);
	}

	/**
	 * Removes a header.
	 *
	 * @param string $key The HTTP header name
	 */
	public function remove(string $key): void
	{
		$key = str_replace('_', '-', strtolower($key));
		unset($this->headers[$key]);
	}

	/**
	 * Returns the number of headers.
	 *
	 * @return int The number of headers
	 */
	public function count(): int
	{
		return count($this->headers);
	}

	/**
	 * Returns an iterator for headers.
	 *
	 * @return ArrayIterator An ArrayIterator instance
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->headers);
	}

}