<?php

namespace ic\Framework\Http;

use ic\Framework\Support\Arr;
use ic\Framework\Support\Data;

/**
 * Class Input
 *
 * @package ic\Framework\Http
 */
class Input
{

	/**
	 * @var static
	 */
	private static $instance;

	/**
	 * @var string
	 */
	private $method;

	/**
	 * @var InputStore
	 */
	private $request;

	/**
	 * @var InputStore
	 */
	private $query;

	/**
	 * @var FileStore
	 */
	private $files;

	/**
	 * @var ServerStore
	 */
	private $server;

	/**
	 * @var HeaderStore
	 */
	private $headers;

	/**
	 * @var InputStore
	 */
	private $referer;

	/**
	 * @return static
	 */
	public static function getInstance()
	{
		if (null === static::$instance) {
			static::$instance = new static($_GET, $_POST, $_FILES, $_SERVER);
		}

		return static::$instance;
	}

	/**
	 * Input constructor.
	 *
	 * @param array $query
	 * @param array $request
	 * @param array $files
	 * @param array $server
	 */
	protected function __construct(array $query = [], array $request = [], array $files = [], array $server = [])
	{
		$this->query   = new InputStore($query);
		$this->request = new InputStore($request);
		$this->files   = new FileStore($files);
		$this->server  = new ServerStore($server);
		$this->headers = new HeaderStore($this->server->getHeaders());
		$this->referer = new RefererStore($this->request->get('_wp_http_referer', ''));
	}

	/**
	 * @return string
	 */
	public function method(): string
	{
		if (null === $this->method) {
			$this->method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
			if ('POST' === $this->method) {

				if ($method = $this->headers->get('X-HTTP-METHOD-OVERRIDE')) {
					$this->method = strtoupper($method);
				} else if ($this->request->has('_method')) {
					$this->method = strtoupper($this->request->get('_method'));
				}
			}
		}

		return $this->method;
	}

	/**
	 * Get all of the input and files for the request.
	 *
	 * @return array
	 */
	public function all(): array
	{
		return array_replace_recursive($this->get(), $this->files->all());
	}

	/**
	 * Get a subset containing the provided keys with values from the input
	 * data.
	 *
	 * @param  array|mixed $keys
	 *
	 * @return array
	 */
	public function only($keys): array
	{
		$keys    = \is_array($keys) ? $keys : \func_get_args();
		$results = [];
		$input   = $this->all();

		foreach ((array) $keys as $key) {
			Arr::set($results, $key, Data::get($input, $key));
		}

		return $results;
	}

	/**
	 * Get all of the input except for a specified array of items.
	 *
	 * @param  array|mixed $keys
	 *
	 * @return array
	 */
	public function except($keys): array
	{
		$keys    = \is_array($keys) ? $keys : \func_get_args();
		$results = $this->all();

		Arr::forget($results, $keys);

		return $results;
	}

	/**
	 * Determine if the request contains a given input item key.
	 *
	 * @param  string|array $key
	 *
	 * @return bool
	 */
	public function exists(string $key): bool
	{
		$keys  = \is_array($key) ? $key : \func_get_args();
		$input = $this->all();

		foreach ((array) $keys as $value) {
			if (!Arr::has($input, $value)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determine if the request contains a non-empty value for an input item.
	 *
	 * @param  string|array $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool
	{
		$keys = \is_array($key) ? $key : \func_get_args();

		foreach ((array) $keys as $value) {
			if ($this->isEmptyString($value)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieve an input item from the request.
	 *
	 * @param  string            $key
	 * @param  string|array|null $default
	 *
	 * @return string|array
	 */
	public function request(string $key = null, $default = null)
	{
		$input = $this->getInputSource()->all();

		return $key ? Arr::get($input, $key, $default) : $input;
	}

	/**
	 * Retrieve an input item from the request.
	 *
	 * @param  string            $key
	 * @param  string|array|null $default
	 *
	 * @return string|array
	 */
	public function get(string $key = null, $default = null)
	{
		$input = $this->getInputSource()->all() + $this->query->all();

		return $key ? Arr::get($input, $key, $default) : $input;
	}

	/**
	 * Retrieve a query string item from the request.
	 *
	 * @param  string            $key
	 * @param  string|array|null $default
	 *
	 * @return string|array
	 */
	public function query(string $key, $default = null)
	{
		return $this->query->get($key, $default);
	}

	/**
	 * Retrieve a file from the request.
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 *
	 * @return File
	 */
	public function file(string $key, $default = null): File
	{
		return $this->files->get($key, $default);
	}

	/**
	 * Determine if the uploaded data contains a file.
	 *
	 * @param  string $key
	 *
	 * @return bool
	 */
	public function hasFile(string $key): bool
	{
		return $this->files->has($key);
	}

	/**
	 * Retrieve a header from the request.
	 *
	 * @param  string            $key
	 * @param  string|array|null $default
	 *
	 * @return string|array
	 */
	public function header(string $key, $default = null)
	{
		return $this->headers->get($key, $default);
	}

	/**
	 * Determine if a header is set on the request.
	 *
	 * @param  string $key
	 *
	 * @return bool
	 */
	public function hasHeader(string $key): bool
	{
		return $this->header($key) !== null;
	}

	/**
	 * Retrieve a server variable from the request.
	 *
	 * @param  string            $key
	 * @param  string|array|null $default
	 *
	 * @return string|array
	 */
	public function server(string $key, $default = null)
	{
		return $this->server->get($key, $default);
	}

	/**
	 * @param  string            $key
	 * @param  string|array|null $default
	 *
	 * @return string|array
	 */
	public function referer(string $key, $default = null)
	{
		return $this->referer->get($key, $default);
	}

	/**
	 * @return InputStore
	 */
	protected function getInputSource(): InputStore
	{
		return $this->method() === 'GET' ? $this->query : $this->request;
	}

	/**
	 * Determine if the given input key is an empty string for "has".
	 *
	 * @param  string $key
	 *
	 * @return bool
	 */
	protected function isEmptyString($key): bool
	{
		$value       = $this->get($key);
		$boolOrArray = \is_bool($value) || \is_array($value);

		return !$boolOrArray && trim((string) $value) === '';
	}

	/**
	 *
	 */
	private function __clone()
	{
	}

}