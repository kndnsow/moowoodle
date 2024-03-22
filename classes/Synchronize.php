<?php
namespace MooWoodle;
class Synchronize {
	/**
	 * Initiate sync process.
	 *
	 * @access public
	 * @return void
	 */
	public function initiate($sync_now_options) {
		// sync category if enabled.
		if ($sync_now_options['sync_courses_category']) {
			// get all category from moodle.
			$categories = MooWoodle()->ExternalService->do_request('get_categories');

			// update course and product categories
			$this->update_categories( $categories, 'course_cat' );
			$this->update_categories( $categories, 'product_cat' );
		}

		// get all caurses from moodle.
		$courses = MooWoodle()->ExternalService->do_request('get_courses');

		// update all course and product
		foreach ($courses as $course){
			$course_ids = $product_ids = [];

			// sync courses post data.
			$course_id = MooWoodle()->Course->update_course($course);
			if($course_id) $course_ids[] = $course_id;

			// sync product if enable.
			if ($sync_now_options['sync_all_product']) {
				$product_id= MooWoodle()->Product->update_product($course,);
				if($product_id) $product_ids[] = $product_id;
			}
		}

		// remove courses that not exist in moodle.
		MooWoodle()->Course->remove_exclude_ids($course_ids);


		if ($sync_now_options['sync_all_product']) {
			// remove product that not exist in moodle.
			MooWoodle()->Product->remove_exclude_ids($product_ids);
		}
		
		do_action('moowoodle_after_sync',$courses, $sync_now_options);
		return 'success';
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
	private function update_categories($categories, $taxonomy) {
		if (empty($taxonomy) || !taxonomy_exists($taxonomy)) {
			return;
		}
		
		$category_ids = array();
		if (!empty($categories)) {
			foreach ($categories as $category) {
				// find and getthe term id for category.
				$term = MooWoodle()->Course->get_category($category['id'], $taxonomy);

				// If term is exist update it.
				if ($term) {
					$term = wp_update_term(
						$term->term_id,
						$taxonomy,
						[
							'name' 			=> $category['name'],
							'slug' 			=> "{$category['name']} {$category['id']}",
							'description' 	=> $category['description']
						]
					);
				} else { // term not exist create it.
					$term = wp_insert_term(
						$category['name'],
						$taxonomy,
						[
							'description' 	=> $category['description'],
							'slug' 			=> "{$category['name']} {$category['id']}"
						]
					);
					if (!is_wp_error($term)) add_term_meta($term['term_id'], '_category_id', $category['id'], false);
				}

				// In success on update or insert sync meta data.
				if ( ! is_wp_error($term)) {
					update_term_meta($term['term_id'], '_parent', $category['parent'], '');
					update_term_meta($term['term_id'], '_category_path', $category['path'], false);

					// Store category id to link with parent or delete term.
					$category_ids[] = $category['id'];
				} else {
					MooWoodle()->Helper->MW_log( "\n        moowoodle url:" . $term->get_error_message() . "\n");
				}
			}
		}

		// get all term for texonomy ( product_cat, course_cat )
		$terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false ));

		// if term not exist.
		if ( is_wp_error( $terms ) ) return;

		// Link with parent or delete term
		foreach ($terms as $term) {
			$category_id = get_term_meta($term->term_id, '_category_id', true);
			
			if (in_array($category_id, $category_ids)) {
				// get parent category id and continue if not exist
				$parent_category_id = get_term_meta($term->term_id, '_parent', true);
				if ( empty($parent) ) continue;
				// get parent term id and continue if not exist
				$parent_term = MooWoodle()->Course->get_category($parent_category_id, $taxonomy);
				if( empty($parent_term) ) continue;
				//   sync parent term with term
				wp_update_term($term->term_id, $taxonomy, array('parent' => $parent_term->term_id));
			} else { // delete term if category is not moodle category.
				wp_delete_term($term->term_id, $taxonomy);
			}
		}
	}
}
