<?php
namespace MooWoodle;
class Synchronize {
	/**
	 * Initiate sync process.
	 *
	 * @access public
	 * @return void
	 */
	public function sync_course($request) {
		// get the current setting.
        $sync_now_options = $request->get_param('data')['preSetting'];
		// sync category if enabled.
		if ($sync_now_options['sync_courses_category']) {
			$this->sync_categories();
		}
		// get all caurses from moodle.
		$courses = MooWoodle()->Helper->moowoodle_moodle_core_function_callback('get_courses');
		// sync courses post data.
		$this->update_posts($courses, 'course', 'course_cat', 'moowoodle_term');
		// sync product if enable.
		if ($sync_now_options['sync_all_product']) {
			$this->update_posts($courses, 'product', 'product_cat', 'woocommerce_term');
		}
		do_action('moowoodle_after_sync',$courses, $sync_now_options);
		return 'success';
	}
	/**
	 * Sync course categories from moodle.
	 *
	 * @access private
	 * @return void
	 */
	private function sync_categories() {
		// get all category from moodle.
		$categories = MooWoodle()->Helper->moowoodle_moodle_core_function_callback('get_categories');
		if ($categories !== null) {
			$this->update_categories($categories, 'course_cat', 'moowoodle_term');
			$this->update_categories($categories, 'product_cat', 'woocommerce_term');
		}
	}
	/**
	 * Update moodle course categories in Wordpress site.
	 *
	 * @access private
	 * @param array $categories
	 * @param string $taxonomy
	 * @param string $meta_key
	 * @return void
	 */
	private function update_categories($categories, $taxonomy, $meta_key) {
		if (empty($taxonomy) || empty($meta_key) || !taxonomy_exists($taxonomy)) {
			return;
		}
		$category_ids = array();
		if (!empty($categories)) {
			foreach ($categories as $category) {
				// find and getthe term id for category.
				$term_id = MooWoodle()->Course->moowoodle_get_term_by_moodle_id($category['id'], $taxonomy, $meta_key)->term_id;
				if (!$term_id) {
					$name = $category['name'];
					$description = $category['description'];
					$term = wp_insert_term($name, $taxonomy, array('description' => $description, 'slug' => "{$name} {$category['id']}"));
					if (!is_wp_error($term)) {
						add_term_meta($term['term_id'], '_category_id', $category['id'], false);
						add_term_meta($term['term_id'], '_parent', $category['parent'], false);
						add_term_meta($term['term_id'], '_category_path', $category['path'], false);
					} else {
						MooWoodle()->Helper->MW_log( "\n        moowoodle url:" . $term->get_error_message() . "\n");
					}
				} else {
					$term = wp_update_term($term_id, $taxonomy, array('name' => $category['name'], 'slug' => "{$category['name']} {$category['id']}", 'description' => $category['description']));
					if (!is_wp_error($term)) {
						update_term_meta($term['term_id'], '_parent', $category['parent'], '');
						update_term_meta($term['term_id'], '_category_path', $category['path'], false);
					} else {
						MooWoodle()->Helper->MW_log( "\n        moowoodle url:" . $term->get_error_message() . "\n");
					}
				}
				$category_ids[] = $category['id'];
			}
		}
		$terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false ));
		if ($terms) {
			foreach ($terms as $term) {
				$category_id = get_term_meta($term->term_id, '_category_id', true);
				if ( empty($category_id) ) continue;
				if (in_array($category_id, $category_ids)) {
					$parent = get_term_meta($term->term_id, '_parent', true);
					if ( empty($parent) ) continue; 
					$parent_term_id = MooWoodle()->Course->moowoodle_get_term_by_moodle_id($parent, $taxonomy, $meta_key)->term_id;
					wp_update_term($term->term_id, $taxonomy, array('parent' => $parent_term_id));
				} else {
					wp_delete_term($term->term_id, $taxonomy);
				}
			}
		}
	}
	/**
	 * Update moodle courses in Wordpress site.
	 *
	 * @access private
	 * @param array $courses
	 * @param string $post_type (default: null)
	 * @param string $taxonomy (default: null)
	 * @param string $meta_key (default: null)
	 * @return void
	 */
	private function update_posts($courses, $post_type = '', $taxonomy = '', $meta_key = '') {
		if (empty($post_type) || !post_type_exists($post_type) || empty($taxonomy) || !taxonomy_exists($taxonomy) || empty($meta_key)) {
			return;
		}
		$course_ids = array();
		if (!empty($courses)) {
			foreach ($courses as $course) {
				if ($course['format'] == 'site') {
					continue;
				}
				$post_id = $this->moowoodle_get_post_by_moodle_id($course['id'], $post_type);
				$post_status = 'publish';
				$args = array('post_title' => $course['fullname'],
					'post_name' => $course['shortname'],
					'post_content' => $course['summary'],
					'post_status' => $post_status,
					'post_type' => $post_type,
				);
				if ($post_id > 0) {
					$args['ID'] = $post_id;
					$new_post_id = wp_update_post($args);
				} else {	
					$new_post_id = wp_insert_post($args);
				}
				if ($new_post_id > 0) {
					if ($post_type == 'product') {
						$linked_course_id = $this->moowoodle_get_post_by_moodle_id($course['id'], 'course');
						update_post_meta($new_post_id, 'linked_course_id', $linked_course_id);
						update_post_meta($new_post_id, '_sku', 'course-' . (int) $course['id']);
						update_post_meta($new_post_id, '_virtual', 'yes');
						update_post_meta($new_post_id, '_sold_individually', 'yes');
					} else {
						$shortname = $course['shortname'];
						update_post_meta($new_post_id, '_course_short_name', sanitize_text_field($shortname));
						update_post_meta($new_post_id, '_course_idnumber', sanitize_text_field($course['idnumber']));
					}
					update_post_meta($new_post_id, '_course_startdate', $course['startdate']);
					update_post_meta($new_post_id, '_course_enddate', $course['enddate']);
					update_post_meta($new_post_id, 'moodle_course_id', (int) $course['id']);
					update_post_meta($new_post_id, '_category_id', (int) $course['categoryid']);
					update_post_meta($new_post_id, '_visibility', $visibility = ($course['visible']) ? 'visible' : 'hidden');
				}
				$course_ids[$course['id']] = $course['categoryid'];
			}
		}
		$posts = get_posts(array('post_type' => $post_type, 'numberposts' => -1, 'post_status' => 'publish'));
		if ($posts) {
			foreach ($posts as $post) {
				$course_id = get_post_meta($post->ID, 'moodle_course_id', true);
				if ( empty($course_id) ) continue;
				if (array_key_exists($course_id, $course_ids)) {
					$term_id = MooWoodle()->Course->moowoodle_get_term_by_moodle_id($course_ids[$course_id], $taxonomy, $meta_key)->term_id;
					if (!$term_id) continue;
					wp_set_post_terms($post->ID, $term_id, $taxonomy);
				} else {
					wp_delete_post($post->ID, false);
				}
			}
		}
	}

	/**
	 * Returns post id by moodle category id.
	 *
	 * @param int $course_id
	 * @param string $post_type (default: null)
	 * @return int
	 */
	private function moowoodle_get_post_by_moodle_id($course_id, $post_type = '') {
		if (empty($course_id) || !is_numeric($course_id) || empty($post_type) || !post_type_exists($post_type)) {
			return 0;
		}
		$posts = get_posts(array('post_type' => $post_type, 'numberposts' => -1,'meta_key' => 'moodle_course_id','meta_value' => $course_id,'meta_compare' => '=','fields' => 'ids',));
		if ( $posts ) {
			return $posts[0];
		}
		return 0;
	}
}
