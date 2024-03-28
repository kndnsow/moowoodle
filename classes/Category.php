<?php
 namespace MooWoodle;
 class Category {
    
	/**
	 * Returns term by moodle category id
	 *
	 * @param int $category_id
	 * @param string $taxonomy (default: null)
	 * @param string $meta_key (default: null)
	 * @return object
	 */
	public static function get_category($category_id, $taxonomy = '') {
		if (empty($category_id) || !is_numeric($category_id) || empty($taxonomy) || !taxonomy_exists($taxonomy) ) {
			return null;
		}

		// Get the trermes basesd on moodle category id.
		$terms = get_terms(
			[
				'taxonomy' 		=> $taxonomy,
				'hide_empty' 	=> false,
				'meta_query' 	=> [
					'key' 	=> '_category_id',
					'value' => $category_id
				]
			]
		);
		
		// Check no category found.
		if ( is_wp_error( $terms ) ) {
			return null;
		}

		return $terms[0];
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
	public static function update_categories($categories, $taxonomy) {
		if (empty($taxonomy) || !taxonomy_exists($taxonomy)) {
			return;
		}
		
		$category_ids = array();
		if (!empty($categories)) {
			foreach ($categories as $category) {
				// find and getthe term id for category.
				$term = self::get_category($category['id'], $taxonomy);

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
				$parent_term =self::get_category($parent_category_id, $taxonomy);
				if( empty($parent_term) ) continue;
				//   sync parent term with term
				wp_update_term($term->term_id, $taxonomy, array('parent' => $parent_term->term_id));
			} else { // delete term if category is not moodle category.
				wp_delete_term($term->term_id, $taxonomy);
			}
		}
	}
 }
