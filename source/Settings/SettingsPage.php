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
    public function __construct(string $parent, string $id, Options $options, string $pageTitle = '', string $menuTitle = '')
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
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return $this->parent;
    }

    /**
     * @return Options
     */
    public function getOptions(): Options
    {
        return $this->options;
    }

    /**
     * @return SettingsForm
     */
    public function getForm(): SettingsForm
    {
        return $this->form;
    }

	/**
	 *
	 */
    abstract protected function register(): void;

    /**
     * Initialize
     */
    protected function initialize(): void
    {
        $this->hook()->on('admin_init', 'register');
    }

}