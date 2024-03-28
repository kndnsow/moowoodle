<?php
namespace MooWoodle;

if (!defined('ABSPATH')) exit;
class RestAPI {
    function __construct() {
        if (current_user_can('manage_options')) {
            add_action('rest_api_init', array($this, 'register_restAPI'));
        }
    }
    /**
     * Rest api register function call on rest_api_init action hook.
     * @return void
     */
    public function register_restAPI() {
        register_rest_route('moowoodle/v1', '/save-moowoodle-setting', [
            'methods' => \WP_REST_Server::EDITABLE,
            'callback' => array($this, 'save_moowoodle_setting'),
            'permission_callback' => array($this, 'moowoodle_permission'),
        ]);
        register_rest_route('moowoodle/v1', '/fetch-all-courses', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array($this, 'fetch_all_courses'),
            'permission_callback' => array($this, 'moowoodle_permission'),
        ]);
        register_rest_route('moowoodle/v1', '/test-connection', [
            'methods' => \WP_REST_Server::EDITABLE,
            'callback' => array($this, 'test_connection'),
            'permission_callback' => array($this, 'moowoodle_permission'),
        ]);
        register_rest_route('moowoodle/v1', '/sync-course-options', [
            'methods' => \WP_REST_Server::EDITABLE,
            'callback' => array($this, 'synchronize'),
            'permission_callback' => array($this, 'moowoodle_permission'),
        ]);
        register_rest_route('moowoodle/v1', '/fetch-mw-log', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array($this, 'mw_get_log'),
            'permission_callback' => array($this, 'moowoodle_permission'),
        ]);
    }

    /**
     * MooWoodle api permission function.
     * @return bool
     */
    public function moowoodle_permission() {
        return current_user_can('manage_options');
    }
    /**
     * Seve the setting set in react's admin setting page.
     * @param mixed $request
     * @return array
     */
    public function save_moowoodle_setting($request) {
        // setting option value.
        $setting = $request->get_param('setting');
        // setting option key.
        $settingid = $request->get_param('settingid');
        update_option($settingid,$setting);
        return 'success';
    }
    /**
     * Seve the setting set in react's admin setting page.
     * @param mixed $request
     * @return array
     */
    public function synchronize( $request ) {
		// get the current setting.
        $sync_now_options = $request->get_param('data')['preSetting'];
        // initiate Synchronisation
        // sync category if enabled.
		if ($sync_now_options['sync_courses_category']) {
			// get all category from moodle.
			$categories = MooWoodle()->ExternalService->do_request('get_categories');

			// update course and product categories
			$this->update_categories( $categories, 'course_cat' );
			$this->update_categories( $categories, 'product_cat' );
		}

		// get all caurses from moodle.
		$courses = MooWoodle()->ExternalService->do_request('get_courses');

		// update all course and product
		foreach ($courses as $course){
			$course_ids = $product_ids = [];

			// sync courses post data.
			$course_id = MooWoodle()->Course->update_course($course);
			if($course_id) $course_ids[] = $course_id;

			// sync product if enable.
			if ($sync_now_options['sync_all_product']) {
				$product_id= MooWoodle()->Product->update_product($course,);
				if($product_id) $product_ids[] = $product_id;
			}
		}

		// remove courses that not exist in moodle.
		MooWoodle()->Course->remove_exclude_ids($course_ids);


		if ($sync_now_options['sync_all_product']) {
			// remove product that not exist in moodle.
			MooWoodle()->Product->remove_exclude_ids($product_ids);
		}

        rest_ensure_response(apply_filters('moowoodle_after_sync','success',$courses, $sync_now_options));
    }
    /**
     * Seve the setting set in react's admin setting page.
     * @param mixed $request
     * @return array
     */
    public function fetch_all_courses() {
        $response = MooWoodle()->Course->fetch_all_courses(['numberposts' => -1, 'fields' => 'ids']);
        rest_ensure_response($response);
    }
    /**
     * Test Connection with moodle server.
     * @param mixed $request
     * @return array
     */
    public function test_connection($request) {
        $request_data = $request->get_param('data');
        $action = $request_data['action'];
        $response = TestConnection::$action($request_data);
        rest_ensure_response($response);
    }
    /**
     * Seve the setting set in react's admin setting page.
     * @param mixed $request
     * @return array
     */
    public function mw_get_log() {
        $logs = [];
        if (file_exists(MW_LOGS . "/error.txt")) {
            $logs = explode("\n", wp_remote_retrieve_body(wp_remote_get(get_site_url(null, str_replace(ABSPATH, '', MW_LOGS) . "/error.txt"))));
        }
        rest_ensure_response($logs);
    }
    
}
