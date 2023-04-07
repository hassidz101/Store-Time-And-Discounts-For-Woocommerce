<?php
/**
 * PSTDS_STORE_TIMINGS loader Class File.
 *
 * @package pstds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'PSTDS_STORE_TIMINGS_LOADER' ) ) {

	/**
	 * TEST_LOADER class.
	 */
	class PSTDS_STORE_TIMINGS_LOADER {

		public $time_type;
		public $categories;
		public $products;

		/**
		 * Function Constructor.
		 */
		public function __construct() {
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				return;
			}
			$store_time_is_disabled = get_option( 'pstds_settings_store_time_status' );

			if ( 'yes' === $store_time_is_disabled ) {

				$store_time_type = get_option( 'pstds_settings_store_type' );
				if(!pstds_is_licensed()){
					$store_time_type = 'site';
				}
				$this->time_type = $store_time_type;

				if ( 'category' === $store_time_type ) {
					$store_time_categories = get_option( 'pstds_settings_store_category_time_ids' );
					$this->categories      = $store_time_categories;
				} elseif ( 'product' === $store_time_type ) {
					$store_time_products = get_option( 'pstds_settings_store_product_time_ids' );
					$this->products      = $store_time_products;
				}

				add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'pstds_replace_add_to_cart' ), 99, 2 );
				add_action( 'woocommerce_simple_add_to_cart', array( $this, 'pstds_remove_add_to_cart' ) );
				add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'pstds_validate_add_cart_item' ), 10, 2 );
				add_action( 'wp', array( $this, 'pstds_store_closed_notice' ), 10 );
				add_action( 'wp', array( $this, 'pstds_empty_cart' ), 10 );
			}

		}//end __construct()
		/**
		 * Replace Add to Cart button.
		 *
		 * @param string $add_to_cart_link Add to Cart Html.
		 * @param array  $product product payload.
		 */
		public function pstds_replace_add_to_cart( $add_to_cart_link, $product ) {
			// var_dump($product);die();
			if ( ! pstds_is_store_closed() ) {
				if ( 'site' === $this->time_type ) {
					$closed_notice    = get_option( 'pstds_settings_store_closed_notice' );
					$add_to_cart_link = do_shortcode( '<button  class="btn btn-primary disabled">' . htmlspecialchars_decode( $closed_notice ) . '</button>' );
				} elseif ( 'product' === $this->time_type ) {
					if ( in_array( $product->get_id(), $this->products ) ) {
						$closed_notice    = get_option( 'pstds_settings_store_closed_notice' );
						$add_to_cart_link = do_shortcode( '<button  class="btn btn-primary disabled">' . htmlspecialchars_decode( $closed_notice ) . '</button>' );
					}
				} elseif ( 'category' === $this->time_type ) {
					foreach ( $product->category_ids as $key => $id ) {
						if ( in_array( $id, $this->categories ) ) {
							$closed_notice    = get_option( 'pstds_settings_store_closed_notice' );
							$add_to_cart_link = do_shortcode( '<button  class="btn btn-primary disabled">' . htmlspecialchars_decode( $closed_notice ) . '</button>' );
						}
					}
				}
			}

			return $add_to_cart_link;

		}//end pstds_replace_add_to_cart()

		/**
		 * Replace Add to Cart button.
		 */
		public function pstds_store_closed_notice() {
			wc_clear_notices();
			if ( ! pstds_is_store_closed() ) {
				if ( 'site' === $this->time_type ) {
					$closed_notice = get_option( 'pstds_settings_store_closed_notice' );
					wc_add_notice( __( $closed_notice, 'pstds' ), 'error' );
				}
			}

		}//end pstds_store_closed_notice()

		/**
		 * Replace Add to Cart button.
		 *
		 * @param boolean $passed Should proceed to cart.
		 * @param int     $product_id Product ID.
		 */
		public function pstds_validate_add_cart_item( $passed, $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! pstds_is_store_closed() ) {
				if ( 'site' === $this->time_type ) {
					$passed        = false;
					$closed_notice = get_option( 'pstds_settings_store_closed_notice' );
					wc_add_notice( __( htmlspecialchars_decode( $closed_notice ), 'pstds' ), 'error' );
				} elseif ( 'product' === $this->time_type ) {
					if ( in_array( $product->get_id(), $this->products ) ) {
						$passed        = false;
						$closed_notice = get_option( 'pstds_settings_store_closed_notice' );
						wc_add_notice( __( htmlspecialchars_decode( $closed_notice ), 'pstds' ), 'error' );
					}
				} elseif ( 'category' === $this->time_type ) {
					foreach ( $product->category_ids as $key => $id ) {
						if ( in_array( $id, $this->categories ) ) {
							$passed        = false;
							$closed_notice = get_option( 'pstds_settings_store_closed_notice' );
							wc_add_notice( __( htmlspecialchars_decode( $closed_notice ), 'pstds' ), 'error' );
						}
					}
				}
			}

			return $passed;

		}//end pstds_validate_add_cart_item()

		/**
		 * Remove Add to Cart Button.
		 */
		public function pstds_remove_add_to_cart() {
			global $product;

			if ( ! pstds_is_store_closed() ) {
				if ( 'site' === $this->time_type ) {
					$closed_notice = get_option( 'pstds_settings_store_closed_notice' );
					remove_action( 'woocommerce_' . $product->get_type() . '_add_to_cart', 'woocommerce_' . $product->get_type() . '_add_to_cart', 30 );
					echo '<button  class="btn btn-primary disabled">' . htmlspecialchars_decode( $closed_notice ) . '</button>';
				} elseif ( 'product' === $this->time_type ) {
					if ( in_array( $product->get_id(), $this->products ) ) {
						$closed_notice = get_option( 'pstds_settings_store_closed_notice' );
						remove_action( 'woocommerce_' . $product->get_type() . '_add_to_cart', 'woocommerce_' . $product->get_type() . '_add_to_cart', 30 );
						echo '<button  class="btn btn-primary disabled">' . htmlspecialchars_decode( $closed_notice ) . '</button>';
					}
				} elseif ( 'category' === $this->time_type ) {
					foreach ( $product->category_ids as $key => $id ) {
						if ( in_array( $id, $this->categories ) ) {
							$closed_notice = get_option( 'pstds_settings_store_closed_notice' );
							remove_action( 'woocommerce_' . $product->get_type() . '_add_to_cart', 'woocommerce_' . $product->get_type() . '_add_to_cart', 30 );
							echo '<button  class="btn btn-primary disabled">' . htmlspecialchars_decode( $closed_notice ) . '</button>';
						}
					}
				}
			}

		}//end pstds_remove_add_to_cart()

		/**
		 * Empty add to cart when store is closed.
		 *
		 * @param boolean $checkout Should proceed to checkout.
		 */
		public function pstds_empty_cart( $checkout ) {

			if ( ! pstds_is_store_closed() ) {

				if ( 'site' === $this->time_type ) {

					if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
						global $woocommerce;
						$woocommerce->cart->empty_cart();

						$empty_notice = get_option( 'pstds_settings_cart_empty_notice' );
						wc_add_notice( __( $empty_notice, 'pstds' ), 'error' );
					}
				} elseif ( 'product' === $this->time_type ) {
					$items = WC()->cart->get_cart();
					foreach ( $items as $item => $values ) {
						// var_dump($values); die();
						// Load product object
						$product = wc_get_product( $values['data']->get_id() );
						if ( in_array( $product->get_id(), $this->products ) ) {

							WC()->cart->remove_cart_item( $values['key'] );

							$empty_notice = get_option( 'pstds_settings_product_closed_notice' );
							wc_add_notice( __( $empty_notice, 'pstds' ), 'error' );
						}
					}
				} elseif ( 'category' === $this->time_type ) {
					$items = WC()->cart->get_cart();
					foreach ( $items as $item => $values ) {
						// var_dump($values); die();
						// Load product object
						$product    = wc_get_product( $values['data']->get_id() );
						$categories = get_the_terms( $product->get_id(), 'product_cat' );
						if ( $categories ) {
							foreach ( $categories as $key => $category ) {
								if ( in_array( $category->term_id, $this->categories ) ) {

									WC()->cart->remove_cart_item( $values['key'] );

									$empty_notice = get_option( 'pstds_settings_category_closed_notice' );
									wc_add_notice( __( $empty_notice, 'pstds' ), 'error' );
								}
							}
						}
					}
				}
			}

		}//end pstds_empty_cart()




	}//end class

	new PSTDS_STORE_TIMINGS_LOADER();
}//end if
