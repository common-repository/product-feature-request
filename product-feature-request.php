<?php
/**
 * Plugin Name: Product Feature Request 
 * Description: Easily take in ideas and suggestions from your customers to include new compelling features or modifications for the WooCommerce products.
 * Version:     1.2.3
 * Author:      ThemeHigh
 * Author URI:  https://www.themehigh.com
 * Text Domain: product-feature-request
 * WC requires at least: 6
 * WC tested up to: 9.2
 * Domain Path: /languages
 */

if (!defined('WPINC')) {
 	die;
}

if (!class_exists('THPFR_Manager')) {
	class THPFR_Manager {	
		const TEXT_DOMAIN = 'product-feature-request'; 

		public function __construct() {
			add_action('init', array($this, 'init'));
			register_activation_hook( __FILE__, array($this, 'activate') );
			add_action( 'before_woocommerce_init', array($this, 'before_woocommerce_init') ) ;
		}		

		public function init() {
			define('THPFR_VERSION', '1.2.3');
			!defined('THPFR_BASE_NAME') && define('THPFR_BASE_NAME', plugin_basename( __FILE__ ));
			!defined('THPFR_PATH') && define('THPFR_PATH', plugin_dir_path( __FILE__ ));
			!defined('THPFR_URL') && define('THPFR_URL', plugins_url( '/', __FILE__ ));
			!defined('THPFR_ASSETS_URL') && define('THPFR_ASSETS_URL', THPFR_URL .'assets/');

			$this->load_plugin_textdomain();
			require_once( THPFR_PATH . 'includes/class-thpfr.php' );
			$frw = new THPFR;
		}

		/**
	     * The code that runs during plugin activation.
	     */
		public function activate() {
			require_once( plugin_dir_path( __FILE__ ).'includes/class-thpfr-activator.php');
			THPFR_Activator::activate();
		}

		/**
	     * The code that load plugin test domain.
	     */
		public function load_plugin_textdomain() {
			$locale = apply_filters('plugin_locale', get_locale(), self::TEXT_DOMAIN);
		
			load_textdomain(self::TEXT_DOMAIN, WP_LANG_DIR.'/product-feature-request/'.self::TEXT_DOMAIN.'-'.$locale.'.mo');
			load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(THPFR_BASE_NAME) . '/languages/');
		}

		/**
		 * HPOS compatibility
		 **/
		public function before_woocommerce_init() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}		
	}
	new THPFR_Manager();
}
