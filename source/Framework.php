<?php

namespace ic\Framework;

use ic\Framework\Plugin\PluginBase;
use ic\Framework\Plugin\Assets;

/**
 * Class Framework
 *
 * @package ic\Framework
 *
 * @method static Framework instance
 */
class Framework extends PluginBase
{

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setOptions([
            'youtube' => [
                'credentials' => [
                    'key' => '',
                ],
            ],
            'vimeo'   => [
                'credentials' => [
                    'id'     => '',
                    'secret' => '',
                ],
            ],
        ]);

        $this->setAssets();

        ob_start();
    }

    /**
     * @param string $source
     * @param int    $target
     * @param array  $parameters
     *
     * @return Assets
     *
     * @throws \InvalidArgumentException
     */
    public function addScript(string $source, int $target, array $parameters = []): Assets
    {
        $parameters['path'] = $this->getFileName();

        return $this->getAssets()->addScript($source, $target, $parameters);
    }

    /**
     * @param string $source
     * @param int    $target
     * @param array  $parameters
     *
     * @return Assets
     *
     * @throws \InvalidArgumentException
     */
    public function addStyle(string $source, int $target, array $parameters = []): Assets
    {
        $parameters['path'] = $this->getFileName();

        return $this->getAssets()->addStyle($source, $target, $parameters);
    }

    /**
     *
     */
    protected function translation(): void
    {
        load_muplugin_textdomain($this->id(), $this->getRelativePath($this->languages()));
    }

}