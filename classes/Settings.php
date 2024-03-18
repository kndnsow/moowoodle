<?php
namespace MooWoodle;
class Settings {
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
		wp_enqueue_style(
			'moowoodle_admin_css',
			MOOWOODLE_PLUGIN_URL . 'build/index.css', array(),
			MOOWOODLE_PLUGIN_VERSION
		);
		wp_enqueue_script(
			'mwd-build-admin-frontend',
			MOOWOODLE_PLUGIN_URL . 'build/index.js',
			['wp-element', 'wp-i18n'],
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
				'manage_enrolment_img_url' => esc_url(plugins_url())."/moowoodle/assets/images/manage-enrolment.jpg",
				'lang' => [
					'warning_to_force_checked' => esc_html__('The \'Sync now\' option requires \'Moodle Courses\' to be enabled.', 'moowoodle'),
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
	// create the root page for load react.
	public static function create_settings_page() {
		$page = filter_input(INPUT_GET, 'page', FILTER_DEFAULT) !== null ? filter_input(INPUT_GET, 'page', FILTER_DEFAULT) : '';?>
		<div class="mw-admin-dashbord <?php echo $page; ?>">
			<div class="mw-general-wrapper" id ="moowoodle_root">
				
        	</div>
      	</div>
      	<?php
	}
	// Upgrade to pro redirection
	public function handle_external_redirects() {
		wp_redirect(esc_url(MOOWOODLE_PRO_SHOP_URL));
		die;
	}
}
