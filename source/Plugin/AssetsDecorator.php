<?php

namespace ic\Framework\Plugin;

/**
 * Class AssetsDecorator
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
     * @param string $pathName
     *
     * @return Assets
     */
    public function setAssets($pathName = ''): Assets
    {
        if ($this->assets === null) {
            if (empty($pathName) && method_exists($this, 'getRelativePath')) {
                $pathName = $this->getRelativePath();
            }

            $this->assets = Assets::create($pathName);
        }

        return $this->assets;
    }

    /**
     * @return Assets
     */
    public function getAssets(): Assets
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
     *
     * @throws \InvalidArgumentException
     */
    public function addStyle(string $source, int $target, array $parameters = [])
    {
        $this->getAssets()->addStyle($source, $target, $parameters);

        return $this;
    }

    /**
     * @param string $source
     * @param array  $parameters
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function addFrontStyle(string $source, array $parameters = [])
    {
        return $this->addStyle($source, Assets::FRONT, $parameters);
    }

    /**
     * @param string $source
     * @param array  $parameters
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function addBackStyle(string $source, array $parameters = [])
    {
        return $this->addStyle($source, Assets::BACK, $parameters);
    }

    /**
     * @param string $source
     * @param int    $target
     * @param array  $parameters
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function addScript(string $source, int $target, array $parameters = [])
    {
        $this->getAssets()->addScript($source, $target, $parameters);

        return $this;
    }

    /**
     * @param string $source
     * @param array  $parameters
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function addFrontScript(string $source, array $parameters = [])
    {
        return $this->addScript($source, Assets::FRONT, $parameters);
    }

    /**
     * @param string $source
     * @param array  $parameters
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function addBackScript(string $source, array $parameters = [])
    {
        return $this->addScript($source, Assets::BACK, $parameters);
    }

}
