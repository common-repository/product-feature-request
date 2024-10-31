<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Product-feature-request
 * @subpackage Product-feature-request/includes/admin
 * @link       https://themehigh.com
 * @since      1.0.0
 */
if (!defined('WPINC')) { 
	die;
}

if (!class_exists('THPFR_Admin')) :

	/**
     * Admin class.
     */
	class THPFR_Admin {

  		/**
         * Enqueue style and script.
         *
         * @param string $hook_suffix The screen id.
         *
         * @return void
         */
	   	public function enqueue_styles_and_scripts($hook_suffix) {
			if ( !in_array($hook_suffix, array('post.php', 'post-new.php','feature-requests_page_thpfr-settings'))) {
	    		return;
	    	}

	    	$screen = get_current_screen();
	    	if ( is_object( $screen ) && !in_array( $screen->post_type, array('feature-requests'))) {
	        	return;
			}

			$deps = array('jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'wp-color-picker');
	        wp_enqueue_media();
	        wp_enqueue_style ('thpfr-admin-style', THPFR_ASSETS_URL_ADMIN. 'css/thpfr-admin.css');
	        wp_enqueue_script('thpfr-admin-script', THPFR_ASSETS_URL_ADMIN. 'js/thpfr-admin.js', $deps, THPFR_VERSION, true);
	    }

	    /**
         * Function for set capability.
         *
         *
         * @return string
         */
		public function add_capability() {
			$allowed = array('manage_woocommerce', 'manage_options');
			$capability = apply_filters('thpfr_required_capability', 'manage_options');

			if (!in_array($capability, $allowed)) {
				$capability = 'manage_woocommerce';
			}
			return $capability;
		}

		 /**
         * Function for set admin menu.
         *
         *
         * @return void
         */
	    public function admin_menu() {
	    	$capability = $this->add_capability();
	    	$page_title = esc_html__('Settings', 'product-feature-request');
	    	$menu_title = esc_html__('Settings', 'product-feature-request');
			$this->screen_id = add_submenu_page('edit.php?post_type=feature-requests', $page_title, $menu_title, $capability, 'thpfr-settings', array($this, 'output_settings'));
		}

		/**
         * function for setting link.
         *
         * @param string $links The plugin action link.
         *
         * @return void
         */
		public function plugin_action_links($links) {
			$settings_link = '<a href="'.admin_url('edit.php?post_type=feature-requests&page=thpfr-settings').'">'. esc_html__('Settings', 'product-feature-request') .'</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		/**
         * function for output settings.
         *
         * @return void
         */
	    public function output_settings() {
			if (!current_user_can('manage_options')) {
				wp_die( __('You do not have sufficient permissions to access this page.'));
			}       
			$settings = THPFR_Admin_Settings_General::instance();
			$settings->render_page();
		}
	}//end class
endif;	
	    