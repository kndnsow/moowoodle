<?php
namespace MooWoodle;
class MyAccountEndPoint {
    private $endpoint_slug = '';
	public function __construct() {
		// define 'my_course' table enpoint.
		$this->add_my_courses_endpoint();
		// Resister 'my_course' end point page in WooCommerce 'my_account'.
		add_filter('woocommerce_account_menu_items', array($this, 'my_courses_page_link'));
		// Put endpoint containt. 
		add_action('woocommerce_account_' . $this->endpoint_slug . '_endpoint', array($this, 'woocommerce_account_my_courses_endpoint'));
    }
	/**
	 * Save product meta.
	 *
	 * @access public
	 * @param int $post_id
	 * @return void
	 */
	public function save_product_meta_data($post_id) {
		// Security check
		if (!filter_input(INPUT_POST, 'product_meta_nonce', FILTER_DEFAULT) === null || !wp_verify_nonce(filter_input(INPUT_POST, 'product_meta_nonce', FILTER_DEFAULT)) || !current_user_can('edit_product', $post_id)) {
			return $post_id;
		}
		$course_id = filter_input(INPUT_POST, 'course_id', FILTER_DEFAULT);
		if ($course_id !== null) {
			update_post_meta($post_id, 'linked_course_id', wp_kses_post($course_id));
			update_post_meta($post_id, '_sku', 'course-' . get_post_meta($course_id, '_sku', true));
			update_post_meta($post_id, 'moodle_course_id', get_post_meta($course_id, 'moodle_course_id', true));
		}
	}
	/**
	 *Adds my-courses endpoints table heade
	 *
	 * @access private
	 * @return void
	 */
	private function add_my_courses_endpoint() {
		$this->endpoint_slug = 'my-courses';	
		add_rewrite_endpoint($this->endpoint_slug, EP_ROOT | EP_PAGES);
		flush_rewrite_rules();
	}
	/**
	 * resister my course to my-account WooCommerce menu.
	 *
	 * @access public
	 * @return void
	 */
	public function my_courses_page_link($menu_links) {
		$name = __('My Courses', 'moowoodle');
		$new = array($this->endpoint_slug => $name);
		$display_settings = get_option('moowoodle_display_settings');
		if (isset($display_settings['my_courses_priority'])) {
			$priority_below = $display_settings['my_courses_priority'];
		} else {
			$priority_below = 0;
		}
		$menu_links = array_slice($menu_links, 0, $priority_below + 1, true)
		 + $new
		 + array_slice($menu_links, $priority_below + 1, NULL, true);
		return $menu_links;
	}
	/**
	 * Add meta box panal.
	 *
	 * @access public
	 * @return void
	 */
	public function woocommerce_account_my_courses_endpoint() {
		$customer = wp_get_current_user();
		$customer_orders = wc_get_orders([
			'numberposts' => -1,
			'orderby' => 'date',
			'order' => 'DESC',
			'type' => 'shop_order',
			'status' => 'wc-completed',
			'customer_id' => $customer->ID,
		]);
		$table_heading = array(
			__("Course Name", 'moowoodle'),
			__("Moodle User Name", 'moowoodle'),
			__("Enrolment Date", 'moowoodle'),
			__("Course Link", 'moowoodle'),
		);
		$pwd = get_user_meta($customer->ID, 'moowoodle_moodle_user_pwd', true);
		if ($pwd) {
			array_splice($table_heading, 2, 0, __("Password (First Time use Only)", 'moowoodle'));
		}
		// Render my-course template.
		MooWoodle()->Template->get_template(
			apply_filters('moowoodle_my_course_template_path', 'endpoints/my-course.php'),
			array(
				'table_heading' => $table_heading,
				'customer_orders' => $customer_orders,
				'customer' => $customer,
				'pwd' => $pwd,
			)
		);
		// load css for admin panel.
		add_action('wp_enqueue_scripts', array($this, 'frontend_styles'));
	}
	/**
	 * Add meta box panal.
	 *
	 * @access public
	 * @return void
	 */
	public function frontend_styles() {
		$suffix = defined('MOOWOODLE_SCRIPT_DEBUG') && MOOWOODLE_SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style('frontend_css', MOOWOODLE_PLUGIN_URL . 'assets/frontend/css/frontend' . $suffix . '.css', array(), MOOWOODLE_PLUGIN_VERSION);
	}

}
