<?php

namespace ic\Framework\Api\Auth;

use ic\Framework\Api\Query;

/**
 * Class OAuthKey
 *
 * @package ic\Framework\Api\OAuthKey
 */
class OAuthKey implements AuthInterface
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $headers;

    /**
     * OAuthKey constructor.
     *
     * @param array $parameters
     * @param array $headers
     */
    public function __construct(array $parameters, array $headers = [])
    {
        $this->parameters = $parameters;
        $this->headers    = $headers;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        if (!$this->id) {
            $this->id = 'oauth_key_' . md5(serialize($this->parameters) . serialize($this->headers));
        }

        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function authorize(Query $query)
    {
        foreach ($this->parameters as $name => $value) {
            $query->setParameter($name, $value);
        }

        foreach ($this->headers as $name => $value) {
            $query->setHeader($name, $value);
        }

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function isReady()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function regenerate()
    {
        return false;
    }

}