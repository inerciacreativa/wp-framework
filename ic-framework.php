<?php
/**
 * Plugin Name: ic Framework
 * Plugin URI:  https://github.com/inerciacreativa/wp-framework
 * Version:     4.0.3
 * Text Domain: ic-framework
 * Domain Path: /languages
 * Description: Framework para la elaboración de plugins.
 * Author:      Jose Cuesta
 * Author URI:  https://inerciacreativa.com/
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */

use ic\Framework\Framework;

if (!class_exists(Framework::class)) {
	$autoload = __DIR__ . '/vendor/autoload.php';

	if (file_exists($autoload)) {
		/** @noinspection PhpIncludeInspection */
		include_once $autoload;
	} else {
		throw new RuntimeException(sprintf('Could not load %s class.', Framework::class));
	}
}

Framework::create(__FILE__, WPMU_PLUGIN_DIR);
