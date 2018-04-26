<?php

namespace ic\Framework\Plugin;

use ic\Framework\Widget\Widget as BaseWidget;

/**
 * Class Widget
 *
 * @package ic\Framework\Plugin
 */
abstract class Widget extends BaseWidget
{

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * Widget constructor.
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin = null)
    {
        $this->plugin = $plugin;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function id()
    {
        return $this->plugin->id;
    }

    /**
     * @inheritdoc
     */
    public function name()
    {
        return $this->plugin->name;
    }

}