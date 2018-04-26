<?php

namespace ic\Framework\Api\Client;

use ic\Framework\Api\Api;
use ic\Framework\Api\Auth\AuthInterface;
use ic\Framework\Support\Collection;

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
    public function getName();

    /**
     * @return string
     */
    public function getVersion();

    /**
     * @return string
     */
    public function getDomain();

    /**
     * @return string
     */
    public function getEndpoint();

    /**
     * @return AuthInterface
     */
    public function getAuth();

    /**
     * @return Collection
     */
    public function getMethods();

    /**
     * @return array
     */
    public function getUrls();

    /**
     * @param string $type
     * @param string $id
     * @param string $extra
     *
     * @return string
     */
    public function getUrl($type, $id = '', $extra = '');

    /**
     * @param int $cache
     *
     * @return static
     */
    public function setCache($cache);

    /**
     * @param bool $json
     *
     * @return static
     */
    public function setJson($json);

    /**
     * @return Api
     */
    public function api();

    /**
     * @param string $method
     * @param array  $parameters
     *
     * @return \stdClass|array|null
     */
    public function query($method, array $parameters = []);

}