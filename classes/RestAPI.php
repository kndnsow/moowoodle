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
            'callback' => array(MooWoodle()->Course, 'fetch_all_courses'),
            'permission_callback' => array($this, 'moowoodle_permission'),
        ]);
        register_rest_route('moowoodle/v1', '/test-connection', [
            'methods' => \WP_REST_Server::EDITABLE,
            'callback' => array($this, 'test_connection'),
            'permission_callback' => array($this, 'moowoodle_permission'),
        ]);
        register_rest_route('moowoodle/v1', '/sync-course-options', [
            'methods' => \WP_REST_Server::EDITABLE,
            'callback' => array(MooWoodle()->Synchronize, 'sync_course'),
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
     * Test Connection with moodle server.
     * @param mixed $request
     * @return array
     */
    public function test_connection($request) {
        $request_data = $request->get_param('data');
        $action = $request_data['action'];
        $response = TestConnection::$action($request_data);
        return $response;
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
        return $logs;
    }
}
