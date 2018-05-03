<?php
/**
 * Plugin Name: ic Framework
 * Plugin URI:  https://github.com/inerciacreativa/wp-framework
 * Version:     2.0.2
 * Text Domain: ic-framework
 * Domain Path: /languages
 * Description: Framework para la elaboración de plugins.
 * Author:      Jose Cuesta
 * Author URI:  https://inerciacreativa.com/
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */

include_once __DIR__ . '/ic-framework/vendor/autoload.php';

ic\Framework\Framework::create(__FILE__, WPMU_PLUGIN_DIR);