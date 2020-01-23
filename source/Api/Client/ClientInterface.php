<?php

namespace ic\Framework\Api\Client;

use ic\Framework\Api\Api;
use ic\Framework\Api\Auth\AuthInterface;
use ic\Framework\Data\Collection;

/**
 * Interface ClientInterface
 *
 * @package ic\Framework\Api\Client
 */
interface ClientInterface
{

	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return string
	 */
	public function getVersion(): string;

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public function getDomain(string $path = ''): string;

	/**
	 * @return string
	 */
	public function getEndpoint(): string;

	/**
	 * @return AuthInterface|null
	 */
	public function getAuth(): ?AuthInterface;

	/**
	 * @return Collection
	 */
	public function getMethods(): Collection;

	/**
	 * @return array
	 */
	public function getUrls(): array;

	/**
	 * @param string $type
	 * @param string $id
	 *
	 * @return string
	 */
	public function getUrl(string $type, string $id): string;

	/**
	 * @param int $cache
	 *
	 * @return static
	 */
	public function setCache(int $cache): ClientInterface;

	/**
	 * @param bool $json
	 *
	 * @return static
	 */
	public function setJson(bool $json): ClientInterface;

	/**
	 * @return Api
	 */
	public function getApi(): Api;

}
