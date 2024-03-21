<?php
namespace MooWoodle;
class Course {
	private $labels;
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
		add_filter('woocommerce_product_class', array(&$this, 'product_type_subcription_warning'), 10, 2);
		// Course meta save with WooCommerce product save
		add_action('woocommerce_process_product_meta', array(&$this, 'save_product_meta_data'));
	}
	/**
	 * get function for Courses from post.
	 *
	 * @access public
	 * @param array $args
	 * @return array $courses
	 */
	public static function mwd_get_courses ($args = []) {
		$args = array_merge(['post_type' => 'course', 'post_status' => 'publish'],$args);
		return get_posts($args);
	}
	/**
	 * Returns term by moodle category id
	 *
	 * @param int $category_id
	 * @param string $taxonomy (default: null)
	 * @param string $meta_key (default: null)
	 * @return object
	 */
	public static function moowoodle_get_term_by_moodle_id($category_id, $taxonomy = '') {
		if (empty($category_id) || !is_numeric($category_id) || empty($taxonomy) || !taxonomy_exists($taxonomy) ) {
			return 0;
		}
		$terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false,  'meta_query' => ['key' => '_category_id', 'value' => $category_id,]));
		if ($terms && !is_wp_error($terms)) {
			return $terms[0];
		}
		return 0;
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
     * Get All Caurse data.
     * @return \WP_Error| \WP_REST_Response
     */
    public function fetch_all_courses( $arg = [] ) {
		// get courses from post
		$default_arg = ['numberposts' => -1, 'fields' => 'ids'];
		$args = is_array($arg) ? array_merge($arg, $default_arg) : $default_arg;
		$courses = $this->mwd_get_courses($args);
		$formatted_courses = [];
		foreach ($courses as $course_id) {
			$course_meta = array_map('current', get_post_meta($course_id,'',true));
			$course_enddate = $course_meta['_course_enddate'];
			//get term object by course category id.
			$term = $this->moowoodle_get_term_by_moodle_id($course_meta['_category_id'], 'course_cat');
			$catagory_name = esc_html($term->name);
			$catagory_url = add_query_arg(['course_cat' => $term->slug, 'post_type' => 'course'], admin_url('edit.php'));
			$moodle_course_id = $course_meta['moodle_course_id'];
			$moodle_url = esc_url(get_option('moowoodle_general_settings')["moodle_url"]) . 'course/edit.php?id=' . $moodle_course_id;
			$synced_products = [];
			// get all products lincked with course.
			$products = get_posts(['post_type' => 'product', 'numberposts' => -1, 'post_status' => 'publish', 'meta_key' => 'linked_course_id', 'meta_value' => $course_id]);
			$count_enrolment = 0;
			foreach ($products as $product) {
				$synced_products[esc_html(get_the_title($product))] = add_query_arg( [ 'post' => $product->ID , 'action' => 'edit'],admin_url('post.php'));
				$count_enrolment = $count_enrolment + (int) get_post_meta($product->ID, 'total_sales', true);
			}
			$date = wp_date('M j, Y', $course_meta['_course_startdate']);
			if ($course_enddate) {
				$date .= ' - ' . wp_date('M j, Y  ', $course_enddate);
			}
			$formatted_courses[] = [
				'id' => $course_id,
				'moodle_course_id' => $moodle_course_id,
				'moodle_url' => $moodle_url,
				'course_name' => esc_html(get_the_title($course_id)),
				'course_short_name' => $course_meta['_course_short_name'],
				'product' => $synced_products,
				'catagory_name' => $catagory_name,
				'catagory_url' => $catagory_url,
				'enroled_user' => $count_enrolment,
				'date' => $date,
			];
		}
		return $formatted_courses;
	}
}
