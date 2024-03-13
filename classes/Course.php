<?php
namespace MooWoodle;
class Course {
	private $labels;
	private $endpoint_slug = '';
	public function __construct() {
		// define labels for resigster course post 
		// and category texonomy.
		$this->labels = array(
			'singular' => __('Course', 'moowoodle'),
			'plural' => __('Courses', 'moowoodle'),
			'menu' => __('Courses', 'moowoodle'),
		);
		// Register 'course' in post DB.
		$this->register_course_post_type();
		// Register 'course_cat' in taxonomy DB.
		$this->register_course_cat_taxonomy();
		//add Link Moodle Course in WooCommerce edit product tab.
		add_filter('woocommerce_product_data_tabs', array(&$this, 'moowoodle_linked_course_tab'), 99, 1);
		add_action('woocommerce_product_data_panels', array(&$this, 'moowoodle_linked_course_panals'));
		// add subcription product notice .
		add_filter('woocommerce_product_class', array($this, 'product_type_subcription_warning'), 10, 2);
		// Course meta save with WooCommerce product save
		add_action('woocommerce_process_product_meta', array(&$this, 'save_product_meta_data'));
		// define 'my_course' table enpoint.
		$this->add_my_courses_endpoint();
		// Resister 'my_course' end point page in WooCommerce 'my_account'.
		add_filter('woocommerce_account_menu_items', array($this, 'my_courses_page_link'));
		// Put endpoint containt. 
		add_action('woocommerce_account_' . $this->endpoint_slug . '_endpoint', array($this, 'woocommerce_account_my_courses_endpoint'));
		// load css for admin panel.
		add_action('wp_enqueue_scripts', array(&$this, 'frontend_styles'));
	}
	/**
	 * get function for Courses from post.
	 *
	 * @access public
	 * @param array $args
	 * @return array $courses
	 */
	public function mwd_get_courses ($args) {
		$args = array_merge(['post_type' => 'course', 'post_status' => 'publish'],$args);
		return get_posts($args);
	}
	/**
	 * Register 'course' in post DB.
	 *
	 * @access private
	 * @return void
	 */
	private function register_course_post_type() {
		$args = array(
			'labels' => array(
				'name' => sprintf(_x('%s', 'post type general name', 'moowoodle'), $this->labels['plural']),
				'singular_name' => sprintf(_x('%s', 'post type singular name', 'moowoodle'), $this->labels['singular']),
				'add_new' => sprintf(_x('Add New %s', 'course', 'moowoodle'), $this->labels['singular']),
				'add_new_item' => sprintf(__('Add New %s', 'moowoodle'), $this->labels['singular']),
				'edit_item' => sprintf(__('Edit %s', 'moowoodle'), $this->labels['singular']),
				'new_item' => sprintf(__('New %s', 'moowoodle'), $this->labels['singular']),
				'all_items' => sprintf(__('%s', 'moowoodle'), $this->labels['plural']),
				'view_item' => sprintf(__('View %s', 'moowoodle'), $this->labels['singular']),
				'search_items' => sprintf(__('Search %s', 'moowoodle'), $this->labels['plural']),
				'not_found' => sprintf(__('No %s found', 'moowoodle'), strtolower($this->labels['plural'])),
				'not_found_in_trash' => sprintf(__('No %s found in Trash', 'moowoodle'), strtolower($this->labels['plural'])),
			),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => true,
			'has_archive' => false,
			'hierarchical' => false,
			'show_in_menu' => false,
			'supports' => array('title', 'editor'),
			'capability_type' => 'post',
			'capabilities' => array('create_posts' => false,
				'delete_posts' => false,
			),
		);
		register_post_type('course', $args);
	}
	/**
	 * Register 'course_cat' in taxonomy DB.
	 *
	 * @access private
	 * @return void
	 */
	private function register_course_cat_taxonomy() {
		register_taxonomy('course_cat', 'course',
			array(
				'labels' => array(
					'name' => sprintf(_x('%s category', 'moowoodle'), $this->labels['singular']),
					'singular_name' => sprintf(_x('%s category', 'moowoodle'), $this->labels['singular']),
					'add_new_item' => sprintf(_x('Add new %s category', 'moowoodle'), $this->labels['singular']),
					'new_item_name' => sprintf(_x('New %s category', 'moowoodle'), $this->labels['singular']),
					'menu_name' => sprintf(_x('%s category', 'moowoodle'), $this->labels['singular']), //'Categories',
					'search_items' => sprintf(_x('Search %s categories', 'moowoodle'), $this->labels['singular']), //'Search Course Categories',
					'all_items' => sprintf(_x('All %s categories', 'moowoodle'), $this->labels['singular']), //'All Course Categories',
					'parent_item' => sprintf(_x('Parent %s category', 'moowoodle'), $this->labels['singular']), //'Parent Course Category',
					'parent_item_colon' => sprintf(_x('Parent %s category', 'moowoodle'), $this->labels['singular']), //'Parent Course Category:',
					'edit_item' => sprintf(_x('Edit %s category', 'moowoodle'), $this->labels['singular']), //'Edit Course Category',
					'update_item' => sprintf(_x('New %s category name', 'moowoodle'), $this->labels['singular']), //'New Course Category Name'
				),
				'show_ui' => false,
				'show_tagcloud' => false,
				'hierarchical' => true,
				'query_var' => true,
			)
		);
	}
	/**
	 * Creates custom tab for product types.
	 *
	 * @access public
	 * @param array $product_data_tabs
	 * @return void
	 */
	public function moowoodle_linked_course_tab($product_data_tabs) {
		$product_data_tabs['moowoodle'] = array(
			'label' => __('Moodle Linked Course', 'moowoodle'), // translatable
			'target' => 'moowoodle_course_link_tab', // translatable
		);
		return $product_data_tabs;
	}
	/**
	 * Add meta box panal.
	 *
	 * @access public
	 * @return void
	 */
	public function moowoodle_linked_course_panals() {
		echo '<div id="moowoodle_course_link_tab" class="panel woocommerce_options_panel">';
		global $post;
		$linked_course_id = get_post_meta($post->ID, 'linked_course_id', true);
		$courses = $this->mwd_get_courses(['numberposts' => -1, 'fields' => 'ids']);
		?>
		<p>
        <label for="courses"><?php esc_html_e('Linked Course', 'moowoodle');?></label>
        <select id="courses-select" name="course_id">
            <option value="0"><?php esc_html_e('Select course...', 'moowoodle');?></option>
            <?php
            if (!empty($courses)) {
                foreach ($courses as $course_id) {
                    $course_short_name = get_post_meta($course_id, '_course_short_name', true);
                    $course_path = array_map(function ($term) {
                        return $term->name;
                    }, get_the_terms($course_id, 'course_cat'));
                    $course_name = implode(' / ', $course_path);
                    $course_name .= ' - ' . esc_html(get_the_title($course_id));
                    ?>
                    <option value="<?php echo esc_attr($course_id); ?>" <?php selected($course_id, $linked_course_id); ?>>
                        <?php echo esc_html($course_name) . (!empty($course_short_name) ? " ( " . esc_html($course_short_name) . " )" : ''); ?>
                    </option>
                    <?php
                }
            }
            ?>
        </select>
    	</p>
		<?php
		echo esc_html_e("Cannot find your course in this list?", "moowoodle");
		?>
		<a href="<?php echo esc_url(get_site_url()) ?>/wp-admin/admin.php?page=moowoodle-synchronization" target="_blank"><?php esc_html_e('Synchronize Moodle Courses from here.', 'moowoodle');?></a>
		<?php
		// Nonce field (for security)
		echo '<input type="hidden" name="product_meta_nonce" value="' . wp_create_nonce() . '">';
		echo '</div>';
	}
	/**
	 * Add meta box panal.
	 *
	 * @access public
	 * @return void
	 */
	public function product_type_subcription_warning($php_classname, $product_type) {
		$active_plugins = (array) get_option('active_plugins', array());
		if (is_multisite()) {
			$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
		}

		if (in_array('woocommerce-subscriptions/woocommerce-subscriptions.php', $active_plugins) || array_key_exists('woocommerce-product/woocommerce-subscriptions.php', $active_plugins) || in_array('woocommerce-product-bundles/woocommerce-product-bundles.php', $active_plugins) || array_key_exists('woocommerce-product-bundles/woocommerce-product-bundles.php', $active_plugins)) {
			add_action('admin_notices', function(){
				if (MOOWOODLE_PRO_ADV) {
					echo '<div class="notice notice-warning is-dismissible"><p>' . __('WooComerce Subbcription and WooComerce Product Bundles is supported only with ', 'moowoodle') . '<a href="' . MOOWOODLE_PRO_SHOP_URL . '">' . __('MooWoodle Pro', 'moowoodle') . '</></p></div>';
				}
			});
		}
		return $php_classname;
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
		MWD()->Template->get_template(
			apply_filters('moowoodle_my_course_template_path', 'endpoints/my-course.php'),
			array(
				'table_heading' => $table_heading,
				'customer_orders' => $customer_orders,
				'customer' => $customer,
				'pwd' => $pwd,
			)
		);
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
	/**
     * Get All Caurse data.
     * @return \WP_Error| \WP_REST_Response
     */
    public function fetch_all_courses() {
		// get courses from post 
		$courses = $this->mwd_get_courses(['numberposts' => -1, 'fields' => 'ids']);
		$formatted_courses = [];
		// define pro banner class.
		$pro_popup_overlay = MOOWOODLE_PRO_ADV ? ' mw-pro-popup-overlay ' : '';
		foreach ($courses as $course_id) {
			$course_enddate = get_post_meta($course_id, '_course_enddate', true);
			//get term object by course category id.
			$term = Helper::moowoodle_get_term_by_moodle_id(get_post_meta($course_id, '_category_id', true), 'course_cat', '_category_id');
			$catagory_name = esc_html($term->name);
			$catagory_url = esc_url(admin_url('edit.php?course_cat=' . $term->slug)) . '&post_type=course';
			$moodle_url = esc_url(get_option('moowoodle_general_settings')["moodle_url"]) . 'course/edit.php?id=' . get_post_meta($course_id, 'moodle_course_id', true);
			$synced_products = [];
			// get all products lincked with course.
			$product_ids = get_posts(['post_type' => 'product', 'numberposts' => -1, 'post_status' => 'publish',  'fields' => 'ids', 'meta_key' => 'linked_course_id', 'meta_value' => $course_id]);
			$count_enrolment = 0;
			foreach ($product_ids as $product_id) {
				$synced_products[esc_html(get_the_title($product_id))] = esc_url(admin_url() . 'post.php?post=' . $product_id->ID . '&action=edit');
				$count_enrolment = $count_enrolment + get_post_meta($product_id, 'total_sales', true);
			}
			$date = wp_date('M j, Y', get_post_meta($course_id, '_course_startdate', true));
			if ($course_enddate) {
				$date .= ' - ' . wp_date('M j, Y  ', $course_enddate);
			}
			$actions = '<div class="moowoodle-course-actions"><input type="hidden" name="course_id" value="' . $course_id . '"/><button type="button" name="sync_courses" class="sync-single-course button-primary ' . $pro_popup_overlay . '" title="' . esc_attr('Sync Couse Data', 'moowoodle') . '"><i class="dashicons dashicons-update"></i></button>';
			if (!empty($product_ids)) {
				$actions .= '<button type="button" name="sync_update_product" class="update-existed-single-product button-secondary ' . $pro_popup_overlay . '" title="' . esc_attr('Sync Course Data & Update Product', 'moowoodle') . '"><i class="dashicons dashicons-admin-links"></i></button></div>';
			} else {
				$actions .= '<button type="button" name="sync_create_product" class="create-single-product button-secondary ' . $pro_popup_overlay . '" title="' . esc_attr('Create Product', 'moowoodle') . '"><i class="dashicons dashicons-cloud-upload"></i></button></div>';
			}
			$formatted_courses[] = [
				'id' => $course_id,
				'moodle_url' => $moodle_url,
				'course_name' => esc_html(get_the_title($course_id)),
				'course_short_name' => get_post_meta($course_id, '_course_short_name', true),
				'product' => $synced_products,
				'catagory_name' => $catagory_name,
				'catagory_url' => $catagory_url,
				'enroled_user' => $count_enrolment,
				'date' => $date,
				'actions' => $actions,
			];
		}
		return $formatted_courses;
	}	
}
