<?php
/**
 * plugin activator.
 *
 */

if(!defined('WPINC')) { 
	die;
}

if(!class_exists('THPFR_Activator')):

	/**
	 * activator class.
	 */
	class THPFR_Activator {
	    public static function activate() {
	        register_post_type( 'feature-request', ['public' => 'true'] );
	        flush_rewrite_rules();
	    }
	}
endif;


