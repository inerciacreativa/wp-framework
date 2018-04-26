<?php

namespace ic\Framework\Plugin;

use ic\Framework\Debug\Debug;
use ic\Framework\Support\Store;

class Metadata extends Store
{

    public function __construct($fileName)
    {
        $defaults = [
            'id'        => 'Text Domain',
            'name'      => 'Plugin Name',
            'version'   => 'Version',
            'languages' => 'Domain Path',
        ];

        $metadata = get_file_data($fileName, $defaults, 'plugin');
        $metadata = array_filter($metadata);

        if (count($metadata) === 0) {
            Debug::error(sprintf('The plugin metadata is missing, Not found in "%s".', $fileName), static::class);
        } elseif (count($metadata) < 4) {
            $keys = implode('", "', array_diff_key($defaults, $metadata));
            Debug::error(sprintf('The plugin metadata is incomplete. Missing value(s): "%s".', $keys), static::class);
        }

        $this->fill(array_merge([
            'id'        => 'ic-unknown',
            'name'      => 'Unknown',
            'version'   => '0.0.0',
            'languages' => 'languages',
        ], $metadata));
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function __set($key, $value)
    {
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

}