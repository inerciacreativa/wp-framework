<?php

namespace ic\Framework\Settings\Page;

use ic\Framework\Settings\SettingsPage;
use ic\Framework\Settings\Form\Tabs;
use ic\Framework\Support\Options;
use ic\Framework\Html\Tag;

/**
 * Class CustomPage
 *
 * @package ic\Framework\Settings\Page
 */
abstract class CustomPage extends SettingsPage
{

    /**
     * @var Tabs
     */
    protected $tabs;

    /**
     * @var string
     */
    protected $capability = 'manage_options';

    /**
     * CustomPage constructor.
     *
     * @param string  $parent
     * @param string  $id
     * @param Options $options
     * @param string  $pageTitle
     * @param string  $menuTitle
     */
    public function __construct($parent, $id, Options $options, $pageTitle, $menuTitle = '')
    {
        parent::__construct($parent, $id, $options, $pageTitle, $menuTitle ?: $pageTitle);

        $this->tabs = new Tabs($this);

        $this->initialize();
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        parent::initialize();

        $this->setHook()->before($this->getHook(), 'addMenu');
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->tabs->register();
    }

    /**
     * @param string $capability
     *
     * @return $this
     */
    public function capability($capability)
    {
        $this->capability = $capability;

        return $this;
    }

    /**
     * Creates a new tab.
     *
     * @param string   $id
     * @param \Closure $content
     *
     * @return $this
     */
    public function tab($id, \Closure $content)
    {
        $this->tabs->tab($id, $content);

        return $this;
    }

    /**
     * @return string
     */
    abstract protected function getHook();

    /**
     * Add the submenu page.
     */
    protected function addMenu()
    {
        add_submenu_page($this->parent, $this->pageTitle, $this->menuTitle, $this->capability, $this->id, function () {
            echo Tag::div(['class' => 'wrap'], [
                Tag::h1($this->pageTitle),
                $this->capture(function () {
                    settings_errors($this->id());
                }),
                $this->tabs->navigation(),
                $this->form()->open(['action' => $this->parent]),
                $this->capture(function () {
                    do_settings_sections($this->id());
                    submit_button();
                }),
                $this->form()->close(),
            ]);
        });
    }

    /**
     * @param callable $callback
     *
     * @return string
     */
    protected function capture(callable $callback)
    {
        ob_start();

        $callback();

        return ob_get_clean();
    }

}