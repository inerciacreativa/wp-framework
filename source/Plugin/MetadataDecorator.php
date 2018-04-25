<?php

namespace ic\Framework\Plugin;

/**
 * Class MetadataDecorator
 *
 * @package ic\Framework\Support
 *
 * @property-read string $id
 * @property-read string $name
 * @property-read string $version
 * @property-read string $languages
 *
 */
trait MetadataDecorator
{

    /**
     * @var array|Plugin
     */
    private $metadata = [];

    /**
     * @param string|Plugin $source
     *
     * @return static
     */
    protected function setMetadata($source)
    {
        $this->metadata = $source instanceof PluginBase ? $source : new Metadata($source);

        return $this;
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function __get($key)
    {
        return isset($this->metadata->$key) ? $this->metadata->$key : null;
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
        return isset($this->metadata->$key);
    }

}