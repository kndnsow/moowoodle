<?php
/**
 * My Course (endpoint)
 *
 */
use MooWoodle\Helper;


if (!defined('ABSPATH')) {
	exit;
}

if (count($customer_orders) > 0) {
?>
<p>
    <div class="auto">
        <table class="table table-bordered responsive-table moodle-linked-courses widefat">
            <thead>
                <tr>
                    <?php foreach ($table_heading as $key_heading => $value_heading) : ?>
                        <th><?php echo $value_heading; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($customer_orders as $order) {
                    $unenrolled_course = $order->get_meta('_course_unenroled', true);
                    $unenrolled_courses = $unenrolled_course ? explode(',', $unenrolled_course) : [];
                    foreach ($order->get_items() as $enrolment) {
                        $moodle_course_id = get_post_meta($enrolment->get_product_id(), 'moodle_course_id', true);
                        $enrolment_date = $order->get_meta('moodle_user_enrolment_date', true);
                        $linked_course_id = get_post_meta($enrolment->get_product_id(), 'linked_course_id', true);

                        if ($linked_course_id && !in_array($moodle_course_id, $unenrolled_courses)) :
                            ?>
                            <tr>
                                <td><?php echo esc_html(get_the_title($enrolment->get_product_id())); ?></td>
                                <td><?php echo esc_html($customer->user_login); ?></td>
                                <?php if($pwd) :?>
                                <td><?php echo esc_html($pwd); ?></td>
                                <?php endif; ?>
                                <td>
                                    <?php
                                    if (!empty($enrolment_date)) {
                                        echo esc_html(get_date_from_gmt(gmdate('M j, Y-H:i', $enrolment_date)));
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button type="button" class="button-tri">
                                        <?php echo apply_filters('moodle_course_view_url', MooWoodle()->Course->get_moowoodle_course_url($moodle_course_id, 'View'), $moodle_course_id); ?>
                                        <span class="dashicons dashicons-visibility"></span>
                                    </button>
                                </td>
                            </tr>
                        <?php
                        endif;
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</p>
<?php
} else {
?>
    <h3><?php esc_html_e('You have no Course.', 'moowoodle'); ?></h3>
    <h3><?php esc_html_e('Kindly purchase a Course and come back here to see your course.', 'moowoodle'); ?></h3>
<?php
}
