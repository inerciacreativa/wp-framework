<?php

namespace ic\Framework\Api;

use ic\Framework\Api\Auth\AuthInterface;
use ic\Framework\Support\Cache;

/**
 * Class Api
 *
 * @package ic\Framework\Api
 */
class Api
{

    const CACHE = 3600;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var AuthInterface
     */
    protected $auth;

    /**
     * @var bool
     */
    protected $json = true;

    /**
     * @var bool
     */
    protected $cache;

    /**
     * @var bool
     */
    protected $exceptions = false;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var Query
     */
    protected $query;

    /**
     * Api constructor.
     *
     * @param string             $name
     * @param string             $endpoint
     * @param AuthInterface|null $auth
     */
    public function __construct($name, $endpoint, AuthInterface $auth = null)
    {
        $this->name     = $name;
        $this->endpoint = $endpoint;
        $this->auth     = $auth;
        $this->cache    = static::CACHE;
    }

    /**
     * @param string             $name
     * @param string             $endpoint
     * @param AuthInterface|null $auth
     *
     * @return static
     */
    public static function create($name, $endpoint, AuthInterface $auth = null)
    {
        return new static($name, $endpoint, $auth);
    }

    /**
     * @param string   $method
     * @param array    $parameters
     * @param int|bool $cache
     *
     * @throws \Exception
     *
     * @return null|string|\stdClass
     */
    public function get($method, array $parameters = [], $cache = true)
    {
        return $this->query('GET', $method, $parameters, $cache);
    }

    /**
     * @param string   $method
     * @param array    $parameters
     * @param int|bool $cache
     *
     * @throws \Exception
     *
     * @return null|string|\stdClass
     */
    public function post($method, array $parameters = [], $cache = true)
    {
        return $this->query('POST', $method, $parameters, $cache);
    }

    /**
     * @param string   $http
     * @param string   $method
     * @param array    $parameters
     * @param int|bool $cache
     *
     * @throws \RuntimeException
     *
     * @return null|string|\stdClass
     */
    public function query($http, $method, array $parameters = [], $cache = true)
    {
        $result = null;

        if ($cache === true) {
            $cache = $this->cache;
        } else {
            $cache = (int)$cache;
        }

        try {
            $query  = $this->prepare($http, $method, $parameters);
            $result = $this->execute($query, $cache);

            $this->query = $query;

            if ($this->json) {
                $result = json_decode($result);
            }
        } catch (\RuntimeException $exception) {
            $this->errors[] = $exception->getMessage();

            if ($this->exceptions) {
                throw $exception;
            }
        }

        return $result;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param bool $json
     *
     * @return $this
     */
    public function setJson($json = true)
    {
        $this->json = (bool)$json;

        return $this;
    }

    /**
     * @return bool
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * @param int $cache
     *
     * @return $this
     */
    public function setCache($cache)
    {
        $this->cache = (int)$cache;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param bool $exceptions
     *
     * @return $this
     */
    public function setExceptions($exceptions = true)
    {
        $this->exceptions = (bool)$exceptions;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $http
     * @param string $method
     * @param array  $parameters
     *
     * @throws \RuntimeException
     *
     * @return Query
     */
    protected function prepare($http, $method, array $parameters = [])
    {
        $query = Query::create($this->endpoint)->query($http, $method, $parameters, false);

        if ($this->auth) {
            if ($this->auth->isReady()) {
                $query = $this->auth->authorize($query);
            } else {
                throw new \RuntimeException(sprintf("The OAuth module is not ready for authorization,\nID: %s", $this->auth->getId()));
            }
        }

        return $query;
    }

    /**
     * @param Query $query
     * @param int   $cache
     * @param bool  $retry
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function execute(Query $query, $cache, $retry = false)
    {
        $id = $this->name . '_query_' . $query->getId();

        if ($cache && !$retry) {
            if (false !== ($result = Cache::get($id))) {
                return $result;
            }
        }

        if ($query->execute()) {
            $result = $query->getResponse();

            if ($cache) {
                Cache::set($id, $result, $cache);
            }

            return $result;
        } elseif (!$retry && $this->auth && $this->auth->regenerate()) {
            $query = $this->auth->authorize($query);

            return $this->execute($query, $cache, true);
        }

        throw new \RuntimeException(sprintf("The query produced an error.\nRequest: %s\nResponse: %s\nError: %s", $query->getUrl(), $query->getResponse(), $query->getError()));
    }

}