<?php

namespace ic\Framework\Api\Auth;

use ic\Framework\Api\Query;
use ic\Framework\Support\Cache;

/**
 * Class OAuthToken
 *
 * @package ic\Framework\Api\OAuth
 */
class OAuthToken implements AuthInterface
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var string
     */
    protected $credentials;

    /**
     * @var bool|string
     */
    protected $token = true;

    /**
     * @var int
     */
    protected $retries = 0;

    /**
     * @param string $endpoint
     * @param string $id
     * @param string $secret
     * @param array  $headers
     */
    public function __construct($endpoint, $id, $secret, array $headers = [])
    {
        $this->endpoint    = $endpoint;
        $this->headers     = $headers;
        $this->credentials = base64_encode($id . ':' . $secret);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        if (!$this->id) {
            $this->id = 'oauth_token_' . md5($this->credentials);
        }

        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function isReady()
    {
        return (bool)$this->getToken();
    }

    /**
     * @inheritdoc
     */
    public function authorize(Query $query)
    {
        if ($this->isReady()) {
            $query->setHeader('Authorization', 'Bearer ' . $this->getToken());

            foreach ($this->headers as $name => $value) {
                $query->setHeader($name, $value);
            }
        }

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function regenerate()
    {
        if ($token = $this->setToken(false)) {
            $this->token = $token;

            return true;
        }

        return false;
    }

    /**
     * @return bool|string
     */
    protected function getToken()
    {
        if ($this->token === true) {
            $this->token = $this->setToken(true);
        }

        return $this->token;
    }

    /**
     * @param bool $cache
     *
     * @return bool|string
     */
    protected function setToken($cache)
    {
        $token = false;

        if ($cache) {
            $this->retries++;

            $token = Cache::pull($this->getId());
        }

        while (!$token && $this->retries < 2) {
            $this->retries++;

            if ($token = $this->retrieveToken()) {
                Cache::push($this->getId(), $token);
            }
        }

        return $token;
    }

    /**
     * Generate the bearer token for unauthenticated requests following the OAuth 2.0 Client Credentials Grant.
     *
     * @return bool|string
     */
    protected function retrieveToken()
    {
        $token = false;
        $query = Query::create($this->endpoint)
                      ->setHeader('Authorization', 'Basic ' . $this->credentials)
                      ->setHeader('Content-Type', 'application/x-www-form-urlencoded;charset=UTF-8')
                      ->setBody(['grant_type' => 'client_credentials']);

        foreach ($this->headers as $name => $value) {
            $query->setHeader($name, $value);
        }

        if ($query->post()) {
            $data = json_decode($query->getResponse());

            if (isset($data->access_token)) {
                $token = $data->access_token;
            }
        }

        return $token;
    }

}