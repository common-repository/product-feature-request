<?php
/**
 * The file that defines the core plugin class.
 *
 * @package    Product-feature-request
 * @subpackage Product-feature-request/includes
 * @link       https://themehigh.com
 * @since      1.0.0
 */
if (!defined('WPINC')) { 
	die;
}

if (!class_exists('THPFR')) :

	class THPFR {

		/**
	     * THPFR class.
	     */
		public function __construct() {
			$this->define_constants();
			$this->load_dependencies();
			$this->define_admin_hooks();
			$this->define_public_hooks();
		}

	    private function define_constants() {
			!defined('THPFR_ASSETS_URL_ADMIN') && define('THPFR_ASSETS_URL_ADMIN', THPFR_ASSETS_URL. 'admin/');
			!defined('THPFR_ASSETS_URL_PUBLIC') && define('THPFR_ASSETS_URL_PUBLIC', THPFR_ASSETS_URL. 'public/');
		}

		/**
         * Load the required dependencies for this plugin.
         *
         * Include the following files that make up the plugin:
         *
         * Create an instance of the loader which will be used to register the hooks
         * with WordPress.
         *
         * @access private
         */

		private function load_dependencies() {
			if (!function_exists('is_plugin_active')) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php');
			}

			require_once THPFR_PATH . 'includes/class-thpfr-utils.php';
			require_once THPFR_PATH . 'includes/admin/class-thpfr-admin-settings.php';
			require_once THPFR_PATH . 'includes/admin/class-thpfr-admin-settings-frw.php';
			require_once THPFR_PATH . 'includes/admin/class-thpfr-admin-settings-general.php';
			require_once THPFR_PATH . 'includes/admin/class-thpfr-admin.php';
			require_once THPFR_PATH . 'includes/public/class-thpfr-public.php';
		}

	  	/**
         * Register all of the hooks related to the admin area functionality
         * of the plugin.
         *
         * @access private
         */
		private function define_admin_hooks() {
			$plugin_admin = new THPFR_Admin();
			$this->woocommerce_is_active($plugin_admin);

			$plugin_admin_settings = new THPFR_Admin_Settings_PFR();
			add_action('add_meta_boxes',array($plugin_admin_settings,'add_meta_boxes'));
			add_action('save_post', array($plugin_admin_settings, 'save_feature_request_status'));
			add_filter('manage_posts_columns',array($plugin_admin_settings,'add_custom_column'));
			add_action('manage_posts_custom_column' ,array($plugin_admin_settings, 'add_custom_column_data'), 10, 2 );
			add_filter('post_type_link', array($plugin_admin_settings,'replace_permalink_with_product_url'), 10, 2 );
			add_action( 'before_delete_post',array($plugin_admin_settings,'before_delete_product'));
			add_action('post_updated', array($plugin_admin_settings,'validate_custom_post_type_status'),10,2);
		}

		/**
         * check WooCommerce is active for some admin hooks.
         *
         * @access private
         */
		private function woocommerce_is_active($plugin_admin) {
			if (class_exists('WooCommerce')) {
				add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles_and_scripts'));
				add_action('admin_menu', array($plugin_admin, 'admin_menu'));
				add_filter('plugin_action_links_'.THPFR_BASE_NAME, array($plugin_admin, 'plugin_action_links'));
			} 
		}

		/**
         * Register all of the hooks related to the public-facing functionality.
         * of the plugin.
         *
         * @access private
         */
		private function define_public_hooks() {
			if (!is_admin() || (defined( 'DOING_AJAX') && DOING_AJAX)) {
				$plugin_public = new THPFR_Public();
				
				add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles_and_scripts'));
				add_action( 'wp_ajax_feature_request_action',array($plugin_public,'feature_request_action'));
				add_action( 'wp_ajax_nopriv_feature_request_action',array($plugin_public,'feature_request_action'));
				add_filter( 'woocommerce_product_tabs',array($plugin_public,'custom_product_tab' ));
				add_action( 'wp_ajax_feature_voting_action',array($plugin_public,'feature_voting_action'));
				add_action( 'wp_ajax_nopriv_feature_voting_action',array($plugin_public,'feature_voting_action'));
				add_filter( 'woocommerce_product_tabs',  array($plugin_public,'set_prioryty_wooco_custom_tab' ));
			}
		}
	}
endif;
