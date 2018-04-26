<?php

namespace ic\Framework\Settings;

use ic\Framework\Hook\HookDecorator;
use ic\Framework\Support\Options;

/**
 * Class Page
 *
 * @package ic\Framework\Settings
 */
abstract class SettingsPage
{

    use HookDecorator;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $parent;

    /**
     * @var string
     */
    protected $menuTitle;

    /**
     * @var string
     */
    protected $pageTitle;

    /**
     * @var Options;
     */
    protected $options;

    /**
     * @var SettingsForm
     */
    protected $form;

    /**
     * Page constructor.
     *
     * @param string  $parent
     * @param string  $id
     * @param Options $options
     * @param string  $pageTitle
     * @param string  $menuTitle
     *
     */
    public function __construct($parent, $id, Options $options, $pageTitle = '', $menuTitle = '')
    {
        $this->parent    = $parent;
        $this->id        = $id;
        $this->options   = $options;
        $this->menuTitle = $pageTitle;
        $this->pageTitle = $menuTitle;
        $this->form      = new SettingsForm($options);
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * @return Options
     */
    public function options()
    {
        return $this->options;
    }

    /**
     * @return SettingsForm
     */
    public function form()
    {
        return $this->form;
    }

    abstract protected function register();

    /**
     * Initialize
     */
    protected function initialize()
    {
        $this->setHook()->on('admin_init', 'register');
    }

}