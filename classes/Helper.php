<?php

namespace MooWoodle;

defined('ABSPATH') || exit;

/**
 * plugin Helper functions
 *
 * @version		3.1.7
 * @package		MooWoodle
 * @author 		DualCube
 */
class Helper {
	/**
	* Call to moodle core functions.
	*
	* @param string $function_name (default: null)
	* @param string $request_param (default: null)
	* @return mixed
	*/
	public static function moowoodle_moodle_core_function_callback($key = '', $request_param = array()) {
		$response = null;
		$function_name = "";
		$moodle_core_functions = array('get_categories' => 'core_course_get_categories',
			'get_courses' => 'core_course_get_courses',
			'get_moodle_users' => 'core_user_get_users',
			'create_users' => 'core_user_create_users',
			'update_users' => 'core_user_update_users',
			'enrol_users' => 'enrol_manual_enrol_users',
			'get_course_image' => 'core_course_get_courses_by_field',
			'unenrol_users' => 'enrol_manual_unenrol_users',
			'get_all_users_data' => 'auth_moowoodle_user_sync_get_all_users_data',
			'sync_users_data' => 'auth_moowoodle_user_sync',
		);
		if (array_key_exists($key, $moodle_core_functions)) {
			$function_name = $moodle_core_functions[$key];
		}
		$conn_settings = get_option('moowoodle_general_settings');
		$url = $conn_settings['moodle_url'];
		$token = $conn_settings['moodle_access_token'];
		$request_url = rtrim($url, '/') . '/webservice/rest/server.php?wstoken=' . $token . '&wsfunction=' . $function_name . '&moodlewsrestformat=json';
		if ($function_name == 'core_user_get_users') {
			$request_url = $request_url . '&criteria[0][key]=email&criteria[0][value]=%%';
		}
		if (!empty($url) && !empty($token) && $function_name != '') {
			$request_query = http_build_query($request_param);
			$response = wp_remote_post($request_url, array('body' => $request_query, 'timeout' => $conn_settings['moodle_timeout']));
			if(isset($conn_settings['moowoodle_adv_log']) && $conn_settings['moowoodle_adv_log'] == 'Enable'){
				Helper::MW_log( "\n\n        moowoodle url:" . $request_url . '&' . $request_query . "\n        moowoodle response:" . wp_json_encode($response) . "\n\n");
			}
		}
		$url_check = $error_massage = '';
		if (!is_wp_error($response) && $response != null && $response['response']['code'] == 200) {
			if (is_string($response['body'])) {
				$response_arr = json_decode($response['body'], true);
				if (json_last_error() === JSON_ERROR_NONE) {
					if (is_null($response_arr) || !array_key_exists('exception', $response_arr)) {
						return $response_arr;
					} else {
						if (str_contains($response_arr['message'], 'Access control exception')) {
							$url_check = '<a href="' . $conn_settings['moodle_url'] . '/admin/settings.php?section=externalservices">Link</a>';
						}
						if (str_contains($response_arr['message'], 'Invalid token')) {
							$url_check = '<a href="' . $conn_settings['moodle_url'] . '/admin/webservice/tokens.php">Link</a>';
						}
						$error_massage = $response_arr['message'] . ' ' . $url_check;
					}
				} else {
					$error_massage = __('Response is not JSON decodeable', 'moowoodle');
				}
			} else {
				$error_massage = __('Not String response', 'moowoodle');
			}
		} else {
			
			$error_codes = '';
			if(is_object($response) && is_array($response->get_error_code())) {
				foreach($response->get_error_code() as $error_code) {
					$error_codes .= $error_code;
				}
				$error_massage =  $error_codes. $response->get_error_message();
			} elseif (is_array($response)) {
				$error_codes .= $response['response']['code'];
				$error_massage =  $error_codes. $response['response']['message'];
			} else {
				$error_codes .= $response->get_error_code();
				$error_massage =  $error_codes. $response->get_error_message();
			}
		}
		Helper::MW_log( "\n        moowoodle error:" . $error_massage . "\n");
		return null;
	}
    /**
     * Admin notice for woocommerce deactive
     */
    public static function woocommerce_admin_notice() {
		?>
		<div id="message" class="error">
		<p><?php printf(__('%sMooWoodle is inactive.%s The %sWooCommerce plugin%s must be active for the MooWoodle to work. Please %sinstall & activate WooCommerce%s', 'moowoodle'), '<strong>', '</strong>', '<a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url('plugins.php') . '">', '&nbsp;&raquo;</a>');?></p>
		</div>
    	<?php
	}
    /**
     * Plugin page links
     */
    public static function moowoodle_plugin_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=moowoodle-settings') . '">' . __('Settings', 'moowoodle') . '</a>',
            '<a href="' . MOOWOODLE_SUPPORT_URL . '">' . __('Support', 'moowoodle') . '</a>',
        );
        $links = array_merge($plugin_links, $links);
        if (apply_filters('moowoodle_upgrage_to_pro', true)) {
            $links[] = '<a href="' . MOOWOODLE_PRO_SHOP_URL . '" target="_blank" style="font-weight: 700;background: linear-gradient(110deg, rgb(63, 20, 115) 0%, 25%, rgb(175 59 116) 50%, 75%, rgb(219 75 84) 100%);-webkit-background-clip: text;-webkit-text-fill-color: transparent;">' . __('Upgrade to Pro', 'moowoodle') . '</a>';
        }
        return $links;
    }
	/**
     * MooWoodle LOG function.
     *
     * @param string $message
     * @return bool
     */
	public static function MW_log($message) {
		global $wp_filesystem;
		if (empty($wp_filesystem)) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		if ($wp_filesystem) {
			// log folder create
			if (!file_exists(MW_LOGS . "/error.txt")) {
				wp_mkdir_p(MW_LOGS);
				$wp_filesystem->put_contents(MW_LOGS . "/error.txt", gmdate("d/m/Y H:i:s", time()) . ': ' . "MooWoodle Log file Created\n");
			}
			// Clear log file
			if (filter_input(INPUT_POST, 'clearlog', FILTER_DEFAULT) !== null) {
				$wp_filesystem->put_contents(MW_LOGS . "/error.txt", gmdate("d/m/Y H:i:s", time()) . ': ' . "MooWoodle Log file Cleared\n");
			}
			// Write Log
			if($message != ''){
				$log_entry = gmdate("d/m/Y H:i:s", time()) . ': ' . $message;
				$existing_content = $wp_filesystem->get_contents(get_site_url(null, str_replace(ABSPATH, '', MW_LOGS) . "/error.txt"));
				if (!empty($existing_content)) {
					$log_entry = "\n" . $log_entry;
				}
				$new_content = $existing_content . $log_entry;
				return $wp_filesystem->put_contents(MW_LOGS . "/error.txt", $new_content);
			}
		}
		return false;
	}
    /**
     * Take action based on if woocommerce is not loaded
     * @return void
     */
    public static function is_woocommerce_loaded_notice() {
        if ( did_action( 'woocommerce_loaded' ) || ! is_admin() ) {
            return;
        }
        add_action('admin_notices', [ Helper::class , 'woocommerce_admin_notice']);
		if (filter_input(INPUT_POST, 'page', FILTER_DEFAULT) == 'moowoodle-settings') {
			?>
			<div style="text-align: center; padding: 20px; height: 100%">
				<h2><?php echo __('Warning: Activate WooCommerce and Verify Moowoodle Files', 'moowoodle'); ?></h2>
				<p><?php echo __('To access Moowoodle, please follow these steps:', 'moowoodle'); ?></p>
				<ol style="text-align: left; margin-left: 40px;">
					<li><?php echo __('Activate WooCommerce on your <a href="', 'moowoodle') . home_url() . '/wp-admin/plugins.php'; ?>"><?php echo __('website', 'moowoodle'); ?></a><?php echo __(', if it\'s not already activated.', 'moowoodle'); ?></li>
					<li><?php echo __('Ensure that all Moowoodle files are present in your WordPress installation.', 'moowoodle'); ?></li>
					<li><?php echo __('If you suspect any missing files, consider reinstalling Moowoodle to resolve the issue.', 'moowoodle'); ?></li>
				</ol>
				<p><?php echo __('After completing these steps, refresh this page to proceed.', 'moowoodle'); ?></p>
			</div>
			<?php
		}
    }
	public static function get_moowoodle_course_url($moodle_course_id, $course_name) {
		$course = $moodle_course_id;
		$class = "moowoodle";
		$target = '_blank';
		$content = $course_name;
		$conn_settings = get_option('moowoodle_general_settings');
		$redirect_uri = $conn_settings['moodle_url'] . "/course/view.php?id=" . $course;
		$url = '<a target="' . esc_attr($target) . '" class="' . esc_attr($class) . '" href="' . $redirect_uri . '">' . $content . '</a>';
		return $url;
	}
	/**
	 * Returns term id by moodle category id
	 *
	 * @param int $category_id
	 * @param string $taxonomy (default: null)
	 * @param string $meta_key (default: null)
	 * @return object
	 */
	public static function moowoodle_get_term_by_moodle_id($category_id, $taxonomy = '', $meta_key = '') {
		if (empty($category_id) || !is_numeric($category_id) || empty($taxonomy) || !taxonomy_exists($taxonomy) || empty($meta_key)) {
			return 0;
		}
		$terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false, 'meta_key' => $meta_key, 'meta_value' => $category_id));
		// echo '<pre>';print_r(var_export($terms,true));die;
		if ($terms && !is_wp_error($terms)) {
			return $terms[0];
		}
		return 0;
	}
	public static function moowoodle_migration() {
		if (get_option('dc_moowoodle_plugin_db_version')) {
			// in update 3.1.4 migrate 'moowoodle_synchronize_settings' to moowoodle_synchronize_now.
			if (version_compare(get_option('dc_moowoodle_plugin_db_version'), '3.1.3' ,'<=')) {
				$old_settings = get_option('moowoodle_synchronize_settings');
				if ($old_settings) {
					update_option('moowoodle_synchronize_now', $old_settings);
					delete_option('moowoodle_synchronize_settings');
				}
			}
			// in update 3.1.9 product meta changed from single to array. 
			if(version_compare(get_option('dc_moowoodle_plugin_db_version'), '3.1.9' ,'=')){
				foreach (wc_get_products(array('return' => 'ids')) as $product_id) {
					$moodle_course_id = get_post_meta($product_id, 'moodle_course_id', true);
					if (is_array($moodle_course_id) && !empty($moodle_course_id)) {
						update_post_meta($product_id, 'moodle_course_id', $moodle_course_id[0]);
					}
				}
			}
			// in update 3.1.11 change chackbox settings data from 'Enable' to true/flase. 
			if(version_compare(get_option('dc_moowoodle_plugin_db_version'), '3.1.11' ,'<')){
				$options =[
					'moowoodle_general_settings' => [
						'update_moodle_user',
						'moowoodle_adv_log'
					],
					'moowoodle_display_settings' => [
						'start_end_date'
					],
					'moowoodle_sso_settings' => [
						'moowoodle_sso_eneble'
					],
					'moowoodle_notification_settings' => [
						'moowoodle_create_user_custom_mail'
					],
					'moowoodle_synchronize_settings' => [
						'realtime_sync_moodle_users',
						'realtime_sync_wordpress_users',
						'sync_user_first_name',
						'sync_user_last_name',
						'sync_username',
						'sync_password',
					],
					'moowoodle_synchronize_now' => [
						'sync_courses',
						'sync_courses_category',
						'sync_all_product',
						'sync_new_products',
						'sync_exist_product',
						'sync_image',

					],
				];
				foreach($options as $option_key => $option_value){
					$option_value = [];
					$settings = get_option($option_key);
					foreach($option_value as $settings_key){
						$settings[$settings_key] = isset($settings[$settings_key]) && $settings[$settings_key] == 'Enable' ? true : false;
					}
					update_option($option_key, $settings);
				}
			}
		}
        update_option('dc_moowoodle_plugin_db_version', MOOWOODLE_PLUGIN_VERSION);
	}
}
