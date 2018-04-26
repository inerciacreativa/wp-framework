<?php

namespace ic\Framework\Debug;

/**
 * Class Debug
 *
 * @package ic\Framework\Debug
 */
class Debug
{

    protected static function send()
    {
        return call_user_func_array(array(FirePHP::getInstance(true), 'fb'), func_get_args());
    }

    public static function group($label)
    {
        return self::send(null, $label, FirePHP::GROUP_START, null);
    }

    public static function groupEnd()
    {
        return self::send(null, null, FirePHP::GROUP_END);
    }

    public static function log($object, $label = null)
    {
        return self::send($object, $label, FirePHP::LOG);
    }

    public static function info($object, $label = null)
    {
        return self::send($object, $label, FirePHP::INFO);
    }

    public static function warn($object, $label = null)
    {
        return self::send($object, $label, FirePHP::WARN);
    }

    public static function error($object, $label = null)
    {
        return self::send($object, $label, FirePHP::ERROR);
    }

    public static function dump($key, $variable)
    {
        return self::send($variable, $key, FirePHP::DUMP);
    }

    public static function trace($label)
    {
        return self::send($label, FirePHP::TRACE);
    }

    public static function table($label, $table)
    {
        return self::send($table, $label, FirePHP::TABLE);
    }

    public static function display($object, $label = null)
    {
        echo '<pre>';
        if ($label) {
            echo '<h2>' . $label . '</h2>';
        }
        print_r($object);
        echo '</pre>';
    }

    public static function debug($label = null, $print = false)
    {
        $exception = new \Exception();

        if ($print) {
            if ($label) {
                echo '<h2>', $label, '</h2>';
            }

            echo '<pre>';
            print_r($exception->getTraceAsString());
            echo '</pre>';
        } else {
            self::send($exception->getTraceAsString(), $label, FirePHP::TRACE);
        }
    }

}