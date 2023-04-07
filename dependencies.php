<?php
/**
 * All dependencies Functions.
 *
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


/**
 * Return about woocommerce is active or not.
 *
 * @return boolean
 */
function pstds_woocommerce_active() {
	$active_plugins = (array) get_option( 'active_plugins', array() );

	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}

	if (
		in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) ||
		array_key_exists( 'woocommerce/woocommerce.php', $active_plugins )
	) {
		return true;
	} else {
		add_action( 'admin_notices', 'pstds_admin_notice_plugin_dependencies' );
		return false;
	}

}//end pstds_woocommerce_active()


/**
 * Show admin notice if WooCommerce plugin is not active
 *
 * @return void
 */
function pstds_admin_notice_plugin_dependencies() { ?>
	<div id="message" class="error">
		<p>
			<?php
			$install_url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'install-plugin',
						'plugin' => 'woocommerce',
					),
					admin_url( 'update.php' )
				),
				'install-plugin_woocommerce'
			);

			// translators: %s: is activated.
			printf(
				esc_html__( 'The WooCommerce plugin must be active for ' . PSTDS_PLUGIN_NAME . ' to work. Please install & activate WooCommerce ', 'pstds' ),
				'<strong>',
				'</strong>',
				'<a href="http://wordpress.org/extend/plugins/woocommerce/">',
				'</a>',
				'<a href="' . esc_url( $install_url ) . '">',
				'</a>'
			);
			?>


		</p>
	</div>
	<?php

}//end pstds_admin_notice_plugin_dependencies()
