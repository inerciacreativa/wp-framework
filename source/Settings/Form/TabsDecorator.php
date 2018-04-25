<?php

namespace ic\Framework\Settings\Form;

use ic\Framework\Settings\Page\CustomPage;

/**
 * Class TabsDecorator
 *
 * @package ic\Framework\Settings\Form
 */
trait TabsDecorator
{

    /**
     * @var Tabs
     */
    protected $tabs;

    /**
     * @param CustomPage $page
     */
    public function createTabs(CustomPage $page)
    {
        $this->tabs = new Tabs($page);
    }

    public function tabs()
    {
        if (!$this->tabs) {
            throw new \RuntimeException('No Page has been specified.');
        }

        return $this->tabs;
    }

    public function tab($id = null, $title = null)
    {
        return $this->tabs()->tab($id, $title ?: $this->menuTitle);
    }

    public function current()
    {
        return $this->tabs()->current();
    }

    public function sections()
    {
        return $this->current()->sections();
    }

    public function section($id = null, $title = null, $content = null)
    {
        return $this->sections()->section($id, $title, $content);
    }

    public function fields()
    {
        return $this->sections()->fields();
    }

    public function field($id, $label, $callback, $attributes = [], $value = null, $chosen = null)
    {
        return $this->sections()->current()->field($id, $label, $callback, $attributes, $value, $chosen);
    }

    public function register()
    {
        $this->tabs()->register();
    }

    public function validation(callable $validation)
    {
        return $this->sections()->validation($validation);
    }

}