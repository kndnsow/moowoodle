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
			$this->migration();
			$this->set_options();
			do_action('moowoodle_updated');
		}
    }
    /**
	 * Plugin migration.
	 *
	 * @access public
	 * @return void
	 */
	public static function migration() {
		$version = get_option('dc_moowoodle_plugin_db_version');
		if ($version) {
			// in update 3.1.4 migrate 'moowoodle_synchronize_settings' to moowoodle_synchronize_now.
			if (version_compare($version, '3.1.3' ,'<=')) {
				$old_settings = get_option('moowoodle_synchronize_settings');
				if ($old_settings) {
					update_option('moowoodle_synchronize_now', $old_settings);
					delete_option('moowoodle_synchronize_settings');
				}
			}
			// in update 3.1.9 product meta changed from single to array. 
			if(version_compare($version, '3.1.9' ,'=')){
				foreach (wc_get_products(array('return' => 'ids')) as $product_id) {
					$moodle_course_id = get_post_meta($product_id, 'moodle_course_id', true);
					if (is_array($moodle_course_id) && !empty($moodle_course_id)) {
						update_post_meta($product_id, 'moodle_course_id', $moodle_course_id[0]);
					}
				}
			}
			// in update 3.1.11 change chackbox settings data from 'Enable' to true/flase. 
			if(version_compare($version, '3.1.12' ,'<')){
				$options =[
					'moowoodle_general_settings' => [ 'update_moodle_user', 'moowoodle_adv_log' ],
					'moowoodle_display_settings' => [ 'start_end_date' ],
					'moowoodle_sso_settings' => [ 'moowoodle_sso_eneble' ],
					'moowoodle_notification_settings' => [ 'moowoodle_create_user_custom_mail' ],
					'moowoodle_synchronize_settings' => [ 'realtime_sync_moodle_users', 'realtime_sync_wordpress_users',
						'sync_user_first_name', 'sync_user_last_name', 'sync_username', 'sync_password', ],
					'moowoodle_synchronize_now' => [ 'sync_courses', 'sync_courses_category', 'sync_all_product',
						'sync_new_products', 'sync_exist_product', 'sync_image', ],
				];
				foreach($options as $option_key => $option_value){
					$settings = get_option($option_key);
					$settings = $settings ? $settings : [];
					foreach($option_value as $index => $settings_key){
						$settings[$settings_key] = $settings[$settings_key] ? true : false;
					}
					update_option($option_key, $settings);
				}
			}
			update_option('dc_moowoodle_plugin_db_version', MOOWOODLE_PLUGIN_VERSION);
		}
	}
	/**
	 * Create and Update options.
	 * 
	 * @access public
	 * @return void
	 */
	private function set_options() {
		add_option('moowoodle_version', MOOWOODLE_PLUGIN_VERSION);
		update_option('woocommerce_registration_generate_username', 'no');
		update_option('woocommerce_enable_guest_checkout', 'no');
	}
}
