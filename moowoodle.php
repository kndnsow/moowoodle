<?php
/**
 * Plugin Name: MooWoodle
 * Plugin URI: https://dualcube.com/
 * Description: The MooWoodle plugin is an extention of WooCommerce that acts as a bridge between WordPress/Woocommerce and Moodle.
 * Author: DualCube
 * Version: 3.1.10
 * Author URI: https://dualcube.com/
 * Requires at least: 5.0
 * Tested up to: 6.4.3
 * WC requires at least: 8.2.2
 * WC tested up to: 8.6.1
 *
 * Text Domain: moowoodle
 * Domain Path: /languages/
 */
require_once trailingslashit(dirname(__FILE__)) . 'moowoodle-config.php';
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

function MWD() {
    return \MooWoodle\MooWoodle::init(__FILE__);
}

MWD();
