<?php

namespace ic\Framework\Api;

/**
 * Class Query
 *
 * @package ic\Framework\Api
 */
class Query
{

    const USER_AGENT = 'ic HTTP/2.0';

    const READY = 0;

    const ERROR = -1;

    const SUCCESS = 200;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $url;
    /**
     * @var int
     */
    protected $status = self::READY;

    /**
     * @var array
     */
    protected $request = [
        'user-agent' => self::USER_AGENT,
        'sslverify'  => false,
        'headers'    => [
            'Accept-Encoding' => 'gzip',
        ],
        'cookies'    => [],
        'body'       => null,
    ];

    /**
     * @var null|array|\WP_Error
     */
    protected $response;

    /**
     * Query constructor.
     *
     * @param string $endpoint
     */
    public function __construct($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @param string $endpoint
     *
     * @return static
     */
    public static function create($endpoint)
    {
        return new static($endpoint);
    }

    /**
     * @param string $http
     * @param string $method
     * @param array  $parameters
     * @param bool   $execute
     *
     * @return bool|Query
     */
    public function query($http, $method = '', array $parameters = [], $execute = true)
    {
        $this->method     = $method;
        $this->parameters = array_merge($this->parameters, $parameters);

        $this->request['method'] = $http;

        return $execute ? $this->execute() : $this;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        $this->response = wp_remote_request($this->getUrl(), $this->request);

        if (is_wp_error($this->response)) {
            $this->status = self::ERROR;
        } else {
            $this->status = (int)wp_remote_retrieve_response_code($this->response);

            if ($this->status === self::SUCCESS) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $method
     * @param array  $parameters
     * @param bool   $execute
     *
     * @return bool|Query
     */
    public function get($method = '', array $parameters = [], $execute = true)
    {
        return $this->query('GET', $method, $parameters, $execute);
    }

    /**
     * @param string $method
     * @param array  $parameters
     * @param bool   $execute
     *
     * @return bool|Query
     */
    public function post($method = '', array $parameters = [], $execute = true)
    {
        return $this->query('POST', $method, $parameters, $execute);
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        if (is_array($this->response) && !is_wp_error($this->response)) {
            return wp_remote_retrieve_body($this->response);
        }

        return '';
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \WP_Error|bool
     */
    public function getError()
    {
        if (is_wp_error($this->response)) {
            return $this->response->get_error_message();
        }

        return false;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if (empty($this->url)) {
            $this->url = empty($this->method) ? $this->endpoint : sprintf('%s/%s', rtrim($this->endpoint, '/'), ltrim($this->method, '/'));

            if (!empty($this->parameters)) {
                $this->url .= '?' . http_build_query($this->parameters);
            }
        }

        return $this->url;
    }

    /**
     * @return string
     */
    public function getId()
    {
        if (empty($this->id)) {
            $this->id = md5($this->getUrl());
        }

        return $this->id;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setParameter($name, $value)
    {
        if (isset($this->parameters[$name])) {
            return $this;
        }

        $this->parameters[$name] = $value;

        return $this->reset(true);
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setHeader($name, $value)
    {
        $this->request['headers'][$name] = $value;

        return $this->reset();
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setCookie($name, $value)
    {
        $this->request['cookies'][$name] = $value;

        return $this->reset();
    }

    /**
     * @param mixed $body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->request['body'] = $body;

        return $this->reset();
    }

    /**
     * @param bool $url
     *
     * @return $this
     */
    protected function reset($url = false)
    {
        $this->response = null;
        $this->status   = self::READY;

        if ($url) {
            $this->url = null;
            $this->id  = null;
        }

        return $this;
    }

}