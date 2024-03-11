<?php
namespace MooWoodle;
class Admin {

	public $settings;

	public function __construct() {

		//admin script and style
		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_script'));
		add_action('moowoodle_admin_footer', array(&$this, 'moowoodle_admin_footer'));
		new Settings();
	}

	public function moowoodle_admin_footer() {

		?>
		<div style="clear: both"></div>
		<div class="dualcube-admin-footer" id="dualcube-admin-footer">
		<?php esc_html_e('Powered by', 'moowoodle');?> <a href="<?php echo esc_url(MOOWOODLE_DUALCUBE_URL); ?>" target="_blank"><img src="<?php echo esc_url(MOOWOODLE_PLUGIN_URL); ?>/assets/images/dualcube.png"></a><?php esc_html_e('ualCube', 'moowoodle');?> &copy; <?php echo esc_html(gmdate('Y')); ?>
		</div>
<?php
}

	/**
	 * Admin Scripts
	 */
	public function enqueue_admin_script() {
		//frontend js file
		$admin_frontend_args = array(
			'lang' => array(
				'warning_to_force_checked' => esc_html__('The \'Sync now\' option requires \'Moodle Courses\' to be enabled.', 'moowoodle'),
				'warning_to_save' => esc_html__('Remember to save your recent changes to ensure they\'re preserved.', 'moowoodle'),
				'Copy' => 'Copy',
				'Copied' => 'Copied',
			),
		);
		wp_enqueue_script('moowoodle_admin_frontend', plugins_url('../assets/admin/js/moowoodle-admin-frontend.js', __FILE__), array('jquery'), '', true);
		wp_localize_script('moowoodle_admin_frontend', 'admin_frontend_args', $admin_frontend_args);
		wp_enqueue_style('moowoodle_admin_css', MOOWOODLE_PLUGIN_URL . 'assets/admin/css/admin.css', array(), MOOWOODLE_PLUGIN_VERSION);
	}
}
