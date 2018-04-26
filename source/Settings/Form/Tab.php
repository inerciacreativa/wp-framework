<?php

namespace ic\Framework\Settings\Form;

use ic\Framework\Settings\Page\CustomPage;
use ic\Framework\Html\Tag;

/**
 * Class Tab
 *
 * @package ic\Framework\Settings\Form
 */
class Tab
{

    use SectionsDecorator;

    /**
     * @var CustomPage
     */
    protected $page;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * Tab constructor.
     *
     * @param CustomPage $page
     * @param string     $id
     * @param \Closure   $content
     */
    public function __construct(CustomPage $page, $id, \Closure $content)
    {
        $this->id   = $id;
        $this->page = $page;

        $this->sections($this->page);

        $content($this);
    }

    /**
     * Retrieves the tab ID.
     *
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Sets the tab title.
     *
     * @param $title
     *
     * @return $this
     */
    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Retrieves the link to this tab.
     *
     * @return string
     */
    public function link()
    {
        $url = add_query_arg([
            'page' => $this->page->id(),
            'tab'  => $this->id,
        ], admin_url($this->page->parent()));

        $class = 'nav-tab';

        if ($this->sections->registered()) {
            $class .= ' nav-tab-active';
        }

        return Tag::a(['href' => $url, 'class' => $class], $this->title ?: $this->id);
    }

}