<?php
/**
 * The admin settings for custom post type page specific functionality of the plugin.
 *
 * @package    Product-feature-request
 * @subpackage Product-feature-request/includes/admin
 * @link       https://themehigh.com
 * @since      1.0.0
 */
if (!defined('WPINC')) { 
    die;
}

if (!class_exists('THPFR_Admin_Settings_PFR')) :

    /**
     *Admin settings for custom post type class.
     */
    class THPFR_Admin_Settings_PFR extends THPFR_Admin_Settings {

        /**
         * Constructor.
         */
        public function __construct() {
            parent::__construct();
            $this->woocommerce_is_active();
        }

        /**
         * Add metabox for each feature request.
         */
        public function add_meta_boxes() {
            $this->add_meta_box_for_product_name();
            $this->add_metabox_for_feature_status();
        }

        /**
         * Display feature request when wooCommerce is active.
         */
        public function woocommerce_is_active() {
            if (class_exists('WooCommerce')) {
                $this->create_feature_requests_custom_post_type();
            }
        }
        
        /**
         * Create custom post-type "feature-request".
         */
        public function create_feature_requests_custom_post_type() {
            $frw_labels =   array(
                'name'                => ('All Feature Request'),
                'singular_name'       => ('All Feature Request'),
                'all_items'           => esc_html__('All Feature Request','product-feature-request'),
                'add_new_item'        => esc_html__('Add New FEATURE REQUEST','product-feature-request'),
                'edit_item'           => esc_html__('Edit Feature Request','product-feature-request'),
                'new_item'            => esc_html__('New Feature Request','aproduct-feature-request'),
                'view_item'           => esc_html__('Vew Feature Request','product-feature-request'),
                'search_items'        => esc_html__('Search Feature Request','product-feature-request'),
                'not_found'           => esc_html__('No Feature Request Found','product-feature-request'),
                'not_found_in_trash'  => esc_html__('No Feature Request Found in Trash ', 'product-feature-request'),
                'parent_item_colon'   => 'parent item',
                'menu_name'           => esc_html__('Feature Request', 'product-feature-request'),
             );
            $frw_args = array(
                'labels'              => $frw_labels,
                'public'              => true,
                'publicly_queryable'  => true,
                'has_archive'         => true,
                'rewrite'             => true,
                'show_in_menu'        => true,
                'query_var'           => true,
                'capabilities'        => array('create_posts' => false),
                'map_meta_cap'        => true, 
                'has_archive'         => false,
                'hierarchical'        => false,
                'menu_icon'           => 'dashicons-megaphone',
                'supports'            => array('title','editor'),
                'menu_position'       => 4,
                'exclude_from_search' => false,
            );
            register_post_type( 'feature-requests',  $frw_args);
        }

        /**
         * Metabox for display product name in each feature request.
         */
        public function add_meta_box_for_product_name() {
            add_meta_box('thpfr_product_feacture','Product name',array($this,'render_meta_box_product_name'),'feature-requests','side');
        }

        public function render_meta_box_product_name($post) {
            $product_id = THPFR_Utils::get_product_id($post->ID);
            if ($product_id) {
                $product = wc_get_product( $product_id);
                $name = $product->get_name();
                ?>
                <table>  
                    <tr>
                        <td><b><?php echo esc_html($name); ?></b></td>
                    </tr>
                </table>
                <?php
            }
        }

        /**
         * Metabox for display and change feature request status in each feature request.
         */
        public function add_metabox_for_feature_status() {
            add_meta_box('thpfr_product_feacture_status','Feature Request  Status',array($this,'render_meta_box_product_status'),'feature-requests','side');
        }

        public function render_meta_box_product_status($post) {
            $field_set = THPFR_Utils::get_feature_request_status_field();
            $field_value = $field_set['feature_request_status']['options'];
            $feature_status = THPFR_Utils::get_feature_request_status($post->ID);
            ?>
            <select name="thpfr_feature_status" style="background-color: #eeee; color:black; width:12em;">
                <?php
                foreach ($field_value as $key => $field) {
                    $selected = $key == $feature_status ? 'selected' : '';
                    ?>
                    <option  <?php echo esc_html($selected); ?> value="<?php echo esc_attr($key); ?>"><?php echo esc_html($field); ?></option>
                    <?php
                }
                ?>
            </select>
            <?php
        }

        /**
         * Save feature request status.
         *
         * @param string $post_id save status to each post using post id.
         */
        public function save_feature_request_status($post_id) {
            $post_type = get_post_type();
            if ($post_type!='feature-requests') { 
                return;
            }

            if ($post_id != '' && !empty($_POST)) {
                $feature_status = isset($_POST['thpfr_feature_status']) ? sanitize_text_field($_POST['thpfr_feature_status']) :'' ;
                $update = THPFR_Utils::update_feature_request_status($post_id,$feature_status); 
            }
        }

        /**
         * Add custom column to feature request listing page.
         */
        public function add_custom_column($columns) {
            $posttype  = get_post_type();
            $next_column_name = 'date';

            foreach ($columns as $key => $value) {
                if ($key == $next_column_name && $posttype == 'feature-requests') {
                    $post_columns['Status'] = 'Status';
                    $post_columns['Votes'] = 'Votes';
                    $post_columns['Product name'] = 'Product name';
                }
                $post_columns[$key] = $value;
            }
            return $post_columns;
        }

        /**
         * Add custom column datas to feature request listing page.
         *
         * @param array $post_columns get custom column name.
         * @param string $postid get vote count, product name and status of each feature request.
         */
        public function add_custom_column_data($post_columns,$postid) {
            $feature_status = THPFR_Utils::get_feature_request_status($postid,'OPEN');
            $vote_count = THPFR_Utils::get_vote_datas($postid,'vote_count',0);
            $product_id = THPFR_Utils::get_product_id($postid);
            $product = wc_get_product($product_id );
            $post_type = get_post_type();
            
            if ($post_type == 'feature-requests' && $product) {
                $product_name = $product->get_name();
                if ($post_columns === 'Status') {
                    echo '<b>'.$feature_status.'</b>';
                } elseif($post_columns === 'Votes') {
                    echo '<b>'.$vote_count.'</b>';
                } elseif($post_columns === 'Product name') {
                    echo '<b>'.$product_name.'</b>';
                }
            }
        }

        /**
         * Replace permalink with product url.
         *
         * @param $permalink get permalink of each custom post.
         * @param $post get post type from the post.
         *
         * @return post url.
         */
        public function replace_permalink_with_product_url( $permalink, $post) {
            $product_id = THPFR_Utils::get_product_id($post->ID);

            if ($post->post_type == 'feature-requests' && $product_id) {
                $product_url = get_permalink($product_id);
                $url = $product_url.'#tab-thpfr_custom_tab'; 
            }
            return isset($url) ? $url : $permalink; 
        } 
        
        public function before_delete_product($product_id) {
            $post_type = get_post_type($product_id);
            
            if ($post_type == 'product') :
                $user_post_id_arr = THPFR_Utils::get_feature_request_ids($product_id);
                foreach ($user_post_id_arr as  $user_post_id) {
                    wp_delete_post($user_post_id);
                }
            endif;
        }

        /**
         * Compare feature request status with parent product status.
         *
         * @param string $id post id.
         */
        public function validate_custom_post_type_status($id) {
            $post_type = get_post_type($id);

            if ($post_type == 'product') {
                $user_post_id_arr = THPFR_Utils::get_feature_request_ids($id);
                $product = wc_get_product( $id );
                $product_status = $product->get_status();
                
                foreach ($user_post_id_arr as  $user_post_id) {
                    if ($product_status == 'trash') {
                        wp_trash_post($user_post_id);
                    } else {
                        wp_untrash_post($user_post_id);
                    }
                }
            }
        }
    }//end class
endif;    


