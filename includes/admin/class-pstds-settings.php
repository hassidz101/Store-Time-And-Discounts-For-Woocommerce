<?php
/**
 * PSTDS_SETTINGS loader Class File.
 *
 * @package pstds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'PSTDS_SETTINGS' ) ) {
	/**
	 * Class for Admin Settings
	 */
	class PSTDS_SETTINGS {



		/**
		 * Class Constructor
		 */
		public function __construct() {
			$this->id = 'pstds_settings';
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings' ), 50 );
			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
			
			//register ajax actions
			add_action('wp_ajax_nopriv_pstds_delete_licence_ajax',array( $this, 'pstds_delete_licence_ajax'));
			add_action('wp_ajax_pstds_delete_licence_ajax',array( $this, 'pstds_delete_licence_ajax'));

		}//end __construct()


		/**
		 * Runs at every includes of our plugin.
		 *
		 * @param array $settings_tab Setting tab.
		 * @return null
		 */
		public function add_settings( $settings_tab ) {
			$settings_tab[ $this->id ] = __( 'Store Time & Discounts', 'pstds' );
			return $settings_tab;

		}//end add_settings()

		/**
		 * Get products from DB.
		 */
		public function pstds_get_wc_products() {
			$products     = array();
			$args         = array(
				'status'  => 'publish',
				'orderby' => 'name',
				'order'   => 'ASC',
				'limit'   => -1,
			);
			$all_products = wc_get_products( $args );
			foreach ( $all_products as $key => $product ) {
				if ( $product->get_type() === 'simple' || $product->get_type() === 'variable' ) {
					$products[ $product->get_id() ] = $product->get_title();
				}
			}

			return $products;

		}//end pstds_get_wc_products()

		/**
		 * Delete Licence.
		 */
		public function pstds_delete_licence_ajax() {
			delete_option('pstds_settings_pro_licensed_key');
			delete_option('pstds_settings_store_license_status');
			pstds_delete_license_key();

		}//end pstds_delete_licence_ajax()

		/**
		 * Get Categories from DB.
		 */
		public function pstds_get_wc_categories() {

			$list               = array();
			$args               = array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'parent'     => 0,
			);
			$product_categories = get_terms( $args );

			foreach ( $product_categories as $cat ) {

				$list[ $cat->term_id ] = $cat->name;
			}
			return $list;

		}//end pstds_get_wc_categories()

		/**
		 * Get Timezone List.
		 */
		public function get_timezone_options() {
			$html = array();

			foreach ( timezone_identifiers_list() as $value ) {
				$html[ $value ] = $value;
			}

			return $html;

		}//end get_timezone_options()


		/**
		 * Runs at pro_form parameter to yes only.
		 *
		 * @param array $current_section Section Array.
		 * @return null
		 */
		public function get_pro_settings( $current_section ) {
			$settings = array(
				'section_1'     => array(
					'name' => __( 'Pro Activation', 'pstds' ),
					'type' => 'title',
					'id'   => $this->id . '_section_1_start',
				),
				array(
					'name' => __( 'Please Enter Licensed Key', 'pstds' ),
					'type' => 'text',
					'id'   => $this->id . '_pro_licensed_key',
					'desc' => __( 'Note : Enter valid licensed key', 'pstds' ),
					'css'  => 'width:50%!important;',

				),

				'section_1_end' => array(
					'type' => 'sectionend',
					'id'   => $this->id . '_section_1_end',
				),

			);

			return apply_filters( 'wc_' . $this->id, $settings );

		}//end get_pro_settings()

		/**
		 * Runs at every includes of our plugin.
		 *
		 * @param array $current_section Section Array.
		 * @return null
		 */
		public function get_settings( $current_section ) {
			$settings = array(
				'section_1'     => array(
					'name' => __( 'Store Time Settings', 'pstds' ),
					'type' => 'title',
					'id'   => $this->id . '_section_1_start',
				),

				array(
					'name' => __( 'Enable Store Timing', 'pstds' ),
					'type' => 'checkbox',
					'id'   => $this->id . '_store_time_status',
					'desc' => __( 'Check here to enable store time feature', 'pstds' ),
				),
				array(
					'name'    => __( 'Timezone', 'pstds' ),
					'type'    => 'select',
					'class'   => 'chosen_select',
					'id'      => $this->id . '_timezone',
					'options' => $this->get_timezone_options(),
				),
				array(
					'name'    => __( 'Type', 'pstds' ),
					'type'    => 'select',
					'class'   => 'chosen_select',
					'id'      => $this->id . '_store_type',
					'options' => array(
						'choose'   => __( 'Choose Type', 'pstds' ),
						'category' => __( 'Specific Category', 'pstds' ),
						'product'  => __( 'Specific Product', 'pstds' ),
						'site'     => __( 'Entire Site', 'pstds' ),
					),
				),
				array(
					'name'    => __( 'Select Products', 'pstds' ),
					'type'    => 'multiselect',
					'class'   => 'chosen_select',
					'id'      => $this->id . '_store_product_time_ids',
					'options' => $this->pstds_get_wc_products(),
				),

				array(
					'name'    => __( 'Select Categories', 'pstds' ),
					'type'    => 'multiselect',
					'class'   => 'chosen_select',
					'id'      => $this->id . '_store_category_time_ids',
					'options' => $this->pstds_get_wc_categories(),
				),
				array(
					'name' => __( 'Store Open Time', 'pstds' ),
					'type' => 'time',
					'id'   => $this->id . '_store_open_time',
				),
				array(
					'name' => __( 'Store Closed Time', 'pstds' ),
					'type' => 'time',
					'id'   => $this->id . '_store_closed_time',
				),
				array(
					'name' => __( 'Store Closed Notice (Entire Site)', 'pstds' ),
					'type' => 'textarea',
					'id'   => $this->id . '_store_closed_notice',
					'desc' => __( 'Note : Please enter notice when store is closed ', 'pstds' ),
					'css'  => 'width:50%!important;',

				),
				array(
					'name' => __( 'Cart Empty Notice (Specific Product)', 'pstds' ),
					'type' => 'textarea',
					'id'   => $this->id . '_product_closed_notice',
					'desc' => __( 'Note : Please enter notice when store is closed for specific products', 'pstds' ),
					'css'  => 'width:50%!important;',

				),
				array(
					'name' => __( 'Cart Empty Notice (Specific Category)', 'pstds' ),
					'type' => 'textarea',
					'id'   => $this->id . '_category_closed_notice',
					'desc' => __( 'Note : Please enter notice when store is closed for specific category ', 'pstds' ),
					'css'  => 'width:50%!important;',

				),
				array(
					'name' => __( 'Cart Empty Notice (Closed Store)', 'pstds' ),
					'type' => 'textarea',
					'id'   => $this->id . '_cart_empty_notice',
					'desc' => __( 'Note : Please enter notice when user is about to checkout the filled cart during closed time.', 'pstds' ),
					'css'  => 'width:50%!important;',

				),

				'section_1_end' => array(
					'type' => 'sectionend',
					'id'   => $this->id . '_section_1_end',
				),

				'section_2'     => array(
					'name' => __( 'Store Discount Settings', 'pstds' ),
					'type' => 'title',
					'id'   => $this->id . '_section_2_start',
				),
				array(
					'name' => __( 'Enable Discount Feature', 'pstds' ),
					'type' => 'checkbox',
					'id'   => $this->id . '_discount_status',
					'desc' => __( 'Check here to enable discount feature', 'pstds' ),
				),
				array(
					'name' => __( 'Enable Discount on already sale products?', 'pstds' ),
					'type' => 'checkbox',
					'id'   => $this->id . '_store_sale_discount_status',
					'desc' => __( 'Check here to apply sale on products which are on sale already', 'pstds' ),
				),
				array(
					'name' => __( 'Already sale products notice (Product Page)', 'pstds' ),
					'type' => 'textarea',
					'id'   => $this->id . '_sale_product_notice',
					'desc' => __( 'Note : Please enter notice when product is already on sale ', 'pstds' ),
					'css'  => 'width:50%!important;',

				),
				array(
					'name'    => __( 'Discount type', 'pstds' ),
					'type'    => 'select',
					'class'   => 'chosen_select',
					'id'      => $this->id . '_discount_type',
					'options' => array(
						'choose'   => __( 'Choose Type From Dropdown', 'pstds' ),
						'product'  => __( 'Product Level', 'pstds' ),
						'site'     => __( 'Entire Site', 'pstds' ),
						'category' => __( 'Category Level', 'pstds' ),
					),
				),
				array(
					'name'    => __( 'Select Products', 'pstds' ),
					'type'    => 'multiselect',
					'class'   => 'chosen_select',
					'id'      => $this->id . '_product_discount_ids',
					'options' => $this->pstds_get_wc_products(),
				),
				array(
					'name'    => __( 'Select Categories', 'pstds' ),
					'type'    => 'multiselect',
					'class'   => 'chosen_select',
					'id'      => $this->id . '_category_discount_ids',
					'options' => $this->pstds_get_wc_categories(),
				),
				array(
					'name'    => __( 'Discount Rule ', 'pstds' ),
					'type'    => 'select',
					'class'   => 'chosen_select',
					'id'      => $this->id . '_discount_rule',
					'options' => array(
						'choose'     => __( 'Choose Rule', 'pstds' ),
						'percentage' => __( 'Percentage', 'pstds' ),
						'fixed'      => __( 'Fixed Amount', 'pstds' ),

					),
				),
				array(
					'name' => __( 'Site Discount', 'pstds' ),
					'type' => 'number',
					'id'   => $this->id . '_site_discount',
				),
				array(
					'name' => __( 'Enable Store Wide Discount Banner', 'pstds' ),
					'type' => 'checkbox',
					'id'   => $this->id . '_store_discount_banner_status',
					'desc' => __( 'Check here to enable discount banner', 'pstds' ),
				),
				array(
					'name' => __( 'Store Wide Banner Notice', 'pstds' ),
					'type' => 'textarea',
					'id'   => $this->id . '_store_discount_banner',
					'desc' => __( 'Note : Please enter notice when the store wide discount is enabled.', 'pstds' ),
					'css'  => 'width:50%!important;',
				),
				array(
					'name' => __( 'Product Discount', 'pstds' ),
					'type' => 'number',
					'id'   => $this->id . '_product_discount',
				),
				array(
					'name' => __( 'Category Discount', 'pstds' ),
					'type' => 'number',
					'id'   => $this->id . '_category_discount',
				),
				
				'section_2_end' => array(
					'type' => 'sectionend',
					'id'   => $this->id . '_section_2_end',
				),
			);

return apply_filters( 'wc_' . $this->id, $settings );

		}//end get_settings()


		/**
		 * Output the settings
		 */
		public function output() {
			global $current_section;
			if(isset($_GET['pro_form'])){
				$settings = $this->get_pro_settings( $current_section );
			}else{
				$settings = $this->get_settings( $current_section );
			}
			WC_Admin_Settings::output_fields( $settings );

		}//end output()


		/**
		 * Save settings
		 */
		public function save() {
			global $current_section;

			if(isset($_GET['pro_form'])){
				$settings = $this->get_pro_settings( $current_section );
				WC_Admin_Settings::save_fields( $settings );
				pstds_validate_license_key();
				
			}else{
				$settings = $this->get_settings( $current_section );
				WC_Admin_Settings::save_fields( $settings );
			}
			
			

		}//end save()

	}//end class

	new PSTDS_SETTINGS();
}//end if
