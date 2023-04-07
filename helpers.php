<?php
/**
 * All helper functions.
 * PHP version 7
 *
 * @category Store-Timing-Discount-wp
 * @package  Pstds
 * @author   Author <info@domain.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://localhost/
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pstds_create_cron() {
	if( is_admin() ){
		if ( false === as_has_scheduled_action( 'pstds_validate_license_key_action' )  ) {
			as_schedule_recurring_action( strtotime("today"), 300, 'pstds_validate_license_key_action', array(), '', true );
		}
	}
}
add_action( 'init', 'pstds_create_cron' );


function pstds_getProductCategoriesList() {

	$list               = array( -1 => __( 'All', 'swpvfw' ) );
	$product_categories = get_terms(
		$args           = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'parent'     => 0,
		)
	);

	foreach ( $product_categories as $cat ) {
		$thumbnail_id          = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
		$image                 = wp_get_attachment_url( $thumbnail_id );
		$link                  = get_term_link( $cat->term_id, 'product_cat' );
		$list[ $cat->term_id ] = $cat->name;
	}
	return $list;
}

function pstds_getProductList() {
	$products = get_posts(
		array(
			'post_type'   => 'product',
			'numberposts' => -1,
			'post_status' => 'publish',
		)
	);
	$list     = array();
	foreach ( $products as $product ) {
		$list[ $product->ID ] = $product->post_title;
	}
	return $list;
}
add_action('pstds_validate_license_key_action','pstds_validate_license_key');
function pstds_validate_license_key(){

	$store_license_status = get_option( 'pstds_settings_pro_licensed_key' );
	
	$url  = 'https://proaddons.com/api/plugin-active';
	$body = array(
		'plugin_name'  => 'Store Time And Discount For Woocommerce',
		'site_url'     => site_url(),
		'user_license' => $store_license_status,
	);

	$args = array(
		'method'      => 'POST',
		'timeout'     => 45,
		'sslverify'   => false,
		'body'        => $body,
	);

	$request = wp_remote_post( $url, $args );
	$response = wp_remote_retrieve_body( $request );
	
	$WC_Logger = new WC_Logger();
	$WC_Logger->add( 'storetiming-license-api-validate', print_r($response,true) );

	$response = json_decode($response);

	if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
		update_option('pstds_settings_store_license_status', "false");
		add_action( 'admin_notices', 'pstds_not_valid_license' );
	}
	else{
		if( $response->status ){
			update_option('pstds_settings_store_license_status', $response->status);
			
			if('true' == $response->status){
				add_action( 'admin_notices', 'pstds_valid_license' );
			}
			else{
				add_action( 'admin_notices', 'pstds_not_valid_license' );
			}

		}
	}
}

function pstds_valid_license(){
	?>
	<div class="notice notice-success is-dismissible">
		<p><?php _e('Congratulations! <b>'.PSTDS_PLUGIN_NAME.'</b>  Plugin License successfully activated.', 'pstds') ?></p>
	</div>
	<?php
}

function pstds_not_valid_license(){
	?>
	<div class="notice notice-error is-dismissible">
		<p><?php _e('Sorry! <b>'.PSTDS_PLUGIN_NAME.'</b> Plugin License is not valid.', 'pstds') ?></p>
	</div>
	<?php
}

function pstds_delete_license_key(){
	
	$url  = 'https://proaddons.com/api/delete-license';
	$body = array(
		'site_url'     => site_url(),
	);

	$args = array(
		'method'      => 'POST',
		'timeout'     => 45,
		'sslverify'   => false,
		'body'        => $body,
	);

	$request = wp_remote_post( $url, $args );
	$response = wp_remote_retrieve_body( $request );

	$WC_Logger = new WC_Logger();
	$WC_Logger->add( 'storetiming-license-api-delete-response', print_r($response,true) );

	$response = json_decode($response);
	
	if ( wp_remote_retrieve_response_code( $request ) == 200 ) {
		if( !empty( $response->status ) && $response->status == 'true' ){
			delete_option('pstds_settings_pro_licensed_key');
			delete_option('pstds_settings_store_license_status');
		}
	}

}

function pstds_is_licensed(){

	$store_license_status = get_option( 'pstds_settings_store_license_status', true );
	return ($store_license_status == 'false' ? false : true);
}


function pstds_is_store_closed() {
	$store_timezone = get_option( 'pstds_settings_timezone' );
	date_default_timezone_set( $store_timezone );
	$current_time = time();

	$open_time   = strtotime( get_option( 'pstds_settings_store_open_time' ) );
	$close_time = strtotime( get_option( 'pstds_settings_store_closed_time' ) );
	
    // If the store closes after midnight, adjust the close time to tomorrow
	if ($close_time < $open_time) {
        $close_time += 86400; // Add 24 hours
    }
    
    // Check if the current time is between open and close
    if ($current_time >= $open_time && $current_time < $close_time) {
    	return true;
    } else {
    	return false;
    }
}
