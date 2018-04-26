<?php

namespace ic\Framework\Settings\Page;

/**
 * Class NetworkPage
 *
 * @package ic\Framework\Settings\Page
 */
class NetworkPage extends CustomPage
{

    /**
     * @inheritdoc
     */
    protected function getHook()
    {
        return 'network_admin_menu';
    }

}