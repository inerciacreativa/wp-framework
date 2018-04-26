<?php

namespace ic\Framework\Support;

/**
 * Class Config
 *
 * @package ic\Engine
 * @method static Config getInstance()
 */
class Config extends Singleton
{

    /**
     * @var Store
     */
    protected $store;

    /**
     * Config constructor.
     */
    protected function __construct()
    {
        parent::__construct();
        $this->store = new Store();
    }

    /**
     * @return Store
     */
    protected static function store()
    {
        return self::getInstance()->store;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public static function add($key, $value)
    {
        self::store()->add($key, $value);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public static function set($key, $value)
    {
        self::store()->set($key, $value);
    }

    /**
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return self::store()->get($key, $default);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function has($key)
    {
        return self::store()->has($key);
    }

    /**
     * @return array
     */
    public static function all()
    {
        return self::store()->all();
    }

}