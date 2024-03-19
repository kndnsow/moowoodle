<?php
namespace MooWoodle;
class Library {
	public $pro_sticker = MOOWOODLE_PRO_ADV ? '<span class="mw-pro-tag">Pro</span>' : '' ;
	public static function get_settings_menu() {
		$moowoodle_menu = [
			"moowoodle-all-courses" => ['name' => __("All Courses", 'moowoodle'), 'default_tab' => 'moowoodle-linked-courses'],
			"moowoodle-manage-enrolment" => ['name' => __("Manage Enrolment", 'moowoodle') . MOOWOOLE_PRO_STICKER, 'default_tab' => 'moowoodle-manage-enrolment'],
			"moowoodle-settings" => ['name' => __("Settings", 'moowoodle'), 'default_tab' => 'moowoodle-general'],
			"moowoodle-synchronization" => ['name' => __("Synchronization", 'moowoodle'), 'default_tab' => 'moowoodle-sync-options'],
		];
		return $moowoodle_menu;
	}
	public static function moowoodle_get_options() {
		$user_sync_running_cron_batch = apply_filters('moowoodle_sync_user_corn_info', '');
		$sync_wordpress_users = apply_filters('add_moowoodle_sync_wordpress_users_roles','');
		$conn_settings = get_option('moowoodle_general_settings');
		$url = isset($conn_settings['moodle_url']) ? $conn_settings['moodle_url'] : '';
		$woocom_new_user_mail = '<a href="'. site_url() .'/wp-admin/admin.php?page=wc-settings&tab=email&section=moowoodle_emails_new_enrollment">here!</a>';
		if ($url != null) {
			$moodle_tokens_url = '<a href="' . $url . '/admin/webservice/tokens.php"> ' . __('Manage tokens', 'moowoodle') . '</a>';
			$moodle_sso_url = '<a href="' . $url . '/admin/settings.php?section=auth_moowoodleconnect"> ' . __('Moodle', 'moowoodle') . '</a>';
			$moowoodle_sync_setting_url = '<a href="' . get_admin_url() . '/admin.php?page=moowoodle#&tab=moowoodle-synchronization&sub-tab=moowoodle-sync-options"> ' . __('Synchronization Settings', 'moowoodle') . '</a>';
		} else {
			$moodle_tokens_url = __('Manage tokens', 'moowoodle');
			$moodle_sso_url = __('Moodle', 'moowoodle');
			$moowoodle_sync_setting_url = __('Moodle', 'moowoodle');
		}
		$account_menu_array = array();
		$i = 0;
		foreach ( wc_get_account_menu_items() as $key => $value) {
			$account_menu_array[$i] = $value;
			$i++;
		}
		$moowoodle_options = array(
			"moowoodle-all-courses" => array(
				"moowoodle-linked-courses" => array(
					"label" => __("Moodle Courses", 'moowoodle'),
					"font_class" => "dashicons-welcome-learn-more",
					"setting" => "moowoodle_course_settings",
					"field_types" => array(
						"moowoodle-link-course-table" => array(
							"type" => "section",
							"label" => __("Courses", 'moowoodle'),
						),
						"all_course_nolabel" => array(
							"type" => "all-course-nolabel",
							"label" => __("", 'moowoodle'),
							"desc" => __("", 'moowoodle'),
							"option_values" => array(
								'Enable' => __('', 'moowoodle'),
							),
						),
					),
				),
			),
			"moowoodle-manage-enrolment" => array(
				"moowoodle-manage-enrolment" => array(
					"label" => __("Enrolment", 'moowoodle'),
					"font_class" => "dashicons-analytics",
					"setting" => "manage_enrolmente_settings",
					"field_types" => array(
						"moowoodle-manage-enrolment" => array(
							"type" => "section",
							"label" => __("All Enrolments", 'moowoodle'),
						),
						"manage_enrolment_nolabel" => array(
							"type" => "manage-enrolment-nolabel",
							"is_pro" => true,
							"label" => __("", 'moowoodle'),
							"desc" => __("", 'moowoodle'),
							"option_values" => array(
								'Enable' => __('', 'moowoodle'),
							),
						),
					),
				),
			),
			"moowoodle-settings" => array(
				"moowoodle-general" => array(
					"label" => __("General", 'moowoodle'),
					"font_class" => "dashicons-admin-generic",
					"setting" => "moowoodle_general_settings",
					"field_types" => array(
						"moowoodle-connection" => array(
							"type" => "section",
							"label" => __("Connection Settings", 'moowoodle'),
						),
						"moodle-url" => array(
							"type" => "textbox",
							'name' => "moodle_url",
							"label" => __("Moodle Site URL", 'moowoodle'),
							"desc" => __('Enter the Moodle Site URL', 'moowoodle'),
						),
						"moodle-access-token" => array(
							"type" => "textbox",
							"name" => "moodle_access_token",
							"label" => __("Moodle Access Token", 'moowoodle'),
							"desc" => __('Enter Moodle Access Token. You can generate the Access Token from - Dashboard => Site administration => Server => Web services => ' . $moodle_tokens_url, 'moowoodle'),
						),
						"test-connection" => array(
							"type" => "empty-div",
							"desc_posi" => "up",
							"name" => "test_connection",
							"submit_btn_value" => __('Test Connection', 'moowoodle'),
							"label" => __("Mooowoodle Test Connection", 'moowoodle'),
							"desc" => __("Refer to the", 'moowoodle') . ' <a href="' . esc_url(MOOWOODLE_SETUP_URL) . '"> ' . esc_html__('setup guide', 'moowoodle') . ' </a>' . esc_html__('to complete all necessary configurations on the Moodle site, and subsequently, perform a Test Connection to verify the functionality of all services.', 'moowoodle'),
							"option_values" => array(
								'Enable' => __('', 'moowoodle'),
							),
						),
						"moowoodle-user-information" => array(
							"type" => "section",
							"label" => __("User Information Exchange Settings", 'moowoodle'),
						),
						"update-moodle-user" => array(
							"type" => "toggle-checkbox",
							"name" => "update_moodle_user",
							"label" => __("Force Override Moodle User Profile", 'moowoodle'),
							"desc" => __('If enabled, all moodle user\'s profile data (first name, last name, city, address, etc.) will be updated as per their wordpress profile data. Explicitly, for existing user, their data will be overwritten on moodle.', 'moowoodle'),
							"option_values" => array(
								'Enable' => __('', 'moowoodle'),
							),
						),
						"moowoodle-system-settings" => array(
							"type" => "section",
							"label" => __("System Settings", 'moowoodle'),
						),
						"moodle-timeout" => array(
							"type" => "textbox",
							"name" => "moodle_timeout",
							"label" => __("Timeout", 'moowoodle'),
							"desc" => __('Set Curl connection time out in sec.', 'moowoodle'),
						),
						"moowoodle-adv-log" => array(
							"type" => "toggle-checkbox",
							"name" => "moowoodle_adv_log",
							"label" => __("Advance Log", 'moowoodle'),
							"desc" => __('These setting will record all advanced error informations. Please don\'t Enable it if not required, because it will create a large log file.', 'moowoodle'),
							"option_values" => array(
								'Enable' => __('', 'moowoodle'),
							),
						),
					),
				),
				"moowoodle-display" => array(
					"id" => "moowoodle-display",
					"label" => __("Display", 'moowoodle'),
					"font_class" => "dashicons-welcome-view-site",
					"setting" => "moowoodle_display_settings",
					"field_types" => array(
						"moowoodle-display" => array(
							"type" => "section",
							"label" => __("Display Settings", 'moowoodle'),
						),
						"start-end-date" => array(
							"type" => "toggle-checkbox",
							"name" => "start_end_date",
							"label" => __("Display Start Date and End Date in Shop Page", 'moowoodle'),
							"desc" => __('If enabled display start date and end date in shop page.', 'moowoodle'),
							"option_values" => array(
								'Enable' => __('', 'moowoodle'),
							),
						),
						"my-courses-priority" => array(
							"type" => "select",
							"name" => "my_courses_priority",
							"label" => __("My Courses Menu Position", 'moowoodle'),
							"desc" => __('Select below which menu the My Courses Menu will be displayed', 'moowoodle'),
							"option_values" => $account_menu_array,
						),
					),
				),
				"moowoodle-SSO" => array(
					"is_pro" => true,
					"label" => __("SSO ", 'moowoodle'),
					"font_class" => "dashicons-admin-multisite",
					"setting" => "moowoodle_sso_settings",
					"field_types" => array(
						"moowoodle-sso" => array(
							"type" => "section",
							"label" => __("Single Sing On Settings", 'moowoodle'),
						),
						"moowoodle-sso-eneble" => array(
							"type" => "toggle-checkbox",
							'name' => "moowoodle_sso_eneble",
							"is_pro" => true,
							"label" => __("Single Sing On", 'moowoodle'),
							"desc" => __('If enabled Moodle user\'s will login by WordPress user', 'moowoodle'),
							"option_values" => array(
								'Enable' => __('', 'moowoodle'),
							),
						),
						"moowoodle-sso-secret-key" => array(
							"type" => "textbox",
							"name" => "moowoodle_sso_secret_key",
							"is_pro" => true,
							"copy_text" => "copy",
							"label" => __("SSO Secret Key", 'moowoodle'),
							"desc" => __('Enter SSO Secret Key it should be same as ' . $moodle_sso_url . ' SSO Secret Key', 'moowoodle'),
						),
					),
				),
				"moowoodle-notification" => array(
					"is_pro" => true,
					"label" => __("Notification ", 'moowoodle'),
					"font_class" => "dashicons-bell",
					"setting" => "moowoodle_notification_settings",
					"field_types" => array(
						"moowoodle-notification" => array(
							"type" => "section",
							"label" => __("Manage Notification", 'moowoodle'),
						),
						"moowoodle-create-user-custom-mail-eneble" => array(
							"type" => "toggle-checkbox",
							'name' => "moowoodle_create_user_custom_mail",
							"is_pro" => true,
							"label" => __("Customize New User Registration Email", 'moowoodle'),
							"desc" => __('If this option is enabled, default WordPress new user registration emails will be disabled for both admin and user. Our custom New User Registration email will be sent to the newly registered user. You can personalize the content of the MooWoodle New User email from ', 'moowoodle') . $woocom_new_user_mail,
							"option_values" => array(
								'Enable' => __('', 'moowoodle'),
							),
						),
					),
				),
				"moowoodle-log" => array(
					"id" => "moowoodle-log",
					"label" => __("Log", 'moowoodle'),
					"font_class" => "dashicons-welcome-write-blog",
					"setting" => "moowoodle_log",
					"field_types" => array(
						"moowoodle-log-table" => array(
							"type" => "section",
							"label" => __("Log", 'moowoodle'),
						),
						"log" => array(
							"type" => "log",
							"label" => __("", 'moowoodle'),
							"desc" => __("", 'moowoodle'),
							"option_values" => array(
								'Enable' => __('', 'moowoodle'),
							),
						),
					),
				),
			),
			"moowoodle-synchronization" => array(
				"moowoodle-sync-options" => array(
					"label" => __("Synchronization Settings", 'moowoodle'),
					"font_class" => "dashicons-admin-links",
					"setting" => "moowoodle_synchronize_settings",
					"field_types" => array(
						"moowoodle-sync-settings" => array(
							"type" => "section",
							"label" => __("Synchronization Options", 'moowoodle'),
						),
						"sync-real-time-user-options" => array(
							"type" => "multiple-checkboxs",
							"label" => __("Realtime User Sync", 'moowoodle'),
							"desc" => __("Activate this feature for effortless user synchronization between Moodle and WordPress. As soon as a new user is added on one platform, our system dynamically syncs their profile to the other, accompanied by email notifications. This ensures users are promptly informed, creating a seamlessly unified experience across both platforms.", 'moowoodle') ,
							"option_values" => array(
								'Moodle ⇒ WordPress' => array(
									"id" => "realtime-sync-moodle-users",
									"name" => "realtime_sync_moodle_users",
									"desc" => __("", 'moowoodle'),
									"is_pro" => true,
								),
								'WordPress ⇒ Moodle' => array(
									"id" => "realtime-sync-wordpress-users",
									"name" => "realtime_sync_wordpress_users",
									"desc" => __("", 'moowoodle'),
									"is_pro" => true,
								),
							),
						),
						"sync-user-options" => array(
							"type" => "multiple-checkboxs",
							"label" => __("User Information", 'moowoodle'),
							"desc" => __("Determine User Information to Synchronize in Moodle-WordPress User synchronization. Please be aware that this setting does not apply to newly created users.", 'moowoodle'),
							"desc_posi" => "up",
							"note" => "<b>Note:</b> We're updating the WordPress password hashing method to ensure compatibility with Moodle. Rest assured, no user data is compromised, and this won't impact the login procedure on your site.",
							"option_values" => array(
								'First Name' => array(
									"id" => "sync-user-first-name",
									"name" => "sync_user_first_name",
									"desc" => __("", 'moowoodle'),
									"is_pro" => true,
								),
								'Last Name' => array(
									"id" => "sync-user-last-name",
									"name" => "sync_user_last_name",
									"desc" => __("", 'moowoodle'),
									"is_pro" => true,
								),
								'Username' => array(
									"id" => "sync-username",
									"name" => "sync_username",
									"desc" => __("", 'moowoodle'),
									"is_pro" => true,
								),
								'Password' => array(
									"id" => "sync-password",
									"name" => "sync_password",
									"desc" => __("", 'moowoodle'),
									"is_pro" => true,
								),
								
							),
						),
					),
				),
				"moowoodle-sync-now" => array(
					"label" => __("Synchronize Now", 'moowoodle'),
					"font_class" => "dashicons-admin-links",
					"setting" => "moowoodle_synchronize_now",
					"field_types" => array(
						"moowoodle-sync-now" => array(
							"type" => "section",
							"label" => __("Synchronize Option", 'moowoodle'),
						),
						"sync-all-user-options" => array(
							"type" => "select",
							"name" => "sync_users_now",
							"submit_btn_value" => __('Sync All Users Now', 'moowoodle'),
							"label" => __("Existing Users", 'moowoodle'),
							"desc" => __("<b>Prior to updating existing user info, you must select the user info to be synchronized at </b>", 'moowoodle') . $moowoodle_sync_setting_url . __("<br><br>While synchronizing user information, we use the email address as the unique identifier for each user. We check the username associated with that email address, and if we find the same username in the other instance but with a different email address, the user's information cannot be synchronized.", 'moowoodle') ,
							"desc_posi" => "up",
							"is_pro" => true,
							"option_values" => array(
								'Moodle &rArr; WordPress' => array(
									"id" => "sync-moodle-users",
									"name" => "sync_moodle_users",
									"desc" =>$user_sync_running_cron_batch . __("", 'moowoodle'),
								),
								'WordPress &rArr; Moodle' => array(
									"id" => "sync-wordpress-users",
									"name" => "sync_wordpress_users",
									"desc" => $user_sync_running_cron_batch . $sync_wordpress_users . __("<br>To update passwords for users created before activating the plugin, they must log into WorPress site to migrate their passwords to the new hashing method. After this step, it will synchronize their passwords from WordPress to Moodle.If the user doesn't log in, all other fields will be synchronized except for the password.", 'moowoodle'),
								),
							),
						),
						"sync-course-options" => array(
							"type" => "multiple-checkboxs",
							"label" => __("Courses", 'moowoodle'),
							"name" => "sync_course_now",
							"submit_btn_value" => __('Sync Courses Now', 'moowoodle'),
							"desc" => __("Choose the category you wish to synchronize from Moodle to your WordPress site. During synchronization, if a course is found deleted in Moodle, it will likewise remove the corresponding course and product data from WordPress.", 'moowoodle'),
							"option_values" => array(
								'Moodle Courses' => array(
									"id" => "sync-courses",
									"name" => "sync_courses",
									"desc" => __("This function will retrieve all Moodle course data and synchronize it with the courses listed in WordPress.", 'moowoodle'),
									"checked" => "forced",
								),
								'Moodle Course Categories' => array(
									"id" => "sync-course-category",
									"name" => "sync_courses_category",
									"desc" => __("This feature will scan the entire Moodle course category structure and synchronize it with the WordPress category listings.", 'moowoodle'),
								),
								'Create and Update Products' => array(
									"id" => "sync-all-product",
									"name" => "sync_all_product",
									"desc" => __("This feature allows you to update previously created product information using Moodle course data. NOTE: This action will overwrite all existing product details with those from Moodle course details.", 'moowoodle'),
								),
								'Create New Products' => array(
									"id" => "sync-new-products",
									"name" => "sync_new_products",
									"desc" => __("This functionality enables automatic creation of new products based on Moodle course data if they do not already exist in WordPress.", 'moowoodle'),
									"is_pro" => true,
								),
								'Update Existing Products' => array(
									"id" => "sync-exist-product",
									"name" => "sync_exist_product",
									"desc" => __("This feature allows you to update previously created product information using Moodle course data. NOTE: This action will overwrite all existing product details with those from Moodle course details.", 'moowoodle'),
									"is_pro" => true,
								),
								'Course Images' => array(
									"id" => "sync-image",
									"name" => "sync_image",
									"desc" => __("This function copies course images and sets them as WooCommerce product images.", 'moowoodle'),
									"is_pro" => true,
								),
							),
						),
					),
				),
			),
		);
		return apply_filters('moowoodle_fileds_options', $moowoodle_options);
	}
}
