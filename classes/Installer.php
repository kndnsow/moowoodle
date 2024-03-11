<?php

namespace MooWoodle;

defined('ABSPATH') || exit;

/**
 * plugin Install
 *
 * @version		3.1.7
 * @package		MooWoodle
 * @author 		DualCube
 */
class Installer {
	/**
	 * Construct installation.
	 *
	 * @access public
	 * @return void
	 */
    public function __construct() {
        if (get_option('moowoodle_version') != MOOWOODLE_PLUGIN_VERSION) {
			self::install();
			do_action('moowoodle_updated');
		}
    }
    /**
	 * Install plugin.
	 *
	 * @access public
	 * @return void
	 */
	public function install() {
		self::create_options();
	}
	/**
	 * Create and Update options.
	 * 
	 * @access public
	 * @return void
	 */
	public function create_options() {
		add_option('moowoodle_version', MOOWOODLE_PLUGIN_VERSION);
		update_option('woocommerce_registration_generate_username', 'no');
		update_option('woocommerce_enable_guest_checkout', 'no');
	}
}
