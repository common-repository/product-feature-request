<?php
/**
 * The admin general settings page functionality of the plugin.
 *
 * @package    Product-feature-request
 * @subpackage Product-feature-request/includes/admin
 * @link       https://themehigh.com
 * @since      1.0.0
 */
if (!defined('WPINC')) {	
	die; 
}

if (!class_exists('THPFR_Admin_Settings_General')) :

	/**
     * Genereal setting class.
     */
	class THPFR_Admin_Settings_General extends THPFR_Admin_Settings {
		protected static $_instance = null;

		/**
         * Constructor.
         */
		public function __construct() {
			parent::__construct();
		}
		
		/**
         * Instance.
         *
         * @return $_instance
         */
		public static function instance() {
			if (is_null(self::$_instance)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
         * Render calls.
         *
         * @return void
         */ 
		public function render_page() {
			$this->output_tabs();
			$this->output_content();
	    }

	    /**
         * out puts.
         *
         * @return void
         */
	    private function output_tabs() {
	    	?>
	    	<h1 class="nav-tab-wrapper woo-nav-tab-wrapper">
	    		<b class="nav-tab nav-tab-active" style="font-size: 18px;">General</b>
	    	</h1>
	    	<?php
	    }

	    /**
         * out put  content function.
         *
         * @return void
         */
	    private function output_content() {
		    if (isset($_POST["thpfr_reset_settings"])) {
			    $this->reset_settings();
		    }

		    if(isset($_POST["thpfr_save_settings"])) {
			    $this->save_settings();
		    }

	        $settings_fields = THPFR_Utils::get_general_settings_field();       
			$settings_general = THPFR_Utils::get_general_settings_datas('',false);
	        $cell_spasing = array('cell_spasing' => '30%', );
	        
	    	?>
	    	<div style="padding-left: 30px;">               
			    <form id="general_settings_form" method="post" action="">
			    <?php $this->wp_verify_nonce();?>	
	                <table class="form-table thpfr-form-table" style="width: 100%;">
	                	<tbody>
				 		   	<tr>
				 		   		<?php
				 		   		$this->render_form_field_element($settings_fields['display_style'],$settings_general);
								?>
				 		   	</tr>
				 		   	<tr>
				 		   		<?php
				 		   		$this->render_form_field_element($settings_fields['feature_request_display_settings'],$settings_general,$cell_spasing);
								?>
				 		   	</tr>
				 		   	 <tr>
				 		   		<?php
				 		   		$this->render_form_field_element($settings_fields['feature_request_display_date'],$settings_general);
								?>
				 		   	</tr>
	                	</tbody>
	                </table>
	            	<p class="submit">
						<input type="submit" name="thpfr_save_settings" class="button-primary" value="Save changes">
	                	<input type="submit" name="thpfr_reset_settings" class="button" value="Reset to default" onclick="return confirm('Are you sure you want to reset to default settings? all your changes will be deleted.');">
	        		</p>
	            </form>
	        </div>        
	    	<?php
	    }

	    /**
         * set hidden field for nonce checking.
         *
         */
	    private function wp_verify_nonce() {
	    	?>
	    	<input type="hidden" name="_WP_THPFRGS_nonce" value="<?php echo wp_create_nonce('thpfrgs_nonce'); ?>">
	        <input type="hidden" name="_WP_THPFRGR_nonce" value="<?php echo wp_create_nonce('thpfrgr_nonce'); ?>">
	    	<?php
	    }
	    
	    /**
         * Save settings.
         *
         * @return void
         */
	    public function save_settings() {
			if (!isset( $_POST['_WP_THPFRGS_nonce']) || ! wp_verify_nonce($_POST['_WP_THPFRGS_nonce'], 'thpfrgs_nonce')) {
	    		echo $responce = '<div class="thpfr-update-message failed"><p><b>Sorry, your nonce did not verify.</b></p></div>';
				exit;
	        }else {
				$settings = array();
				$settings_props = THPFR_Utils::get_general_settings_field();

				foreach ($settings_props as $key => $field ) {
					$type = isset($field['type']) ? $field['type'] : 'text';
					if($type != 'separator' && $type != 'subtitle' && isset($_POST[$key])) {
						$settings[$key] = THPFR_Utils::get_posted_value($_POST, $key, $type);
					}
				}
				$result = THPFR_Utils::save_general_settings_datas($settings);

			    if ($result == 'true') {
				 	echo '<div class="thpfr-update-message updated"><p><b>Your changes were saved.</b></p></div>'; 
				}else {
					echo '<div class="thpfr-update-message failed"><p><b>Your changes were not saved due to an error(or you made none!)</b></p></div>';
				}
			}			
		}

		/**
         * Reset settings.
         *
         * @return void
         */
		public function reset_settings() {
			if (! isset( $_POST['_WP_THPFRGR_nonce'] ) || ! wp_verify_nonce( $_POST['_WP_THPFRGR_nonce'], 'thpfrgr_nonce')) {
				echo $responce = '<div class="thpfr-update-message failed"><p><b>Sorry, your nonce did not verify.</b></p></div>';
				exit;
	        }else {
				THPFR_Utils::delete_general_settings_datas();
				$settings_new = array();
				$settings_props = THPFR_Utils::get_general_settings_field();

				foreach ($settings_props as $key => $field ) {
					if (isset($_POST[$key])) {
						$settings_new[$key] = $field['value'];
					}
				}

				$settings = THPFR_Utils::prepare_default_settings_general();
				$result = THPFR_Utils::save_general_settings_datas($settings);

				if ($result=='true') {
				    echo '<div class="thpfr-update-message updated"><p><b>Settings successfully reset.</b></p></div> ';
				}else {
		            echo '<div class="thpfr-update-message"><p><b>Error restoring, Please try again.</b></p></div>';
				}
			}
		}
	}//end class
endif;