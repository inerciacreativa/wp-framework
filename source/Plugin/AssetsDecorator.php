<?php

namespace ic\Framework\Plugin;

/**
 * Class Assetable
 *
 * @package ic\Framework\Plugin
 */
trait AssetsDecorator
{

    /**
     * @var Assets
     */
    private $assets;

    /**
     * @param string|null $pathName
     *
     * @return Assets
     */
    public function setAssets($pathName = null)
    {
        if ($this->assets === null) {
            if ($pathName === null && method_exists($this, 'getRelativePath')) {
                $pathName = $this->getRelativePath();
            }

            $this->assets = Assets::create($pathName);
        }

        return $this->assets;
    }

    /**
     * @return Assets
     */
    public function getAssets()
    {
        if ($this->assets === null) {
            return $this->setAssets();
        }

        return $this->assets;
    }

    /**
     * @param string $source
     * @param int    $target
     * @param array  $parameters
     *
     * @return static
     */
    public function addStyle($source, $target, array $parameters = [])
    {
        $this->getAssets()->addStyle($source, $target, $parameters);

        return $this;
    }

    /**
     * @param string $source
     * @param array  $parameters
     *
     * @return static
     */
    public function addFrontStyle($source, array $parameters = [])
    {
        return $this->addStyle($source, Assets::FRONT, $parameters);
    }

    /**
     * @param string $source
     * @param array  $parameters
     *
     * @return static
     */
    public function addBackStyle($source, array $parameters = [])
    {
        return $this->addStyle($source, Assets::BACK, $parameters);
    }

    /**
     * @param string $source
     * @param int    $target
     * @param array  $parameters
     *
     * @return static
     */
    public function addScript($source, $target, array $parameters = [])
    {
        $this->getAssets()->addScript($source, $target, $parameters);

        return $this;
    }

    /**
     * @param string $source
     * @param array  $parameters
     *
     * @return static
     */
    public function addFrontScript($source, array $parameters = [])
    {
        return $this->addScript($source, Assets::FRONT, $parameters);
    }

    /**
     * @param string $source
     * @param array  $parameters
     *
     * @return static
     */
    public function addBackScript($source, array $parameters = [])
    {
        return $this->addScript($source, Assets::BACK, $parameters);
    }

}