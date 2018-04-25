<?php
/**
 * Plugin Name: ic Framework
 * Plugin URI:  http://inerciacreativa.com
 * Version:     4.4.1
 * Description: Framework para la elaboración de plugins.
 * Author:      Jose Cuesta
 * Author URI:  http://inerciacreativa.com/
 * Text Domain: ic-framework
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) {
    exit;
}

include_once __DIR__ . '/ic-framework/vendor/autoload.php';

use ic\Framework\Framework;

Framework::create(__FILE__, WPMU_PLUGIN_DIR);
