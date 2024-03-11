<?php
namespace MooWoodle;
class TestConnection {
	//user data for test
	public static $user_data = array();
	function __construct() {
		$response_data['message'] = __('Failed, please check the', 'moowoodle') . '<a href="' . admin_url("admin.php?page=moowoodle-settings&tab=moowoodle-log") . '"> ' . __('error log', 'moowoodle') . ' </a>';
		self::$user_data['email'] = 'moowoodletestuser@gmail.com';
		self::$user_data['username'] = 'moowoodletestuser';
		self::$user_data['password'] = 'Moowoodle@123';
		self::$user_data['auth'] = 'manual';
		$a = get_locale();
		$b = strtolower($a);
		self::$user_data['lang'] = substr($b, 0, 2);
		self::$user_data['firstname'] = 'moowoodle';
		self::$user_data['lastname'] = 'testuser';
		self::$user_data['city'] = 'moowoodlecity';
		self::$user_data['country'] = 'IN';
		self::$user_data['preferences'][0]['type'] = "auth_forcepasswordchange";
		self::$user_data['preferences'][0]['value'] = 1;
	}
	//test get site info
	public static function get_site_info($response_data) {
		$response = self::moowoodle_moodle_test_connection_callback('get_site_info');
		if ($response != null) {
			if (self::check_connection($response) == 'success') {
				$response_arr = json_decode($response['body'], true);
				$web_service_functions = array(
					'core_user_get_users',
					'core_user_update_users',
					'enrol_manual_enrol_users',
					'core_user_delete_users',
					'enrol_manual_unenrol_users',
					'core_course_get_courses_by_field',
					'core_course_get_courses',
					'core_user_create_users',
					'core_course_get_categories',
					'core_webservice_get_site_info',
				);
				$response_functions = array_map(function ($function) {
					return $function['name'];
				}, $response_arr['functions']);
				$missing_functions = array_diff($web_service_functions, $response_functions);
				if (!empty($missing_functions)) {
					Helper::MW_log( "\n\n        It seems that certain Moodle external web service functions are not configured correctly.\n        The missing functions following:" . wp_json_encode($missing_functions) . "\n\n");
				} else if (!MOOWOODLE_PRO_ADV) {
					if (!in_array('auth_moowoodle_user_sync_get_all_users_data', $response_functions) && defined( 'MOOWOODLE_PRO_PLUGIN_VERSION' ) && version_compare( MOOWOODLE_PRO_PLUGIN_VERSION, '1.0.5', '<' ) ) {
						Helper::MW_log( "\n\n        It seems that you are using MooWoodle Pro but Moodle external web service functions 'auth_moowoodle_user_sync_get_all_users_data' is not configured correctly.\n\n");
					} else if (!in_array('auth_moowoodle_user_sync', $response_functions) && defined( 'MOOWOODLE_PRO_PLUGIN_VERSION' ) && version_compare( MOOWOODLE_PRO_PLUGIN_VERSION, '1.0.5', '>=' ) ) {
						MWD()->dMW_log( "\n\n        It seems that you are using MooWoodle Pro but Moodle external web service functions 'auth_moowoodle_user_sync' is not configured correctly.\n\n");
					} else if ($response_arr['downloadfiles'] != 1) {
						Helper::MW_log( "\n\n        It seems that you are using MooWoodle Pro but Moodle external web service is not configured correctly. Please edit your Moodle External service and enable 'Can download files' (you can find it from 'Show more...' options)\n\n");
					} else {
						$response_data['message'] = 'success';
					}
				} else {
					$response_data['message'] = 'success';
				}
				update_option('moowoodle_moodle_site_name', $response_arr['sitename']);
			}
		}
		return $response_data;
	}
	//test get course
	public  static function get_course($response_data) {
		$response = self::moowoodle_moodle_test_connection_callback('get_courses');
		if ($response != null) {
			if (self::check_connection($response) == 'success') {
				$response_data['message'] = 'success';
				if($response_data['course_id']){
					$response_arr = json_decode($response['body'], true);
					if (!empty($response_arr)) {
						foreach ($response_arr as $course) {
							if ($course['format'] != 'site') {
								$response_data['course_id'] = $course['id'];
								$response_data['course_empty'] = '';
								break;
							}
							$response_data['course_empty'] = __('Set up a Moodle course to conduct the connection test.', 'moowoodle');
						}
					}
				}
			}
		}
		return $response_data;
	}
	// test get category
	public  static function get_catagory($response_data) {
		$response = self::moowoodle_moodle_test_connection_callback('get_categories');
		if ($response != null && self::check_connection($response) == 'success') {
			$response_data['message'] = 'success';
		}
		return $response_data;
	}
	//  test get course by fuild
	public  static function get_course_by_fuild($response_data) {
		$response = self::moowoodle_moodle_test_connection_callback('get_course_by_fuild');
		if ($response != null && self::check_connection($response) == 'success') {
			$response_arr = json_decode($response['body'], true);
			$response_data['message'] = 'success';
			if (!empty($response_arr)) {
				foreach ($response_arr['courses'] as $course) {
					if ($course['format'] != 'site') {
						$response_data['course_id'] = $course['id'];
						$response_data['course_empty'] = '';
						break;
					}
				}
			}
		}
		return $response_data;
	}
	public  static function create_user($response_data) {
		$response = self::moowoodle_moodle_test_connection_callback('create_users', array('users' => array(self::$user_data)));
		$response_data['message'] = esc_html(__('Failed, please check the', 'moowoodle')) . ' <a href="' . admin_url("admin.php?page=moowoodle-settings&tab=moowoodle-log") . '"> ' . esc_html(__('error log', 'moowoodle')) . ' </a>';
		if ($response != null && (self::check_connection($response) == 'success' || strpos(self::check_connection($response), 'Username already exists'))) {
			$response_data['message'] = 'success';
		}
		return $response_data;
	}
	// test get user
	public  static function get_user($response_data) {
		
		$response = self::moowoodle_moodle_test_connection_callback('get_moodle_users', array('criteria' => array(array('key' => 'email', 'value' => 'moowoodletestuser@gmail.com'))));
		if ($response != null && self::check_connection($response) == 'success') {
			$response_arr = json_decode($response['body'], true);
			$response_data['user_id'] = $response_arr['users'][0]['id'];
			$response_data['message'] = 'success';
		}
		return $response_data;
	}
	// test user update
	public  static function update_user($response_data) {
		self::$user_data['id'] = $response_data['user_id'];
		self::$user_data['city'] = 'citymoowoodle';
		if(self::$user_data){
			$response = self::moowoodle_moodle_test_connection_callback('update_users', array('users' => array(self::$user_data)));
			if ($response != null && self::check_connection($response) == 'success') {
				$response_data['message'] = 'success';
			}
		}
		return $response_data;
	}
	// test enrol users
	public  static function enrol_users($response_data) {
		
		$enrolment['courseid'] = $response_data['course_id'];
		$enrolment['userid'] = $response_data['user_id'];
		$enrolment['roleid'] = '5';
		if($enrolment['courseid'] && $enrolment['userid']){
			$response = self::moowoodle_moodle_test_connection_callback('enrol_users', array('enrolments' => array($enrolment)));
			if ($response != null && self::check_connection($response) == 'success') {
				$response_data['message'] = 'success';
			}
		}
		return $response_data;
	}
	// test unenrol users
	public  static function unenrol_users($response_data) {
		$enrolment['courseid'] = $response_data['course_id'];
		$enrolment['userid'] = $response_data['user_id'];
		if($enrolment['courseid'] && $enrolment['userid']){
			$response = self::moowoodle_moodle_test_connection_callback('unenrol_users', array('enrolments' => array($enrolment)));
			if ($response != null && self::check_connection($response) == 'success') {
				$response_data['message'] = 'success';
			}
		}
		return $response_data;
	}
	// test delete users
	public  static function delete_users($response_data) {
		self::$user_data['id'] = $response_data['user_id'];
		if(self::$user_data['id']){
			$response = self::moowoodle_moodle_test_connection_callback('delete_users', array('userids' => array(self::$user_data['id'])));
			if ($response != null && self::check_connection($response) == 'success') {
				$response_data['message'] = 'success';
			}
		}
		return $response_data;
	}
	/**
	 * get server resposne from moodle with externel service.
	 *
	 * @access private
	 * @param string $function_name (default: null)
	 * @param string $request_param (default: null)
	 * @return mixed
	 */
	private static function moowoodle_moodle_test_connection_callback($key = '', $request_param = array()) {
		$response = null;
		$function_name = "";
		$moodle_core_functions = array(
			'get_site_info' => 'core_webservice_get_site_info',
			'get_categories' => 'core_course_get_categories',
			'get_courses' => 'core_course_get_courses',
			'get_moodle_users' => 'core_user_get_users',
			'create_users' => 'core_user_create_users',
			'update_users' => 'core_user_update_users',
			'enrol_users' => 'enrol_manual_enrol_users',
			'get_course_by_fuild' => 'core_course_get_courses_by_field',
			'unenrol_users' => 'enrol_manual_unenrol_users',
			'delete_users' => 'core_user_delete_users',
		);
		if (array_key_exists($key, $moodle_core_functions)) {
			$function_name = $moodle_core_functions[$key];
		}
		$conn_settings = get_option('moowoodle_general_settings');
		$url = $conn_settings['moodle_url'];
		$token = $conn_settings['moodle_access_token'];
		$request_url = $url . '/webservice/rest/server.php?wstoken=' . $token . '&wsfunction=' . $function_name . '&moodlewsrestformat=json';
		if ($function_name == 'core_user_get_users') {
			$request_url = $request_url . '&criteria[0][key]=email&criteria[0][value]=%%';
		}
		if (!empty($url) && !empty($token) && $function_name != '') {
			$request_query = http_build_query($request_param);
			$response = wp_remote_post($request_url, array('body' => $request_query, 'timeout' => get_option('moowoodle_general_settings')['moodle_timeout']));
			if (isset($conn_settings['moowoodle_adv_log']) && $conn_settings['moowoodle_adv_log'] == 'Enable') {
				Helper::MW_log( "\n\n        moowoodle url:" . $request_url . '&' . $request_query . "\n        moowoodle response:" . wp_json_encode($response) . "\n\n");
			}
		}
		return $response;
	}
	/**
	 * check server resposne result .
	 *
	 * @access private
	 * @param string $response (default: null)
	 * @return string
	 */
	private static function check_connection($response) {
		$conn_settings = get_option('moowoodle_general_settings');
		$url_check = $error_massage = $error_codes = '';
		if (!is_wp_error($response) && $response != null && $response['response']['code'] == 200) {
			if (is_string($response['body'])) {
				$response_arr = json_decode($response['body'], true);
				if (json_last_error() === JSON_ERROR_NONE) {
					if (is_null($response_arr) || !array_key_exists('exception', $response_arr)) {
						return 'success';
					} else {
						if (str_contains($response_arr['message'], 'Access control exception')) {
							$url_check = '<a href="' . $conn_settings['moodle_url'] . '/admin/settings.php?section=externalservices">' . __('Link', 'moowoodle') . '</a>';
						}
						if (str_contains($response_arr['message'], 'Invalid token')) {
							$url_check = '<a href="' . $conn_settings['moodle_url'] . '/admin/webservice/tokens.php">' . __('Link', 'moowoodle') . '</a>';
						}
						$error_massage = $response_arr['message'] . ' ' . $url_check . $response_arr['debuginfo'];
					}
				} else {
					$error_massage = __('Response is not JSON decodeable', 'moowoodle');
				}
			} else {
				$error_massage = __('Not String response', 'moowoodle');
			}
		} elseif (!is_wp_error($response) && $response != null && isset($response['response']['code'])) {
			$error_msg = '';
			if ($response['response']['code'] == 404) {
				$error_msg = ' | Check Moodle URL | ';
			}
			if ($response['response']['code'] == 403) {
				$error_msg = ' | Ensure that Moodle web services and REST protocol are enabled. | ';
			}

			$error_massage = $error_msg . 'error code:' . $response['response']['code'] . '  ' . $response['response']['message'];
		} elseif ($response != null) {
			if (is_array($response->get_error_codes())) {
				foreach ($response->get_error_codes() as $error_code) {
					$error_codes .= $error_code;
				}
			} else {
				$error_codes .= $response->get_error_code();
			}
			$error_massage = $error_codes . $response->get_error_message();
		}
		Helper::MW_log( "\n        moowoodle error:" . $error_massage . "\n");
		return $error_massage;
	}
}
