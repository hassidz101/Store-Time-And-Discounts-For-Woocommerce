<?php
/**
 * TEST_LOADER loader Class File.
 *
 * @package pstds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'PSTDS_LOADER' ) ) {

	/**
	 * TEST_LOADER class.
	 */
	class PSTDS_LOADER {



		/**
		 * Function Constructor.
		 */
		public function __construct() {
			add_filter( 'plugin_action_links_'.PSTDS_BASENAME, array($this, 'settings_link') );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'front_assets' ) );
			
			$this->includes();

		}//end __construct()

		public function settings_link( $links ) {
			$links[] = '<a href="' .
			admin_url( 'admin.php?page=wc-settings&tab=pstds_settings' ) .
			'">' . __( 'Settings' ) . '</a>';
			return $links;
		}

		/**
		 * Include the settings.
		 */
		public function includes() {
			if ( is_admin() ) {
				include_once PSTDS_PLUGIN_DIR . '/includes/admin/class-pstds-settings.php';
			}

		}//end includes()

		/**
		 * Include the admin assets.
		 */
		public function admin_assets() {
			if ( isset( $_GET['page'] ) && isset( $_GET['tab'] ) ) {
				if ( 'wc-settings' === $_GET['page'] && 'pstds_settings' === $_GET['tab'] ) {
					wp_enqueue_style( 'pstds-admin-style', PSTDS_ASSETS_DIR_URL . '/css/admin/admin.css', array(), wp_rand( 1000, 9999 ) );
					wp_enqueue_script( 'pstds-admin-script', PSTDS_ASSETS_DIR_URL . '/js/admin/admin.js', array( 'jquery' ), wp_rand( 1000, 9999 ) );

					wp_localize_script(
						'pstds-admin-script',
						'pstds_ajax_object',
						array(
							'ajax_url' => admin_url( 'admin-ajax.php' ),
							'pstds_is_licensed' => pstds_is_licensed(),
							'nonce'    => wp_create_nonce( 'ajax-nonce' ),
						)
					);
				}
			}

		}//end admin_assets()

		/**
		 * Include the front assets.
		 */
		public function front_assets() {
			wp_enqueue_style( 'pstds-front-style', PSTDS_ASSETS_DIR_URL . '/css/style.css', array(), wp_rand( 1000, 9999 ) );
			wp_enqueue_script( 'pstds-front-script', PSTDS_ASSETS_DIR_URL . '/js/script.js', array( 'jquery' ), wp_rand( 1000, 9999 ) );

			// bootstrap.
			wp_register_script(
				'pstds-bootstrap-script',
				'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
				array( 'jquery' ),
				false,
				false
			);

			wp_register_style( 'pstds-bootstrap-style', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css' );

		}//end front_assets()

	}//end class

	new PSTDS_LOADER();
}//end if
