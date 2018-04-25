<?php

namespace ic\Framework\Settings\Page;

/**
 * Class SitePage
 *
 * @package ic\Framework\Settings\Page
 */
class SitePage extends CustomPage
{

    /**
     * @inheritdoc
     */
    protected function getHook()
    {
        return 'admin_menu';
    }

}