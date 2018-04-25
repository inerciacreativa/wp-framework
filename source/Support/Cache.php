<?php

namespace ic\Framework\Support;

/**
 * Class Cache
 *
 * @package ic\Framework\Support
 */
class Cache
{

    /**
     * @param string $name
     * @param mixed  $value
     * @param int    $expiration
     */
    public static function set($name, $value, $expiration = 0)
    {
        set_transient($name, $value, $expiration);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public static function get($name)
    {
        return get_transient($name);
    }

    /**
     * @param string    $name
     * @param mixed     $value
     * @param null|bool $autoload
     *
     * @return bool
     */
    public static function push($name, $value, $autoload = null)
    {
        return update_option($name, $value, $autoload);
    }

    /**
     * @param string $name
     * @param bool   $default
     *
     * @return mixed
     */
    public static function pull($name, $default = false)
    {
        return get_option($name, $default);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function forget($name)
    {
        return delete_option($name);
    }

}