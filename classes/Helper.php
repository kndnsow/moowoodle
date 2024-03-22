<?php

namespace MooWoodle;

defined('ABSPATH') || exit;

/**
 * plugin Helper functions
 *
 * @version		3.1.7
 * @package		MooWoodle
 * @author 		DualCube
 */
class Helper {
	/**
     * MooWoodle LOG function.
     *
     * @param string $message
     * @return bool
     */
	public static function MW_log($message) {
		global $wp_filesystem;
		if (empty($wp_filesystem)) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		if ($wp_filesystem) {
			// log folder create
			if (!file_exists(MW_LOGS . "/error.txt")) {
				wp_mkdir_p(MW_LOGS);
				$message = "MooWoodle Log file Created\n";
			}
			// Clear log file
			if (filter_input(INPUT_POST, 'clearlog', FILTER_DEFAULT) !== null) {
				$message = "MooWoodle Log file Cleared\n";
			}
			// Write Log
			if($message != ''){
				$log_entry = gmdate("d/m/Y H:i:s", time()) . ': ' . $message;
				$existing_content = $wp_filesystem->get_contents(get_site_url(null, str_replace(ABSPATH, '', MW_LOGS) . "/error.txt"));
				if (!empty($existing_content)) {
					$log_entry = "\n" . $log_entry;
				}
				return $wp_filesystem->put_contents(MW_LOGS . "/error.txt", $existing_content . $log_entry );
			}
		}
		return false;
	}
}
