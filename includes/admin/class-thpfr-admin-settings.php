<?php
/**
 * The admin settings page specific functionality of the plugin.
 *
 * @package    Product-feature-request
 * @subpackage Product-feature-request/includes/admin
 * @link       https://themehigh.com
 * @since      1.0.0
 */
if (!defined('WPINC')) { 
	die; 
}

if (!class_exists('THPFR_Admin_Settings')) :

	/**
	 * Admin settings class.
	 */
	abstract class THPFR_Admin_Settings {
		public $settings_fields = array();
		public $cell_props_L = array();
		public $cell_props_R = array();
		public $cell_props_CB = array();
		public $cell_props_CBS = array();
		public $cell_props_CBL = array();
		public $cell_props_CPL = array();
		public $cell_props_CPR = array();

		/**
         * Constructor.
         *
         */
		public function __construct() {
			$this->init_constants();
		}

		/**
         * constants.
         *
         */
		public function init_constants() {

			$this->cell_props_L = array( 
				'label_cell_props' => 'width="20%"', 
				'input_cell_props' => 'width="27%"', 
				'input_width' => '250px',  
			);
			
			$this->cell_props_R = array( 
				'label_cell_props' => 'width="20%"', 
				'input_cell_props' => 'width="27%"', 
				'input_width' => '250px', 
			);
			
			$this->cell_props_CB = array( 
				'label_props' => 'style="margin-right: 40px;"', 
			);
			$this->cell_props_CBS = array( 
				'label_props' => 'style="margin-right: 15px;"', 
			);
			$this->cell_props_CBL = array( 
				'label_props' => 'style="margin-right: 52px;"', 
			);
			
			$this->cell_props_CPL = array(
				'label_cell_props' => 'width="20%"', 
				'input_cell_props' => 'width="27%"', 
			);
			$this->cell_props_CPR = array(
				'label_cell_props' => 'width="15%"', 
				'input_cell_props' => 'width="32%"', 
			);
		}

		/**
         * function for render form field element.
         *
         * @param array $field field data.
         * @param array $settings saved data.
         * @param array $atts array attribute.
         * @param array $status status information.
         * @param array $render_tooltip tooltip information.
         *
         * @return array
         */
		public function render_form_field_element($field, $settings=array(), $atts=array(), $status=array(), $render_tooltip=true) {
		 	if ($field && is_array($field)) {
	    		$F_type = isset($field['type']) ? $field['type'] : 'text';
				$name = isset($field['name']) ? $field['name'] : '';
				$label = isset($field['label']) ? $field['label'] : '';
				$label_for = isset($field['label_for']) ? esc_attr($field['label_for']) : '';
	    	}

	    	if (is_array($settings) && isset($settings[$name])) {
	    		if ($F_type === 'checkbox' || $F_type === 'switch') {
					$field['checked'] = $settings[$name];
					
				}else {
					$field['value'] = $settings[$name];
				}
	    	}

			$args = shortcode_atts( array(
				'label_cell_props' => '',
				'input_cell_props' => '',
				'label_cell_th' => false,
				'input_width' => '',
				'cell_spasing' => '',
				'input_height' => '',
				'Font_style' => '',
				'font_size' => '',
				'input_name_prefix' => '',
				'input_name_suffix' => '',
			), $atts );
			$args = array_map( 'esc_attr', $args);

			$I_class = isset($field['class']) ? $field['class'] : '';
			$L_class = isset($field['lclass']) ? esc_html__($field['lclass']) : '';
			$F_name = $args['input_name_prefix'].$name.$args['input_name_suffix'];
			$F_label = isset($field['label']) ? esc_html__($field['label'], 'product-feature-request') : '';
			$F_value = isset($field['value']) ? $field['value'] : '';
			$colspan = isset($field['colspan']) ? esc_attr($field['colspan']) : '';

			$input_width = $args['input_width'] ? 'width:'.$args['input_width'].';' : '';
	        $input_height = $args['input_height'] ? 'height:'.$args['input_height'].';' : '';
	        $cell_spasing = $args['cell_spasing'] ? 'width:'.$args['cell_spasing'].';' : '';
	        $Font_style = $args['Font_style'] ? 'font-style:'.$args['Font_style'].';' : '';
	        $font_size = $args['font_size'] ? 'font-size:'.$args['font_size'].';' : '';

	        $field_props = 'name="'. esc_attr($F_name) .'" value="'. esc_attr($F_value) .'" style="'. esc_attr($input_width) .'"';
	        $field_props .= isset($field['placeholder']) && !empty($field['placeholder']) ? ' placeholder="'.esc_attr($field['placeholder']).'"' : '';
	        $field_props .= !empty($I_class) ? ' class="'.esc_attr($I_class).'"': '';
			$required_html = isset($field['required']) && $field['required'] ? '<abbr class="required" title="required">*</abbr>' : '';
			$field_html = '';
			$prev_style = '';

	        if ($F_type === 'sub_title') {
	        	$prev_style = isset($F_value) ? $font_size.' '.$Font_style : '';
	        	$field_html = '<td class="'. $I_class .'" colspan="'. $colspan .'" style = "'.esc_attr($prev_style).'">';
	        	$field_html .= $F_value;
		        $field_html .= '</td>';

	        }elseif ($F_type === 'select') {
				$L_class .= '';
				$field_html = '<td><label class="'.$L_class.'">'.$F_label.'</label></td>';
				$field_html .= '<td class="">';
				$field_html .= '<select '.$field_props.' >';

				foreach ($field['options'] as $ovalue => $otext) {
					$otext = esc_html__($otext, 'product-feature-request');
					$selected = $ovalue === $F_value ? 'selected' : '';
					$field_html .= '<option value="'.esc_attr($ovalue).'" '.esc_html($selected).'>'. esc_html($otext) .'</option>';
				}
				$field_html .= '</select></td>';

			}elseif ($F_type === 'switch') {
				$field_props .= isset($field['checked']) && $field['checked'] ? 'checked' : '';
				$field_html .= '<td style="'.$cell_spasing.'"><label data-labelfor="'.$label_for.'" class="">'.$F_label.'</label></td>';
				$field_html .= '<td><label class="thpfr-switch">';
				$field_html .= '<input id="'.$label_for.'" type="checkbox" '. $field_props .' />'; 
				$field_html .= '<span class="thpfr-slider"></span>';
				$field_html .= '</label></td>';		
			}
		    echo $field_html;
		}

		/**
         * display blanck space.
         *
         */
		public  function render_blank_space($colspan) {
			$blank_td = '<td colspan="'. esc_attr($colspan) .'"></td>';
			echo $blank_td;
		}
	}//end class
endif;
