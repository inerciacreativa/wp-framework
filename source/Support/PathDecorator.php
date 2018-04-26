<?php

namespace ic\Framework\Support;

/**
 * Class PathDecorator
 *
 * @package ic\Framework\Support
 */
trait PathDecorator
{

    /**
     * @var array
     */
    private $paths = [];

    /**
     * @param string $fileName
     * @param string $rootName
     *
     * @return $this
     */
    protected function setPaths($fileName, $rootName)
    {
        $baseName = basename(dirname($fileName));
        $rootName = wp_normalize_path(($rootName === WP_PLUGIN_DIR) ? ($rootName . '/' . $baseName) : $rootName);
        $fileName = basename($fileName);

        $this->paths = compact('baseName', 'rootName', 'fileName');

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->getAbsolutePath(Arr::get($this->paths, 'fileName', ''));
    }

    /**
     * @return string
     */
    public function getRootName()
    {
        return Arr::get($this->paths, 'rootName', '');
    }

    /**
     * @return string
     */
    public function getBaseName()
    {
        return Arr::get($this->paths, 'baseName', '');
    }

    /**
     * Return the absolute path to the plugin.
     *
     * @param string $path
     *
     * @return string
     */
    public function getAbsolutePath($path = '')
    {
        return $this->getRootName() . $this->getPath($path);
    }

    /**
     * Return the relative path to the plugin.
     *
     * @param string $path
     *
     * @return string
     */
    public function getRelativePath($path = '')
    {
        return $this->getBaseName() . $this->getPath($path);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function getPath($path)
    {
        return $path ? ('/' . ltrim(wp_normalize_path($path), '/\\')) : '';
    }

}