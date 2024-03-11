<?php
namespace MooWoodle;
class Settings {
	private $page = '';
	/*
	* Start up
	*/
	public function __construct() {
		//Admin menu
		add_action('admin_menu', array($this, 'add_settings_page'));
	}

	/**
	 * Add Option page
	 */
	public function add_settings_page() {
		$menu = Library::get_settings_menu();
		add_menu_page(
			"MooWoodle",
			"MooWoodle",
			'manage_options',
			'moowoodle',
			array($this, 'create_settings_page'),
			esc_url(MOOWOODLE_PLUGIN_URL) . 'assets/images/moowoodle.png',
			50
		);
		foreach ($menu as $menu_slug => $menu_names) {
			add_submenu_page(
				'moowoodle',
				$menu_names['name'],
				$menu_names['name'],
				'manage_options',
				'moowoodle#&tab=' . $menu_slug . '&sub-tab=' . $menu_names['default_tab'],
				'_-return_null'
			);
		}
		
		wp_enqueue_script(
			'mwd-build-admin-frontend',
			MOOWOODLE_PLUGIN_URL . 'build/index.js',
			['wp-element'],
			time(),
			true
		);
		wp_localize_script(
			'mwd-build-admin-frontend',
			'MooWoodleAppLocalizer',
			[
				'admin_url' => get_admin_url(),
				'side_banner_img' => esc_url(plugins_url()) .'/moowoodle/assets/images/logo-moowoodle-pro.png',
				'library' => Library::moowoodle_get_options(),
				'porAdv' => MOOWOODLE_PRO_ADV,
				'preSettings' => [
					'moowoodle_general_settings' => get_option('moowoodle_general_settings'),
					'moowoodle_display_settings' => get_option('moowoodle_display_settings'),
					'moowoodle_sso_settings' => get_option('moowoodle_sso_settings'),
					'moowoodle_synchronize_settings' => get_option('moowoodle_synchronize_settings'),
					'moowoodle_synchronize_now' => get_option('moowoodle_synchronize_now'),
				],
				'MW_Log' => MW_LOGS.'/error.txt',
				'rest_url' => esc_url_raw(rest_url()),
                'nonce'	=> wp_create_nonce('wp_rest'),
				'pro_sticker' => MOOWOOLE_PRO_STICKER,
				'pro_popup_overlay' => MOOWOODLE_PRO_ADV ? ' mw-pro-popup-overlay ' : '',
				'shop_url' => MOOWOODLE_PRO_SHOP_URL,
				'from_heading' => array(
					'<label hidden><span>' . esc_html__('Select All', 'moowoodle') . '</span></label><input type="checkbox" class="bulk-action-select-all" name="bulk_action_seclect_all">',
					esc_html__('Course', 'moowoodle'),
					esc_html__('Short Name', 'moowoodle'),
					esc_html__('Product', 'moowoodle'),
					esc_html__('Category', 'moowoodle'),
					esc_html__('Enrolled', 'moowoodle'),
					esc_html__('Start Date - End Data', 'moowoodle'),
					esc_html__('Actions', 'moowoodle') ,
				),
				'bulk_actions_label' => esc_html__('Select bulk action', 'moowoodle'),
				'bulk_actions' => esc_html__('Bulk Actions', 'moowoodle'),
				'sync_course' => esc_html__('Sync Course', 'moowoodle'),
				'create_product' => esc_html__('Create Product', 'moowoodle'),
				'update_product' => esc_html__('Update Product', 'moowoodle'),
				'apply' => esc_html__('Apply', 'moowoodle'),
				'search_course' => esc_html__('Search Course', 'moowoodle'),
				'cannot_find_course' => esc_html__('Cannot find your course in this list?', 'moowoodle'),
				'sync_moodle_courses' => esc_html__('Synchronize Moodle Courses from here.', 'moowoodle'),
				'testconnection_actions' => [
					'get_site_info' => __('Connecting to Moodle', 'moowoodle'),
					'get_catagory' => __('Course Category Sync', 'moowoodle'),
					'get_course_by_fuild' => __('Course Data Sync', 'moowoodle'),
					'get_course' => __('Course Sync', 'moowoodle'),
					'create_user' => __('User Creation', 'moowoodle'),
					'get_user' => __('User Data Sync', 'moowoodle'),
					'update_user' => __('User Data Update', 'moowoodle'),
					'enrol_users' => __('User Enrolment', 'moowoodle'),
					'unenrol_users' => __('User Unenrolment', 'moowoodle'),
					'delete_users' => __('All Test', 'moowoodle'),
				],
				'lang' => [
					'warning_to_force_checked' => esc_html__('The \'Sync now\' option requires \'Moodle Courses\' to be enabled.', 'moowoodle'),
					'warning_to_save' => esc_html__('Remember to save your recent changes to ensure they\'re preserved.', 'moowoodle'),
					'Copy' => 'Copy',
					'Copied' => 'Copied',
				],
			],
		);
		do_action('moowoodle_upgrade_to_pro_admin_menu_hide');
		if (MOOWOODLE_PRO_ADV) {
			add_submenu_page(
				'moowoodle',
				__("Upgrade to Pro", 'moowoodle'),
				'<div class="upgrade-to-pro"><i class="dashicons dashicons-awards"></i>' . esc_html__("Upgrade to Pro", 'moowoodle') . '</div> ',
				'manage_options',
				'',
				array($this, 'handle_external_redirects')
			);
		}
	}
	public function create_settings_page() {
		$this->page = filter_input(INPUT_GET, 'page', FILTER_DEFAULT) !== null ? filter_input(INPUT_GET, 'page', FILTER_DEFAULT) : '';?>
		<div class="mw-admin-dashbord <?php echo $this->page; ?>">
			<div class="mw-general-wrapper" id ="moowoodle_root">
				
        	</div>
      	</div>
      	<?php
		// MooWoodle admin footer
		do_action('moowoodle_admin_footer');

	}
	// Upgrade to pro link
	public function handle_external_redirects() {
		wp_redirect(esc_url(MOOWOODLE_PRO_SHOP_URL));
		die;
	}
}
