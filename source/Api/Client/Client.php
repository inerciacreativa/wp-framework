<?php

namespace ic\Framework\Api\Client;

use ic\Framework\Api\Api;
use ic\Framework\Support\Arr;
use ic\Framework\Support\Str;

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
    public static function create(array $credentials = [])
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
        $this->credentials = array_merge(
            $this->credentials,
            Arr::only($credentials, array_keys($this->credentials))
        );
    }

    /**
     * @inheritdoc
     */
    public function setCache($cache)
    {
        $this->api()->setCache($cache);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setJson($json)
    {
        $this->api()->setJson($json);

        return $this;
    }

    /**
     * @return Api
     */
    public function api()
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
    public function getUrl($type, $id = '', $extra = '')
    {

        $urls = $this->getUrls();
        $url  = isset($urls[$type]) ? $urls[$type] : $type;

        if (!Str::startsWith($url, 'http')) {
            $url = $this->getDomain() . $url;
        }

        return str_replace(['#ID#', '#EXTRA#'], [$id, $extra], $url);
    }

    /**
     * @return string|null
     */
    public function getAuth()
    {
        return null;
    }

}