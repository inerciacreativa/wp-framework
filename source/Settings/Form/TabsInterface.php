<?php

namespace ic\Framework\Settings\Form;

/**
 * Interface TabsInterface
 *
 * @package ic\Framework\Settings\Form
 */
interface TabsInterface
{

    /**
     * Return all Tabs.
     *
     * @return Tabs
     */
    public function tabs();

    /**
     * Add a new Tab.
     *
     * @param string $id
     * @param string $title
     *
     * @return Tab
     */
    public function tab($id = null, $title = null);

    /**
     * Return the current Tab.
     *
     * @return Tab
     */
    public function current();

    /**
     * Return all Sections.
     *
     * @return Sections
     */
    public function sections();

    /**
     * Add a new Section if not exists and/or return that Section.
     *
     * @param string $id
     * @param string $title
     * @param string $content
     *
     * @return Section
     */
    public function section($id = null, $title = null, $content = null);

    /**
     * Return all Fields.
     *
     * @return Field[]
     */
    public function fields();

    /**
     * Create a new Field and add it to the current Section.
     *
     * @param string          $id
     * @param string          $label
     * @param string|callable $callback
     * @param array           $attributes
     * @param mixed           $value
     * @param string|array    $chosen
     *
     * @return Section
     */
    public function field($id, $label, $callback, $attributes = [], $value = null, $chosen = null);

}