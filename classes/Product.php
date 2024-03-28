<?php

namespace MooWoodle;

class Product {
    /**
	 * Update moodle product data in WordPress WooCommerce.
	 * if not exist create new product.
	 * 
	 * @access public
	 * @param array $course (moodle course data)
	 * @return int course id
	 */
	public static function update_Product($course) {
		if (empty($courses) || $course['format'] == 'site') return 0;

		// get the product id linked with moodle.
        $products = \wc_get_products(
            [
                'meta_query' => [
                    [
                        'key'=> 'moodle_course_id',
                        'value' => $course['id'],
                    ]
                ] 
            ]
        );

        // create a new product if not exist.
        if(empty($products)) {
            $product = new \WC_Product_Simple();
        } else { // take the 1st one if exist.
            $product = $products[0];
        }

        // get category term
        $term = MooWoodle()->Category->get_category($course['id'], 'product_cat');

        // Set product properties
        $product->set_name($course['fullname']);
        $product->set_slug($course['shortname']);
        $product->set_description($course['summary']);
        $product->set_status('publish');
        $product->set_sold_individually(true);
        $product->set_category_ids([$term]);
        $product->set_virtual(true);
        $product->set_catalog_visibility($course['visible'] ? 'visible' : 'hidden');
        // $product->set_sku(); // need to set if need



		// get the course id linked with moodle.
        $linked_course_id = MooWoodle()->Course->get_courses(
			[
				'meta_key' 		=> 'moodle_course_id',
				'meta_value' 	=> $course['id'],
				'meta_compare' 	=> '=',
				'fields'	 	=> 'ids'
			]
		)[0];


        // Set product meta data.
        $product->update_meta_data('_course_startdate', $course['startdate']);
        $product->update_meta_data('_course_enddate', $course['enddate']);
        $product->update_meta_data('moodle_course_id', (int) $course['id']);
        $product->update_meta_data('linked_course_id', $linked_course_id);

		return $product->get_id();
	}
    
	/**
	 * Delete all the product which id is not prasent in $exclude_ids array.
	 * 
	 * @access public
	 * @param array $exclude_ids (product ids)
	 * @return void
	 */
	public static function remove_exclude_ids($exclude_ids) {

        // get all product except $exclude_ids array
		$product_ids = \wc_get_products(
            [
                'exclude' => $exclude_ids,
                'status' => 'publish',
                'return' => 'ids',
            ]
        );

		// delete product.
		foreach ($product_ids as $product_id) {
			\wh_deleteProduct($product_id, false);
		}
	}
}
