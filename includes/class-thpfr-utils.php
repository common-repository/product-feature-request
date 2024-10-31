<?php
/**
 * The common utility functionalities.
 *
 * @package    Product-feature-request
 * @subpackage Product-feature-request/includes
 * @link       https://themehigh.com
 * @since      1.0.0
 */
if (!defined('ABSPATH')) {
    die;
}

if (!class_exists('THPFR_Utils')) :

	 /**
	 * Common utils class.
	 */	
	class THPFR_Utils {
		const OPTION_KEY_SETTINGS_GENERAL = '_thpfr_settings';
		const FEATURE_REQUEST_PRODUCT_ID = '_product_id';
		const FEATURE_REQUEST_USER_ID = '_user_id';
		const FEATURE_REQUEST_VOTE_DATA = '_vote_data';
		const FEATURE_REQUEST_STATUS = '_feature_request_status';

		/**
		 * feature request user form field.
		 *
		 */	
		public static function get_feature_request_user_field() {
	        return array(
	            'th_user_name' => array(
					'type'        => 'uf_text',
					'iclass'      => 'thpfr-name-fields thpfr-form-fields',
					'label'       => 'Name your feature request:', 
					'placeholder' => ''
			    ), 
				'th_user_comment' => array(
					'type'        => 'uf_textarea',
			       	'id'          => '',
				    'iclass'      => 'thpfr-form-fields thpfr-request-fields',
				    'label'       => '',
				    'placeholder' => 'Describe your feature request...',
				), 
				'action' => array(
					'type'        => 'uf_hidden',
			       	'value'       => 'feature_request_action',
				), 
				'product_id' => array(
					'type'        => 'uf_hidden',
			       	'value'       => '',
				), 
				'_WP_THPFR_nonce' => array(
					'type'        => 'uf_hidden',
			       	'value'       => wp_create_nonce('thpfr_nonce'),
				),
			);
		}

		/**
		 * feature request statuse select field in each feature request.
		 *
		 */	
		public static function get_feature_request_status_field() {
			return array(
				'feature_request_status' =>array(
		    	'name'    => 'feature_request_status',
				'type'    => 'multiselect',
				'options' => array(
					'OPEN'              => 'Open',
			  		'NEED RESEARCH' => 'Need Research',
			   		'UNDER REVIEW'  => 'Under Review',
			   		'PLANNED'       => 'Planned',
			   		'IN PROGRESS'   => 'In Progress',
			   		'COMPLETED'     => 'Completed',
			   		'DECLINED'      => 'Declined',
				),
				'class'   => '',
				'value'   => '',
				)
			);
		}

		/**
		 * general settings field. 
		 *
		 */	
		public static  function get_general_settings_field() {
			return array ( 
	        	'feature_request_display_settings' => array(
	        		'name'   => 'feature_request_display_settings',
					'label'  => 'Display feature request before reviewing.',
					'type'   => 'switch',
					'class'  => '',
					'value'  => 'yes',
	                'checked'=> 0,
	                'label_for' => 'thpfr-display-fr',
	        	), 
	        	'display_style' => array(
					'type'    	=> 'sub_title',
					'value'   	=> 'Display  Settings',
					'class'   	=> 'thpfr-form-section-title',
					'colspan' 	=> '6',
					
				), 
				'feature_request_display_date' => array(
	    		'name'   => 'feature_request_display_date',
				'label'  => 'Display feature request posted date.',
				'type'   => 'switch',
				'class'  => '',
				'value'  => 'yes',
	            'checked'=> 0,
	            'label_for' => 'thpfr-display-upd',
	        	),
			);
		}	

		/**
		 * filter and prepare general settings data.
		 *
		 */	
		public static function get_general_settings_datas($index = false,$load_defaults = true) {
			$settings_general = get_option(self::OPTION_KEY_SETTINGS_GENERAL);

			if (empty($settings_general) && $load_defaults) {
				$settings_general = self::prepare_default_settings_general();
			}

			if ($index) {
	        	$settings_general = isset($settings_general[$index]) ? $settings_general[$index] : '';
			}
			return empty($settings_general) ? false : $settings_general;
		}

		public static function prepare_default_settings_general() {
			$settings = array();
			$settings_fields = self::get_general_settings_field();

			foreach ($settings_fields as $key => $field) {
				$type = isset($field['type']) ? $field['type'] : 'text';

				if ($type != 'separator' && $type != 'subtitle') {
					if ($type === 'checkbox' || $type === 'switch') {
						$settings[$key] = isset($field['checked']) && $field['checked'] ? 'yes' : '';
					}else {
						$settings[$key] = isset($field['value']) ? $field['value'] : false;
					}
				}
			}
			return $settings;
		}

		
        /**
         * Sanitization of post fields.
         *
         * @param string $type.
         * @param array $value.
         *
         * @return array.
         */
		public static function get_posted_value($posted, $key, $type) {
			$value = is_array($posted[$key]) ? implode(',', $posted[$key]) : $posted[$key];
			$value = stripslashes($value);

			switch ($type) {
				case 'uf_text':
					$value = trim(wp_filter_post_kses($value));// use trim remove whitespace from the user input field
					break;
				case 'uf_textarea':
					$value = wp_filter_post_kses($value);
					break;
				case 'switch':
					$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
					break;
				default:
					$value = sanitize_text_field($value); 
					break;
			}
			return $value;
		}

		/**
		 * function for sanitize frontend field value.
		 *
		 */	
		public static function sanitize_user_field_datas() {
			$settings_props = self::get_feature_request_user_field();

			foreach ($settings_props as $key => $field ) {
				$type = isset($field['type']) ? $field['type'] : 'uf_text';

				if ($type != 'separator' && $type != 'subtitle' && isset($_POST[$key])) {
					$user_request[$key] = self::get_posted_value($_POST, $key, $type);
				}
				$user_request[$key] = isset($user_request[$key]) ? $user_request[$key] : false;
			}
			return $user_request;
		}

		public static function save_general_settings_datas($settings) {
			$result = update_option(self::OPTION_KEY_SETTINGS_GENERAL, $settings);
			return $result;
		}

		public static function delete_general_settings_datas() {
			$result = delete_option(self::OPTION_KEY_SETTINGS_GENERAL);
			return $result;
		}

		/**
		 *List all feature request ides. 
		 *
		 */	
		public static function get_feature_request_ids($product_id,$status = false) {
			$user_post_id_arr = [];
			$key = self::FEATURE_REQUEST_PRODUCT_ID;
			$status = $status == false ? array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ) : 'publish';
			$args = array('posts_per_page' => -1,'post_type' => 'feature-requests','post_status' => $status,'meta_key' => $key,'meta_query' => array(array('key' => $key,'value' => $product_id,'compare' => '=='),),);
	        $loop = new WP_Query($args);
			
			while ( $loop->have_posts()) : $loop->the_post();
				$post_id = get_the_ID();
				array_push($user_post_id_arr, $post_id);
			endwhile;
		    wp_reset_query();
		    return isset($user_post_id_arr) ? $user_post_id_arr : false;
		}

	    /**
		 * post meta settings start here.
		 *
		 */	
		public static function update_vote_datas($post_id,$post_array) {
			$update = update_post_meta($post_id, self::FEATURE_REQUEST_VOTE_DATA,$post_array);
			return empty($update) ? false : $update;
		}

		public static function get_vote_datas($post_id,$index = '',$default = false) {
			$data = get_post_meta($post_id, self::FEATURE_REQUEST_VOTE_DATA,true);
	        $data = !empty($index) ? (isset($data[$index]) ? $data[$index] : '') : $data;
			return empty($data) ? $default : $data;
		}

		public static function update_product_id($post_id,$post_array) {
			$update = update_post_meta($post_id, self::FEATURE_REQUEST_PRODUCT_ID,$post_array);
			return empty($update) ? false : $update;
		}

		public static function get_product_id($post_id) {
			$data = get_post_meta($post_id, self::FEATURE_REQUEST_PRODUCT_ID,true);
			return empty($data) ? false : $data;
		}

		public static function update_user_id($post_id,$post_array) {
			$update = update_post_meta($post_id, self::FEATURE_REQUEST_USER_ID,$post_array);
			return empty($update) ? false : $update;
		}

		public static function get_user_id($post_id) {
			$data = get_post_meta($post_id, self::FEATURE_REQUEST_USER_ID,true);
			return empty($data) ? false : $data;
		}

		public static function update_feature_request_status($post_id,$post_array) {
			$update = update_post_meta($post_id, self::FEATURE_REQUEST_STATUS,$post_array);
			return empty($update) ? false : $update;
		}

		public static function get_feature_request_status($post_id,$default_status =false) {
			$data = get_post_meta($post_id, self::FEATURE_REQUEST_STATUS,true);
			return empty($data) ? $default_status : $data;
		}
		/**
		 * post meta settings end here.
		 *
		 */	

		public static function write_log( $log=false) {
	        if ( true === WP_DEBUG ) {
	          	if ( is_array( $log ) || is_object( $log )) {
	            	error_log( print_r( $log, true ) );
	          	}else {
	            	error_log( $log );
	          	}
	        }
	    }
	}

endif;