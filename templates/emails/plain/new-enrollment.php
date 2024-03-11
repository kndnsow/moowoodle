<?php
/**
 * New enrollment email (plain)
 *
 */

use MooWoodle\Helper;

if (!defined('ABSPATH')) {
	exit;
}

$i = 0;
$enrollment_list = array();
$user_details = get_user_by('email', $user_data);
$pwd = get_user_meta($user_details->data->ID, 'moowoodle_moodle_user_pwd', true);
if (!get_user_meta($user_id, 'moowoodle_moodle_user_id')) {
	echo esc_html('Username : ', 'moowoodle') . esc_html($user_details->data->user_login) . '\n\n';
	echo esc_html('Password : ', 'moowoodle') . esc_html($pwd) . '\n\n';
}
echo esc_html('To enroll and access your course please click on the course link given below :', 'moowoodle') . '\n\n';
foreach ($enrollments['enrolments'] as $enrollment) {
	$enrollment_list[] = Helper::get_moowoodle_course_url($enrollment['courseid'], $enrollment['course_name']);
	echo esc_html('You are enrolled in ' . $enrollment_list[$i]) . ' \n\n';
	$i++;
}
if (!get_user_meta($user_id, 'moowoodle_moodle_user_id')) {
	echo esc_html('You need to change your password after first login.', 'moowoodle') . '\n\n';
}
