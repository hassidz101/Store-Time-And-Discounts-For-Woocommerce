<?php
/**
 * Plugin Name: Store Time And Discounts For Woocommerce
 * Plugin URI:  proaddons.com
 * Description: Plugin to manage store timings and discount on either whole site or specific products.
 * Version:     1.1.1.0
 * Author:      Aliza Solutions LTD
 * Author URI:  https://proaddons.com
 * Text Domain: pstds
 * Domain Path: /languages
 * License: GPLv2 or later
 *
 * PHP version 7
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PSTDS_PLUGIN_NAME' ) ) {
	define( 'PSTDS_PLUGIN_NAME', 'Store Time And Discounts For Woocommerce' );
}

if ( ! defined( 'PSTDS_PLUGIN_DIR' ) ) {
	define( 'PSTDS_PLUGIN_DIR', __DIR__ );
}

if ( ! defined( 'PSTDS_BASENAME' ) ) {
	define( 'PSTDS_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'PSTDS_PLUGIN_DIR_URL' ) ) {
	define( 'PSTDS_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'PSTDS_TEMP_DIR' ) ) {
	define( 'PSTDS_TEMP_DIR', PSTDS_PLUGIN_DIR . '/templates' );
}

if ( ! defined( 'PSTDS_ASSETS_DIR_URL' ) ) {
	define( 'PSTDS_ASSETS_DIR_URL', PSTDS_PLUGIN_DIR_URL . 'assets' );
}

if ( ! defined( 'PSTDS_ABSPATH' ) ) {
	define( 'PSTDS_ABSPATH', dirname( __FILE__ ) );
}


require_once PSTDS_PLUGIN_DIR . '/dependencies.php';

if ( pstds_woocommerce_active()
) {
	include_once PSTDS_PLUGIN_DIR . '/helpers.php';
include_once PSTDS_PLUGIN_DIR . '/includes/class-pstds-loader.php';
include_once PSTDS_PLUGIN_DIR . '/includes/class-pstds-store-timings-loader.php';

if (pstds_is_licensed()){

	include_once PSTDS_PLUGIN_DIR . '/includes/class-pstds-store-discount-loader.php';

}
}
