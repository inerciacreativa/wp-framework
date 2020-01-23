<?php

namespace ic\Framework\Api\Client;

use ic\Framework\Api\Api;
use ic\Framework\Api\Auth\AuthInterface;
use ic\Framework\Support\Arr;

/**
 * Class Client
 *
 * @package ic\Framework\Api\Client
 */
abstract class Client implements ClientInterface
{

	/**
	 * @var Api
	 */
	protected $api;

	/**
	 * @var array
	 */
	protected $credentials = [];

	/**
	 * @param array $credentials
	 *
	 * @return static
	 */
	public static function create(array $credentials = []): ClientInterface
	{
		return new static($credentials);
	}

	/**
	 * Client constructor.
	 *
	 * @param array $credentials
	 */
	public function __construct(array $credentials = [])
	{
		$this->credentials = Arr::defaults($this->getCredentials(), $credentials);
	}

	/**
	 * @inheritdoc
	 */
	public function setCache(int $cache): ClientInterface
	{
		$this->getApi()->setCache($cache);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function setJson(bool $json): ClientInterface
	{
		$this->getApi()->setJson($json);

		return $this;
	}

	/**
	 * @return Api
	 */
	public function getApi(): Api
	{
		if ($this->api === null) {
			$this->api = Api::create($this->getName(), $this->getEndpoint(), $this->getAuth());
		}

		return $this->api;
	}

	/**
	 * @inheritdoc
	 */
	public function query($method, array $parameters = [])
	{
		if (method_exists($this, $method)) {
			return call_user_func_array([$this, $method], $parameters);
		}

		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function getUrl(string $type, string $id): string
	{
		$urls = $this->getUrls();
		$url  = $urls[$type] ?? $type;

		return str_replace('#ID#', $id, $url);
	}

	/**
	 * @inheritdoc
	 */
	public function getAuth(): ?AuthInterface
	{
		return null;
	}

	/**
	 * @return array
	 */
	protected function getCredentials(): array
	{
		return [];
	}

}
