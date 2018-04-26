<?php

namespace ic\Framework;

use ic\Framework\Plugin\PluginBase;
use ic\Framework\Plugin\Assets;

/**
 * Class Framework
 *
 * @package ic\Framework
 *
 * @method static Framework getInstance
 */
class Framework extends PluginBase
{

    /**
     * @inheritdoc
     */
    protected function onCreation()
    {
        parent::onCreation();

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
     */
    public function addScript($source, $target, array $parameters = [])
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
     */
    public function addStyle($source, $target, array $parameters = [])
    {
        $parameters['path'] = $this->getFileName();

        return $this->getAssets()->addStyle($source, $target, $parameters);
    }

    /**
     *
     */
    protected function setTranslation()
    {
        load_muplugin_textdomain($this->id, $this->getRelativePath($this->languages));
    }

}