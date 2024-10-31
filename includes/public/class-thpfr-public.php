<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package    Product-feature-request
 * @subpackage Product-feature-request/includes/public
 * @link       https://themehigh.com
 * @since      1.0.0
 */

if (!defined('WPINC')) {	
	die;
}

if (!class_exists('THPFR_Public')) :

	/**
     * Public class.
     */
	class THPFR_Public {

		/**
         * Function for enqueue style and script.
         *
         * @return void
         */
	    public function enqueue_styles_and_scripts() {
			wp_register_style('thpfr-public-style', THPFR_ASSETS_URL_PUBLIC.'css/thpfr-public.css');
	    	wp_enqueue_style('thpfr-public-style');
	   		wp_enqueue_style( 'font-awesome-free', '//use.fontawesome.com/releases/v5.2.0/css/all.css' );
	        wp_enqueue_style('FontAwesome',THPFR_ASSETS_URL_PUBLIC.'css/font-awesome.min.css');
	    	wp_register_script('thpfr-public-script', THPFR_ASSETS_URL_PUBLIC.'js/thpfr-public.js', array('jquery'), THPFR_VERSION, true);
			wp_enqueue_script('thpfr-public-script');
			wp_localize_script('thpfr-public-script', 'get_request', 	
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			));
		}

		/**
         * Function for custom product tab for display feature request section form.
         *
         */
		public function custom_product_tab($tabs) {
			global $product;
			$product_id = $product->get_id();
			$user_post_id_arr = THPFR_Utils::get_feature_request_ids($product_id,'publish');
		    $count_request = self::rate_feature_request($user_post_id_arr);
			$tabs['thpfr_custom_tab'] = array('title' => __( 'Feature Request ('.$count_request.')', 'woocommerce' ),'callback' => array($this,'custom_tab_content'),'priority' => 60,);
			return $tabs;
		}

		/**
         * Function for custom tab content.
         *
         */
		public function custom_tab_content($slug,$tab) {
			global $product,$current_user;
			$theme_wrapper = '';
			$user_log_id_arr =array();
			$get_current_theme = wp_get_theme();
			$current_theme = $get_current_theme['Name'];
		    $product_id = $product->get_id();
		    $feature_request_display_settings = THPFR_Utils::get_general_settings_datas('feature_request_display_settings');
		    $updated_date = THPFR_Utils::get_general_settings_datas('feature_request_display_date');
		    $user_id = $current_user->ID;
		    $thwpfr_custom_tab_wrapper = $user_id <1 ? 'thpfr-custom-tab-wrapper' : '';
		    $user_name = $current_user->user_login;
			$user_post_id_arr = THPFR_Utils::get_feature_request_ids($product_id,'publish');
			$feature_request_count = self::rate_feature_request($user_post_id_arr);
			$add_cls_for_user_reg_form_btn = (0 == $feature_request_count && $user_id >0) ? 'thpfr-hide-form' : '';
			
			if ($current_theme == 'Avada') {
				$theme_wrapper = 'thpfr-avada-wrapper';
			}elseif ($current_theme == 'Flatsome') {
				$theme_wrapper = 'thpfr-flatsm-wrapper';
			}elseif ($current_theme == 'Divi') {
				$theme_wrapper = 'thpfr-divi-wrapper';
			}

	        $arg = array(
	        	'updated_date' => $updated_date,
	         	'fr_display_settings' => $feature_request_display_settings,
	         	'user_post_id_arr' => $user_post_id_arr,
	         	'current_theme' => $current_theme,    	
	        );

		    ?>
			<div class="thwpfr-check-class <?php echo $theme_wrapper.' '.$thwpfr_custom_tab_wrapper;?>">
			    <h2>Feature Request</h2>
			    <p><button class="thpfr-submit-fr button primary is-outline <?php echo $add_cls_for_user_reg_form_btn; ?>" onclick="ShowFeatureRequest(this)">Post Request</button></p>
			    <?php
			    echo $this->feature_request($product_id,$user_id,$user_post_id_arr);
			    echo $this->feature_request_display_html_settings($arg);
		    	?>
		    </div> 
		    <?php
		}

		/**
         * Function for rate feature request on product page feature request custam tab.
         *
         * @param array $user_post_id_arr all feature request ides.
         */
		public static function rate_feature_request($user_post_id_arr) {
			$request_arr = [];
			if (isset($user_post_id_arr)) {
				foreach ($user_post_id_arr as $key =>$user_request_single_id) {
					$post_status = get_post_status($user_request_single_id);
					$post_type = get_post_type($user_request_single_id);
		            
					if ($post_status == 'publish' and $post_type == 'feature-requests') {
		        		array_push($request_arr,$user_request_single_id);
			    	}
				}
			}
			return isset($request_arr) ? count($request_arr) : 0;
		} 

		/**
         * Function for feature request form .
         *
         * @param array $product_id all feature request product ids.
         * @param string $user_id logined user ids.
         * @param array $user_post_id_arr all feature request ides.
         */
		public function feature_request($product_id,$user_id,$user_post_id_arr) {
	        $field_set = THPFR_Utils::get_feature_request_user_field();
	        $feature_request_count = self::rate_feature_request($user_post_id_arr);
	        $add_cls_for_user_reg_form = 0 < $feature_request_count ? 'thpfr-hide-form' : '';
	        ob_start();
	        if ($user_id>0) {
				?>
				<form method="post" class="thpfr-form <?php echo $add_cls_for_user_reg_form;?>"> 
		        	<?php
		        	foreach ($field_set as $index => $field) {
		        		$L_class = isset($field['lclass']) ? $field['lclass'] : '';
		        		$I_class = isset($field['iclass']) ? $field['iclass'] : '';
		        		$label = isset($field['label']) ? $field['label'] : '';
		        		$placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';
		        		$value = isset($field['value']) ? $field['value'] : '';
		        		$prev_style = 'style=width:100%;';
		        		$path = plugin_dir_url(__FILE__);
		        	    $path = $path.'/gif/Bars-1s-200px.gif';

		        		if ($field['type'] === 'uf_text') {
		        			?>
		        			<p>
		    			    	<label for="<?php  echo esc_attr($index); ?>"><?php  echo esc_html($label); ?></label>
		    					<input <?php echo esc_attr($prev_style);?> data-type="text" class="<?php  echo esc_attr($I_class); ?>" type="text" name="<?php  echo esc_attr($index); ?>" placeholder="<?php  echo esc_attr($placeholder); ?>"/>
		    					<p class="thpfr-text"></p>
		    			    </p>
		        			<?php
		        		}elseif ($field['type'] === 'uf_textarea') {
		        			?>
		        			<p>
		        				<textarea <?php echo esc_attr($prev_style);?> data-type="textarea" class="<?php  echo esc_attr($I_class); ?>" name="<?php  echo esc_attr($index); ?>" placeholder="<?php  echo esc_attr($placeholder); ?>"></textarea>
		        			</p>
		        			<?php
		        		}elseif ($field['type'] === 'uf_hidden') {
		        			$value = $index == 'product_id' ? $product_id : $value;
		        			?>
		        			<input type="hidden" name="<?php  echo esc_attr($index); ?>" value="<?php  echo esc_attr($value); ?>"/>
		        			<?php
		        		}
				    }
				   
				    ?>
				    <p class="thpfr-auto-lodder-wrapper">
						<input style ="margin-top: 5px;"type="submit" class="th_Submit" value="Submit" onclick="thpfrsubmit(this)">
						<img class="thpfr-auto-lodder" src="<?php echo esc_attr($path);?>" width="40px" height="40px">
						<div class="thpfr-remove-html"></div>
						<div class="thpfr-hide-notification">Feature Request posted successfully.</div>
				    </p>
				</form>  
		    	<?php
			}else {
				?>
				<div class="thpfr-form thpfr-hide-form"><?php $this->user_login_and_regestration_settings($product_id);?></div> 
				<?php
			}
		    echo ob_get_clean();
		} 

		/**
         * Function for user login and regestration settings.
         *
         * @param string $product_id all feature request product ids.
         */
		public function user_login_and_regestration_settings($product_id) {
			$enable_disable_rege_form = get_option( 'woocommerce_enable_myaccount_registration');
	        $create_new_accnt = ($enable_disable_rege_form == 'yes') ? '/<a class="thpfr-login-panel" onclick="ShowFeatureRegisterForm(this)" href="#">Create an account</a>' : ''; 
	        $list_link = '<a class="thpfr-login-panel login" onclick="UserLoginSettings(this)" href="#">Login</a>'.$create_new_accnt;
			$action = get_permalink($product_id).'#tab-thpfr_custom_tab';
		
			?>
			<p><?php echo $list_link;?></p>
			<div class="thpfr-login-form thpfr-hide-form">
				<?php	
				woocommerce_login_form(
					array(
						'message'  => '', 
						'redirect' => $action,
					    'hidden'   => false,
					)
				);
				?>
			</div>
			<div class="thpfr-registration-form thpfr-hide-form">

				<?php
				if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
					<div class="u-column2 col-2">
						<h2><?php esc_html_e( 'Register', 'woocommerce' ); ?></h2>
						<form action="<?php echo $action; ?>" method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
							<?php do_action( 'woocommerce_register_form_start' ); ?>
							<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
								<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" />
							</p>
							<?php endif; ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
								<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" />
							</p>
							<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
								<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
							</p>
							<?php else : ?>
								<p><?php esc_html_e( 'A password will be sent to your email address.', 'woocommerce' ); ?></p>
							<?php endif; ?>
							<?php do_action( 'woocommerce_register_form' ); ?>
							<p class="woocommerce-FormRow form-row">
								<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
								<button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Register', 'woocommerce' ); ?></button>
								<a href="<?php echo $action;?>"></a>
							</p>
							<?php do_action( 'woocommerce_register_form_end' ); 
							
							?>
						</form>
					</div>
				<?php 
				endif; 
				?>
			</div>
			<?php
		}

		/**
         * Function for save feature request datas.
         *
         */
		public function feature_request_action() {
			if (! isset( $_POST['_WP_THPFR_nonce'] ) || ! wp_verify_nonce( $_POST['_WP_THPFR_nonce'], 'thpfr_nonce')) {
	            $responce = array('verify_nonce' => '<span class="thpfr-error-submt">Sorry, your nonce did not verify.</span>');
	            wp_send_json($responce);
	        	exit;
	        }else {
	        	global $current_user;
	 		    $user_name = $current_user->user_login;
	 			$user_id = $current_user->ID;
		        $responce = '';
				$empty = array();
	            $user_field_datas = THPFR_Utils::sanitize_user_field_datas();
			    $feature_title = $user_field_datas['th_user_name'];
				$feature_request = $user_field_datas['th_user_comment'];
				$product_id = $user_field_datas['product_id'];
			    $updated_date = THPFR_Utils::get_general_settings_datas('feature_request_display_date');
			    $post_status  = (THPFR_Utils::get_general_settings_datas('feature_request_display_settings') == true) ? 'publish' : 'draft';
			   
				if (empty($feature_title)) {
					$empty['name'] = "<span class='thpfr-error-submt'>You must specify your request.</span>";		
				}

				if (!empty($empty)) {
		            wp_send_json($empty);
			        die($empty);
				}

				$user_request = array(
			        'post_title'   => $feature_title,
			        'post_content' => $feature_request,
			        'post_type'    => 'feature-requests',
			        'post_status'  => $post_status,
			    );

				$post_id = wp_insert_post( $user_request, $wp_error = false );
				$posted_date = $updated_date == true ? human_time_diff(get_the_time('U',$post_id), current_time('timestamp')).' ago' : '';
				$posted_date = !empty($posted_date) ?  ' '.$posted_date : '';
				$user_name_and_date = 'Posted by '.$user_name.' '.$posted_date;
				$feature_status = 'OPEN';
				$update1 = THPFR_Utils::update_user_id($post_id,$user_id);
				$update2 = THPFR_Utils::update_product_id($post_id,$product_id);

		        if ($update2 and $post_id) { 
		            $responce = array(
		            	'user_name_and_date' => $user_name_and_date,
		            	'user_post_id'   => $post_id,
		            	'feature_title'  => make_clickable(stripcslashes($feature_title)),
		            	'user_request'   => wpautop(make_clickable(stripcslashes($feature_request))),
		            	'feature_status' => $feature_status,
		            );
					wp_send_json($responce);
	            }
		 	}
		}

		/**
         * Function for feature request up vote down vote settings.
         *
         * @param string $request_ID feature request ides for get vote data.
         * @param string $user_vote_status feature request vote status from each user.
         */
		public function voting_for_feature_request($request_ID,$user_vote_status) {
			global $current_user;
			$user_id = $current_user->ID;
			$user_data = THPFR_Utils::get_vote_datas($request_ID);
			$vote_count = isset($user_data['vote_count']) ? $user_data['vote_count'] : 0;
	        $path = plugin_dir_url(__FILE__);
	        $path = $path.'/gif/Ring-2s-98px.gif';
	        $vote_type = (int)$vote_count == 1 ? 'Vote' : 'Votes';

	        if ($user_vote_status == 1) {
	        	$add_class_for_downvote = $user_id >0 ? 'thpfr-user-vote-btn-active' : '';
	        	$add_class_for_upvote = $user_id >0 ? 'thpfr-user-vote-btn-inactive' : '';
	        }else {
	        	$add_class_for_downvote = $user_id >0 ? 'thpfr-user-vote-btn-inactive' : '';
	        	$add_class_for_upvote = $user_id >0 ? 'thpfr-user-vote-btn-active' : '';
	        }      

			?>
	        <div id="upvote_<?php echo esc_attr($request_ID); ?>" class="thpfr-voter-pids thpfr-user-vote">
	        	<span title=" Upvote this question" class="fas fa-caret-up thpfr-icon thpfr-upvt <?php echo esc_attr($add_class_for_upvote);?>" onclick="VoteforFeatureRequest(this)" data-post_id="<?php echo esc_attr($request_ID);?>" data-voter_pid="<?php echo esc_attr($request_ID);?>" data-_wp_thpfrv_nonce="<?php echo wp_create_nonce('thpfrv_nonce');?>" data-action="feature_voting_action"></span>
	        	<center><img src="<?php echo esc_attr($path);?>" width="16" class="thpfr-load-voting thpfr-hide-load-voting"></center>
	        	<span class="thpfr-vote-counter"><?php echo esc_html($vote_count).' '.esc_html($vote_type); ?></span>
	        	<span title=" Downvote this question"  class="fas fa-caret-down thpfr-icon thpfr-downvt <?php echo esc_attr($add_class_for_downvote);?>" onclick="VoteforFeatureRequest(this)" data-post_id="<?php echo esc_attr($request_ID);?>" data-voter_pid="<?php echo esc_attr($request_ID);?>" data-_wp_thpfrv_nonce="<?php echo wp_create_nonce('thpfrv_nonce');?>" data-action="feature_voting_action"></span>
	        </div>
			<?php
		}

		/**
         * Function for feature request vote settings.
         *
         */
		public function feature_voting_action() {
			if (! isset( $_REQUEST['_wp_thpfrv_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_wp_thpfrv_nonce'], 'thpfrv_nonce')) {
	        	$responce = array( 'verify_nonce' => '<span class="thpfr-error-submt">Sorry, your nonce did not verify.</span>');
	            wp_send_json($responce);
	    		exit;
	        }else {
				global $current_user;
				$user_id = $current_user->ID;
		    	$voter_post_id = isset($_REQUEST['voter_pid']) ? $_REQUEST['voter_pid'] : false;
		    	$voter_post_id = is_numeric($voter_post_id) ? absint($voter_post_id) : '';
		    	$post_id = isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : false;
		    	$post_id = is_numeric($post_id) ? absint($post_id) : '';
		        $post_id = !empty($post_id) ?  $post_id : $voter_post_id;

			    if ($user_id >0) {
			        $get_vote_datas = THPFR_Utils::get_vote_datas($post_id);
			        $user_data = THPFR_Utils::get_vote_datas($post_id,'user_'.$user_id,'');
			        $user_eids = isset($user_data['uid']) ? $user_data['uid'] : '';
			        $vote_status = $user_eids == $user_id ? 'true' : 'false';
			        $value = isset($user_data['toggle_value']) ? $user_data['toggle_value'] : 1;
			 		$value = $vote_status == 'false' ? 1 : -$value;
			        $vote_count = 0;
			  		$vote_count = isset($get_vote_datas['vote_count']) ? $get_vote_datas['vote_count'] : '';
			 		$vote_count = (int)$vote_count + (int)$value;
			 		$user_arr = array('uid' => $user_id,'toggle_value' => $value);
			 		$get_vote_datas['vote_count'] = $vote_count;
			 		$get_vote_datas['user_'.$user_id] = $user_arr;
			 		$update = THPFR_Utils::update_vote_datas($post_id,$get_vote_datas);
					$vote_array = array('vote_count' => $vote_count,'post_id' => $post_id, 'toggle_value' => $value);
					wp_send_json($vote_array);
				}
			}	
		}

		/**
         * Function for display feature request settings.
         *
         */
		public function feature_request_display_html_settings($arg) {
			global $current_user;
			$user_id = $current_user->ID;
			$add_new_cls = $user_id <1 ? 'thpfr-login-settings' : '';
			$params = shortcode_atts( array(
	         	'fr_display_settings' => '',
	         	'user_post_id_arr' => '',
	         	'updated_date' => '',
	         	'current_theme' => '',
	    	), $arg );
	        
	    	$user_post_id_arr = $params['user_post_id_arr'];
	    	$fr_display_settings = $params['fr_display_settings'];
	    	$updated_date = $params['updated_date'];
	        $current_theme = $params['current_theme'];

	        ?>
	        <div id="thpfr_appen_datas"></div>
	        <?php

			if (!empty($user_post_id_arr)) {
				rsort($user_post_id_arr);
				foreach ($user_post_id_arr as  $user_post_id) {
					if ($user_post_id) {	
						$post_date = $updated_date == true ? human_time_diff(get_the_time('U',$user_post_id), current_time('timestamp')).' ago' : '';
						$user_eids = THPFR_Utils::get_vote_datas($user_post_id,'user_'.$user_id,'');
	                    $saved_user_id =  THPFR_Utils::get_user_id($user_post_id);
	                    $user_datas = get_user_by('id', $saved_user_id);
						$user_name = !empty($user_datas) ? $user_datas->data->display_name : '';
						$user_vote_status = isset($user_eids['toggle_value']) ? $user_eids['toggle_value'] : -1;
						$feature_status = THPFR_Utils::get_feature_request_status($user_post_id,'OPEN');
						$post = get_post($user_post_id);
						
		            	if (!empty($post) and $post->post_status == 'publish' and $post->post_type == 'feature-requests') {
		            	    $post_title = $post->post_title;
						    $post_content = $post->post_content;
							$feature_request_display_html = $this->feature_request_display_html($user_name,$post_content,$post_title,$post_date,$user_post_id,$feature_status,$user_vote_status,$current_theme,$add_new_cls);
						}
				    }
				}
			}

			?>
			<div id="thpfr_html_form_datas" style="display: none;">
				<?php
				$feature_request_display_html = $fr_display_settings == true ? $this->feature_request_display_html('','','','','','','','','') : '';
				echo $feature_request_display_html;
				?>
			</div>	
			<?php
		}

		/**
         * Function for display feature request HTML.
         *
         * @param string $user_name feature requested user name.
         * @param string $user_request feature request from user.
         * @param string $feature_title title for each feature request.
         * @param string $request_date feature requested date.
         * @param string $request_ID get vote datas from given id.
         * @param string $feature_status feature request status.
         * @param string $add_new_cls add class for feature request display panel. 
         * @param string $user_vote_status feature request vote status.
         */
		public function feature_request_display_html($user_name,$user_request,$feature_title,$request_date,$request_ID,$feature_status,$user_vote_status,$current_theme,$add_new_cls) {
			ob_start();
			$request_date = !empty($request_date) ?  ' '.$request_date : '';
			$status_bg_color = '#b82e8a';
			$prev_style1 = 'display: flex;';
			$prev_style2 = $user_request == '' ? 'margin: 0px 0px 10px 0px; display: block;' : '';

			if ($feature_status == 'DECLINED') {
				$status_bg_color = '#FF1A22';
			}elseif ($feature_status == 'IN PROGRESS') {
				$status_bg_color = '#015CE5';
			}elseif ($feature_status == 'COMPLETED') {
				$status_bg_color = '#00BB00';
			}elseif ($feature_status == 'NEED RESEARCH') {
				$status_bg_color = '#4CADE9';
			}elseif ($feature_status == 'UNDER REVIEW') {
				$status_bg_color = '#F5A741';
			}elseif ($feature_status == 'PLANNED') {
				$status_bg_color = '#7463CB';
			}elseif ($feature_status == 'OPEN') {
				$status_bg_color = '#b82e8a';
			}

	    	?>
		    <section class="thpfr-feature-display-panel <?php echo esc_attr($add_new_cls); ?>">
		    	<div class="thpfr-title-wrapper">
		    		<div class="thpfr-voting-table">
		    			<?php echo $this->voting_for_feature_request($request_ID,$user_vote_status);?>
		    		</div>
		    	</div>	
		    	<div class="thpfr-content-wrapper">
		    		<h4 class="thpfr-feature-title"><b class="thpfr-cnt-text-to-link"><?php echo make_clickable(wp_kses_post($feature_title)); ?></b></h4>
		    		<div Style = "<?php echo esc_attr($prev_style1); ?>">
		    			<span class="thpfr-feature-request-status"  style="background-color: <?php echo esc_attr($status_bg_color).'!important';?>"><?php echo esc_html($feature_status); ?></span>
		    		</div>
		    		<span style ="<?php echo esc_attr($prev_style2); ?>" ><small class="thpfr-request-date-and-name"> Posted by <?php echo esc_html($user_name).' '.$request_date;?></small></span>
		    		<div class="thpfr-feature-request thpfr-cnt-text-to-link"><?php echo wpautop(make_clickable(wp_kses_post($user_request))); ?></div>
		    	</div>	 		
		    </section>
			<?php
			echo ob_get_clean();
		}

		/**
         * Set custom prioryty for feature request tab on product page. 
         *
         */
		public function set_prioryty_wooco_custom_tab($tabs) {
			$add_priority = apply_filters('thpfr_custom_product_tab_pos_priority',10);
			$tabs['thpfr_custom_tab']['priority'] = $add_priority;
			return $tabs;
		}

	}//end class
endif;
