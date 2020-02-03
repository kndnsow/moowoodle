<?php
global $DC_Woodle;

class DC_Woodle_Enrollment {
	public $wc_order;

	public function __construct() {
		add_action( 'woocommerce_order_status_completed', array( &$this, 'process_order' ), 10, 1 );		
		add_action( 'woocommerce_subscription_status_updated', array( &$this, 'update_course_access' ), 10, 3 );
	}
	
	/**
	 * Process the oreder when order status is complete.
	 *
	 * @access public
	 * @param int $order_id
	 * @return void
	 */
	public function process_order( $order_id ) {
		global $DC_Woodle;
		
		$this->wc_order = new WC_Order( $order_id );
		$this->process_enrollment();
	}
	
	/**
	 * Perform enrollment to moodle
	 *
	 * @access private
	 * @return void
	 */
	private function process_enrollment() {
		global $DC_Woodle;
		
		$wc_order = $this->wc_order;		
		$user_id = $wc_order->get_user_id();
		
		$moodle_user_id = $this->get_moodle_user_id( true );
		$this->enrol_moodle_user( $moodle_user_id );
	}
	
	/**
	 * Get moodle user id. If the user does not exist in moodle then creats an user in moodle.
	 *
	 * @access private
	 * @param bool $create_moodle_user (default: bool)
	 * @return int
	 */
	private function get_moodle_user_id( $create_moodle_user = false ) {
		global $DC_Woodle;
		
		$wc_order = $this->wc_order;		
		$user_id = $wc_order->get_user_id();
		add_user_meta( $user_id, '_moodle_user_order_id', $wc_order->get_id() );
		$billing_email = $wc_order->get_billing_email();
		$moodle_user_id = 0;
		
		if( $user_id ) {
			$moodle_user_id = get_user_meta( $user_id, '_moodle_user_id', true );
			if( $$moodle_user_id ){
				$moodle_user_id = intval( $moodle_user_id );
			} else {
				$moodle_user_id = intval( $user_id );
			}
			
			/*if( $moodle_user_id ) {
				$moodle_user_id = $this->search_for_moodle_user( 'id', $moodle_user_id );
			}
			*/
			if( ! $moodle_user_id ) {
				delete_user_meta( $user_id, '_moodle_user_id' );
			}
		}

		//if( ! $moodle_user_id ) {
			/*$moodle_user_id = $this->search_for_moodle_user( 'email', $billing_email );
			if( $moodle_user_id && $user_id ) {
				add_user_meta( $user_id, '_moodle_user_id', $moodle_user_id );
			}*/
			//add_user_meta( $user_id, '_moodle_user_id', $user_id );
			//if( metadata_exists( , int $object_id, string $meta_key ) )


		//}

		/*if( ! $moodle_user_id && $create_moodle_user ) {
			$moodle_user_id = $this->create_moodle_user();

			if( $moodle_user_id && $user_id ) {
				add_user_meta( $user_id, '_moodle_user_id', $moodle_user_id );
			}
		} else if( woodle_get_settings( 'update_user_info', 'dc_woodle_general' ) == 'yes' ) {
			$this->update_moodle_user( $moodle_user_id );
		}*/
		//print_r( $moodle_user_id );die;
		return $moodle_user_id;
	}
	
	/**
	 * Searches for an user in moodle by a specific field.
	 *
	 * @access private
	 * @param string $field
	 * @param string $values
	 * @return int
	 */
	private function search_for_moodle_user( $field, $values ) {
		global $DC_Woodle;
		
		$moodle_user = woodle_moodle_core_function_callback( $DC_Woodle->moodle_core_functions['get_users'], array( 'criteria' => array( array( 'key' => $field, 'value' => $values ) ) ) );
		if( ! empty( $moodle_user ) && array_key_exists( 'users', $moodle_user ) && ! empty( $moodle_user['users'] ) ) {
			return $moodle_user['users'][0]['id'];
		}
		
		return 0;
	}
	
	/**
	 * Creates an user in moodle.
	 *
	 * @access private
	 * @param int $moodle_user_id (default: int)
	 * @return int
	 */
	private function create_moodle_user( $moodle_user_id = 0 ) {
		global $DC_Woodle;
		
		$user_data = $this->get_user_data();
		$moodle_user = woodle_moodle_core_function_callback( $DC_Woodle->moodle_core_functions['create_users'], array( 'users' => array( $user_data ) ) );
		if( ! empty( $moodle_user ) && array_key_exists( 0, $moodle_user ) ) {
			$moodle_user_id = $moodle_user[0]['id'];
			// send email with credentials
			do_action( 'woodle_after_create_moodle_user', $user_data );
		}
		
		return $moodle_user_id;
	}
	
	/**
	 * Updates an user info in moodle.
	 *
	 * @access private
	 * @param int $moodle_user_id (default: int)
	 * @return int
	 */
	private function update_moodle_user( $moodle_user_id = 0 ) {
		global $DC_Woodle;
		
		$user_data = $this->get_user_data( $moodle_user_id );

		woodle_moodle_core_function_callback( $DC_Woodle->moodle_core_functions['update_users'], array( 'users' => array( $user_data ) ) );
		
		return $moodle_user_id;
	}
	
	/**
	 * Info about an user to be created/updated in moodle.
	 *
	 * @access private
	 * @param int $moodle_user_id (default: int)
	 * @return array
	 */
	private function get_user_data( $moodle_user_id = 0 ) {
		global $DC_Woodle;
		
		$wc_order = $this->wc_order;

		$user_id = $wc_order->get_user_id();
		$user = ( $user_id != 0) ? get_userdata( $user_id ) : false;
		
		$billing_email = $wc_order->get_billing_email();
		$username = $billing_email;
		if( $user ) {
			$username = $user->user_login;
		} else {
			$user = get_user_by( 'email', $billing_email );
			if( $user ) {
				$username = $user->data->user_login;
			}
		}
		$username = str_replace( ' ', '', $username );
		$username = strtolower( $username );
		
		$user_data = array();
		if( $moodle_user_id ) {
			$user_data['id'] = $moodle_user_id;
		} else {
			$user_data['email'] = ( $user && $user->user_email != $billing_email ) ? $user->user_email : $billing_email;
			$user_data['username'] = $username;
			$user_data['password'] = wp_generate_password( 8, true );
			$user_data['auth'] = 'manual';
			$a=get_locale();
			$b=strtolower($a);
			$user_data['lang'] = substr($b,0,2);
		}
		
		$user_data['firstname'] = $wc_order->get_billing_first_name();
		$user_data['lastname'] = $wc_order->get_billing_last_name();
		$user_data['city'] = $wc_order->get_billing_city();
		$user_data['country'] = $wc_order->get_billing_country();
		
		return apply_filters( 'woodle_moodle_users_data', $user_data, $wc_order );
	}
	
	/**
	 * Enrollment/suspend enrollment of an user in moodle.
	 *
	 * @access private
	 * @param int $moodle_user_id (default: int)
	 * @param int $suspend (default: int)
	 * @return void
	 */
	private function enrol_moodle_user( $moodle_user_id, $suspend = 0 ) {
		global $DC_Woodle;		
		
		if( empty( $moodle_user_id ) || ! is_int( $moodle_user_id ) ) {
			return;
		}
		
		$enrolments = $this->get_enrollment_data( $moodle_user_id, $suspend );
		
		if( empty( $enrolments ) ) {
			return;
		}
		woodle_moodle_core_function_callback( $DC_Woodle->moodle_core_functions['enrol_users'], array( 'enrolments' => $enrolments ) );
		// send confirmation email
		do_action( 'woodle_after_enrol_moodle_user', $enrolments );
	}
		
	/**
	 * Data required for enrollment.
	 *
	 * @access private
	 * @param int $moodle_user_id (default: int)
	 * @param int $suspend (default: int)
	 * @return array
	 */
	private function get_enrollment_data( $moodle_user_id, $suspend = 0 ) {
		global $DC_Woodle;
		
		$wc_order = $this->wc_order;
		$enrolments = array();
		$items = $wc_order->get_items();
		$role_id = woodle_get_settings( 'moodle_role_id', 'dc_woodle_general' );
		$role_id = ( empty( $role_id ) || ! intval( $role_id ) ) ? 5 : intval( $role_id );
		if( ! empty( $items ) ) {
			foreach( $items as  $item ) {
				$course_id = get_post_meta( $item['product_id'], '_course_id', true );
				if( ! empty( $course_id ) ) {
					$enrolment = array();
					$enrolment['courseid'] = $course_id;
					$enrolment['userid'] = $moodle_user_id;
					$enrolment['roleid'] =  $role_id;
					$enrolment['suspend'] = $suspend;
					
					$enrolments[] = $enrolment;
				}
			}
		}
		
		return apply_filters( 'woodle_moodle_enrolments_data', $enrolments );
	}
	
	/**
	 * Update user access to a course in moodle.
	 *
	 * @access public
	 * @param object $subscription
	 * @param string $new_status
	 * @param string $old_status
	 * @return void
	 */
	public function update_course_access( $subscription, $new_status, $old_status ) {
		$this->wc_order = $subscription->order;
		$suspend_for_status = apply_filters( 'woodle_suspend_course_access_for_subscription', array( 'on-hold', 'cancelled', 'expired' ) );
		$create_moodle_user = false;
		$suspend = 0;
		
		if( $old_status == 'active' && in_array( $new_status , $suspend_for_status ) ) {
			$create_moodle_user = false;
			$suspend = 1;
		} else if( $new_status == 'active' ) {
			$create_moodle_user = true;
			$suspend = 0;
		}
		
		$moodle_user_id = $this->get_moodle_user_id( $create_moodle_user );
		$this->enrol_moodle_user( $moodle_user_id, $suspend );
	}

	public function moowoodle_generate_hyperlink($cohort,$group,$course,$activity = 0) {
	// needs authentication; ensure userinfo globals are populated
	global $DC_Woodle;

	$wc_order = $this->wc_order;		
	$user_id = $wc_order->get_user_id();
	$order_user = get_user_by('id', $user_id);
	$dc_dc_woodle_general_settings_name = get_option( 'dc_dc_woodle_general_settings_name', true );

    wp_get_current_user();

    $enc = array(
		"offset" => rand(1234,5678),						// just some junk data to mix into the encryption
		"stamp" => time(),									// unix timestamp so we can check that the link isn't expired
		"firstname" => $order_user->user_firstname,		// first name
		"lastname" => $order_user->user_lastname,			// last name
		"email" => $order_user->user_email,				// email
		"username" => $order_user->user_login,			// username
		// "passwordhash" => $order_user->user_pass,			// hash of password (we don't know/care about the raw password)
		"passwordhash" => '1Admin@23',
		"idnumber" => $order_user->ID,					// int id of user in this db (for user matching on services, etc)
		"cohort" => $cohort,								// string containing cohort to enrol this user into
		"group" => $group,									// string containing group to enrol this user into
		"course" => $course,								// string containing course id, optional
		"updatable" => $dc_dc_woodle_general_settings_name['update_existing_users'],								// if user profile fields can be updated in moodle
		"activity" => $activity						// index of first [visible] activity to go to, if auto-open is enabled in moodle
	);

	// encode array as querystring
	$details = http_build_query($enc);

	return rtrim($dc_dc_woodle_general_settings_name['access_url'],"/").DC_WOODLE_MOODLE_PLUGIN_URL.$this->encrypt_string($details, $dc_dc_woodle_general_settings_name['ws_token']);
}

	/**
	 * Given a string and key, return the encrypted version (openssl is "good enough" for this type of data, and comes with modern php)
	 */
	public function encrypt_string($value, $key) {
		if ($this->moowoodle_is_base64($key)) {
			$encryption_key = base64_decode($key);
		} else {
			$encryption_key = $key;
		}
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
		$encrypted = openssl_encrypt($value, 'aes-256-cbc', $encryption_key, 0, $iv);
		$result = str_replace(array('+','/','='),array('-','_',''),base64_encode($encrypted . '::' . $iv));
		return $result;
	}

	public function moowoodle_is_base64($string) {
	    $decoded = base64_decode($string, true);
	    // Check if there is no invalid character in string
	    if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string)) return false;
	    // Decode the string in strict mode and send the response
	    if (!base64_decode($string, true)) return false;
	    // Encode and compare it to original one
	    if (base64_encode($decoded) != $string) return false;
	    return true;
	}

}
