<?php
/**
 * TEST_LOADER loader Class File.
 *
 * @package pstds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'PSTDS_STORE_DISCOUNT_LOADER' ) ) {

	/**
	 * TEST_LOADER class.
	 */
	class PSTDS_STORE_DISCOUNT_LOADER {

		/**
		 * Ids of product which have discount enabled.
		 *
		 * @var array
		 */
		public $product_ids = array();

		/**
		 * Ids of categories which have discount enabled.
		 *
		 * @var array
		 */
		public $category_ids = array();

		/**
		 * percentageage of Category Discount.
		 *
		 * @var int
		 */
		public $category_discount;

		/**
		 * percentageage of Site Discount.
		 *
		 * @var int
		 */
		public $site_discount;

		/**
		 * percentageage of Product discount.
		 *
		 * @var int
		 */
		public $product_discount;

		/**
		 * Discount Rule.
		 *
		 * @var int
		 */
		public $discount_rule;

		/**
		 * Should sale be enabled on already sale produts?
		 *
		 * @var bool
		 */
		public $should_replace_already_sale = false;

		/**
		 * already sale product notice
		 *
		 * @var string
		 */
		public $already_sale_product_notice;


		/**
		 * Function Constructor.
		 */
		public function __construct() {
			$store_discount_is_enabled              = get_option( 'pstds_settings_discount_status' );
			$store_discount_on_sale_products_status = get_option( 'pstds_settings_store_sale_discount_status' );

			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				return;
			}

			if ( 'yes' === $store_discount_is_enabled ) {

				if ( 'yes' === $store_discount_on_sale_products_status ) {
					$this->should_replace_already_sale = true;
					$this->already_sale_product_notice = get_option( 'pstds_settings_sale_product_notice' );
				}
				$store_discount_type = get_option( 'pstds_settings_discount_type' );
				$store_discount_rule = get_option( 'pstds_settings_discount_rule' );
				$this->discount_rule = $store_discount_rule;
				if ( 'site' === $store_discount_type ) {

					$this->site_discount   = intval( get_option( 'pstds_settings_site_discount' ) );
					$store_discount_banner = get_option( 'pstds_settings_store_discount_banner_status' );

					if ( 'yes' === $store_discount_banner ) {
						add_action( 'wp_head', array( $this, 'site_wide_banner' ), 10 );
					}
				} elseif ( 'product' == $store_discount_type ) {
					$this->product_ids      = get_option( 'pstds_settings_product_discount_ids' );
					$this->product_discount = get_option( 'pstds_settings_product_discount' );

				} elseif ( 'category' == $store_discount_type ) {

					$this->category_ids      = get_option( 'pstds_settings_category_discount_ids' );
					$this->category_discount = intval( get_option( 'pstds_settings_category_discount' ) );

				}//end if
				// change simple products sale price.
				add_filter( 'woocommerce_product_get_sale_price', array( $this, 'simple_sale_price' ), 99, 2 );
				add_filter( 'woocommerce_get_price_html', array( $this, 'simple_sale_price_html' ), 20, 2 );

				// change sale price in cart.
				add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_cart_item_sale_price' ), 10, 1 );

				// change cart table price html.
				add_filter( 'woocommerce_cart_item_price', array( $this, 'change_cart_table_price_display' ), 99, 3 );
				add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'add_price_multiplier_to_variation_prices_hash' ), 99, 3 );

				// change variation product sale price.
				add_filter( 'woocommerce_product_variation_get_price', array( $this, 'variable_sale_price' ), 99, 2 );
				add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'variable_sale_price' ), 99, 2 );

				// change html of variation product.
				add_filter( 'woocommerce_available_variation', array( $this, 'my_variation_range_display' ), 10, 3 );

				// change price range of variable products.
				add_filter( 'woocommerce_variation_prices_price', array( $this, 'custom_variation_price' ), 99, 3 );
				add_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'custom_variation_price' ), 99, 3 );

				// change grouped product html
				add_filter( 'woocommerce_grouped_price_html', array( $this, 'change_grouped_product_html' ), 99, 3 );

				if ( true == $this->should_replace_already_sale ) {
					add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'add_already_sale_discount_notice' ) );
				}

				// var_dump($this->category_ids); die();
			}//end if

		}//end __construct()

		public function add_already_sale_discount_notice() {
			echo htmlspecialchars_decode( $this->already_sale_product_notice );
		}

		public function change_grouped_product_html( $price, $product, $child_prices ) {

			$product_prices = array();
			$index          = 0;

			if ( count( $this->product_ids ) > 0 ) {
				foreach ( $product->get_children() as $product_id ) {
					if ( in_array( "pstds_product_$product_id", $this->product_ids, true ) ) {
						$product_prices[] = $child_prices[ $index ] - ( $child_prices[ $index ] * ( $this->product_discount / 100 ) );
					} else {
						$product_prices[] = $child_prices[ $index ];
					}
					$index++;
				}
				$prices = array( min( $product_prices ), max( $product_prices ) );
			}

			if ( count( $this->category_ids ) > 0 ) {
				foreach ( $product->get_children() as $product_id ) {
					$categories = get_the_terms( $product->get_id(), 'product_cat' );
					if ( $categories ) {
						foreach ( $categories as $key => $category ) {
							if ( in_array( strval( $category->term_id ), $this->category_ids, true ) ) {

								$product_prices[] = $child_prices[ $index ] - ( $this->discount_rule == 'percentage' ? ( $child_prices[ $index ] * ( $this->category_discount / 100 ) ) : $this->category_discount );
							} else {
								$product_prices[] = $child_prices[ $index ];
							}
						}
					}

					$index++;
				}
				$prices = array( min( $product_prices ), max( $product_prices ) );
			}

			if ( ! empty( $this->site_discount ) ) {
				$prices = array( min( $child_prices ) - ( $this->discount_rule == 'percentage' ? ( min( $child_prices ) * ( $this->site_discount / 100 ) ) : $this->site_discount ), max( $child_prices ) - ( $this->discount_rule == 'percentage' ? ( max( $child_prices ) * ( $this->site_discount / 100 ) ) : $this->site_discount ) );
			}

			$price = $prices[0] !== $prices[1] ? sprintf( __( 'From: %1$s', 'woocommerce' ), wc_price( $prices[0] ) ) : wc_price( $prices[0] );
			
			return $price;
		}

		/**
		 * Change Cart Table Price Display.
		 *
		 * @param int   $price price.
		 * @param array $values payload.
		 * @param int   $cart_item_key key.
		 */
		public function change_cart_table_price_display( $price, $values, $cart_item_key ) {
			$slashed_price = $values['data']->get_price_html();
			$is_on_sale    = $values['data']->is_on_sale();
			if ( $is_on_sale ) {
				$price = $slashed_price;
			}

			return $price;

		}//end change_cart_table_price_display()

		/**
		 * Change Display of variation product.
		 *
		 * @param array  $data data.
		 * @param array  $product product payload.
		 * @param object $variation variation payload.
		 */
		public function my_variation_range_display( $data, $product, $variation ) {

			$product_id = $variation->get_parent_id();
			if ( in_array( $product_id, $this->product_ids, true ) ) {
				$data['price_html'] = '<span class="price"><del aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $variation->get_regular_price() . '</bdi></span></del> <ins><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $variation->get_sale_price() . '</bdi></span></ins></span>';
			}
			return $data;

		}//end my_variation_range_display()

		/**
		 * Add_price_multiplier_to_variation_prices_hash.
		 *
		 * @param array $price_hash price data.
		 * @param array $product product payload.
		 * @param array $for_display display payload.
		 */
		public function add_price_multiplier_to_variation_prices_hash( $price_hash, $product, $for_display ) {
			$price_hash[] = $this->simple_sale_price_html( $price_hash, $product );

			return $price_hash;

		}//end add_price_multiplier_to_variation_prices_hash()

		/**
		 * Change variable sale price.
		 *
		 * @param int    $sale_price Sale Price.
		 * @param object $variation variation payload.
		 */
		public function variable_sale_price( $sale_price, $variation ) {
			$product_id = $variation->get_parent_id();
			if ( empty( $sale_price ) || 0 === $sale_price ) {
				if ( in_array( $product_id, $this->product_ids, true ) ) {
					return number_format( ( $variation->get_regular_price() - ( $this->discount_rule == 'percentage' ? ( $variation->get_regular_price() * ( $this->product_discount / 100 ) ) : $this->product_discount ) ), 2 );
				}
				if ( count( $this->category_ids ) > 0 ) {
					$categories = get_the_terms( $product_id, 'product_cat' );
					if ( $categories ) {
						foreach ( $categories as $key => $category ) {
							if ( in_array( strval( $category->term_id ), $this->category_ids, true ) ) {
								return number_format( ( $variation->get_regular_price() - ( $this->discount_rule == 'percentage' ? ( $variation->get_regular_price() * ( $this->category_discount / 100 ) ) : $this->category_discount ) ), 2 );

							}
						}
					}
				}

				if ( ! empty( $this->site_discount ) ) {
					return number_format( ( $variation->get_regular_price() - ( $this->discount_rule == 'percentage' ? ( $variation->get_regular_price() * ( $this->site_discount / 100 ) ) : $this->site_discount ) ), 2 );
				}
			} else {
				if ( true === $this->should_replace_already_sale ) {
					if ( in_array( $product_id, $this->product_ids, true ) ) {
						return number_format( ( $variation->get_regular_price() - ( $this->discount_rule == 'percentage' ? ( $variation->get_regular_price() * ( $this->product_discount / 100 ) ) : $this->product_discount ) ), 2 );
					}
					if ( count( $this->category_ids ) > 0 ) {
						$categories = get_the_terms( $product_id, 'product_cat' );
						if ( $categories ) {
							foreach ( $categories as $key => $category ) {
								if ( in_array( strval( $category->term_id ), $this->category_ids, true ) ) {
									return number_format( ( $variation->get_regular_price() - ( $this->discount_rule == 'percentage' ? ( $variation->get_regular_price() * ( $this->category_discount / 100 ) ) : $this->category_discount ) ), 2 );

								}
							}
						}
					}
					if ( ! empty( $this->site_discount ) ) {
						return number_format( ( $variation->get_regular_price() - ( $this->discount_rule == 'percentage' ? ( $variation->get_regular_price() * ( $this->site_discount / 100 ) ) : $this->site_discount ) ), 2 );
					}
				}
				return $sale_price;
			}

		}//end variable_sale_price()

		/**
		 * Add custom price for variation products.
		 *
		 * @param int    $price price.
		 * @param array  $variation variation payload.
		 * @param object $product product payload.
		 */
		public function custom_variation_price( $price, $variation, $product ) {

			if ( in_array( $product->get_id(), $this->product_ids, true ) ) {
				return number_format( ( $price - ( $this->discount_rule == 'percentage' ? ( $price * ( $this->product_discount / 100 ) ) : $this->product_discount ) ), 2 );
			}
			if ( count( $this->category_ids ) > 0 ) {
				$categories = get_the_terms( $product->get_id(), 'product_cat' );
				if ( $categories ) {
					foreach ( $categories as $key => $category ) {
						if ( in_array( strval( $category->term_id ), $this->category_ids, true ) ) {
							return number_format( ( $price - ( $this->discount_rule == 'percentage' ? ( $price * ( $this->category_discount / 100 ) ) : $this->category_discount ) ), 2 );

						}
					}
				}
			}
			if ( in_array( $product->get_id(), $this->product_ids, true ) ) {
				return number_format( ( $price - ( $this->discount_rule == 'percentage' ? ( $price * ( $this->product_discount / 100 ) ) : $this->product_discount ) ), 2 );
			}
			if ( ! empty( $this->site_discount ) ) {
				return number_format( ( $price - ( $this->discount_rule == 'percentage' ? ( $price * ( $this->site_discount / 100 ) ) : $this->site_discount ) ), 2 );
			}

			return $price;

		}//end custom_variation_price()

		/**
		 * Set sale price in cart items.
		 *
		 * @param object $cart cart payload.
		 */
		public function set_cart_item_sale_price( $cart ) {
			// Iterate through each cart item.
			foreach ( $cart->get_cart() as $cart_item ) {
				// get sale price.
				$price = $cart_item['data']->get_sale_price();
				// Set the sale price.
				$cart_item['data']->set_price( $price );
			}

		}//end set_cart_item_sale_price()

		/**
		 * Add sale price to simple products.
		 *
		 * @param int    $sale_price price.
		 * @param object $product product payload.
		 */
		public function simple_sale_price( $sale_price, $product ) {

			if ( $product->get_type() == 'simple' ) {
				$product_id = $product->get_id();

				if ( empty( $sale_price ) || 0 === $sale_price ) {
					if ( in_array( $product_id, $this->product_ids, true ) ) {
						return number_format( ( $product->get_regular_price() - ( $this->discount_rule == 'percentage' ? ( $product->get_regular_price() * ( $this->product_discount / 100 ) ) : $this->product_discount ) ), 2 );
					}

					if ( count( $this->category_ids ) > 0 ) {
						$categories = get_the_terms( $product_id, 'product_cat' );
						// var_dump($categories); die();
						if ( $categories ) {
							foreach ( $categories as $key => $category ) {

								if ( in_array( strval( $category->term_id ), $this->category_ids, true ) ) {
									return number_format( ( $product->get_regular_price() - ( $this->discount_rule == 'percentage' ? ( $product->get_regular_price() * ( $this->category_discount / 100 ) ) : $this->category_discount ) ), 2 );

								}
							}
						}
					}

					if ( ! empty( $this->site_discount ) ) {
						return number_format( ( $product->get_regular_price() - ( $this->discount_rule == 'percentage' ? ( $product->get_regular_price() * ( $this->site_discount / 100 ) ) : $this->site_discount ) ), 2 );
					}
				} else {
					if ( true === $this->should_replace_already_sale ) {
						if ( in_array( $product_id, $this->product_ids, true ) ) {
							return number_format( ( $product->get_regular_price() - ( $this->discount_rule == 'percentage' ? ( $product->get_regular_price() * ( $this->product_discount / 100 ) ) : $this->product_discount ) ), 2 );
						}
						if ( count( $this->category_ids ) > 0 ) {
							$categories = get_the_terms( $product_id, 'product_cat' );
							if ( $categories ) {
								foreach ( $categories as $key => $category ) {
									if ( in_array( strval( $category->term_id ), $this->category_ids, true ) ) {
										// var_dump( number_format( ( $product->get_regular_price() - ( $this->discount_rule == 'percentage' ?  ( $product->get_regular_price() * ( $this->category_discount / 100 ) ) : $this->category_discount ) ), 2 ) ); die();
										return number_format( ( $product->get_regular_price() - ( $this->discount_rule == 'percentage' ? ( $product->get_regular_price() * ( $this->category_discount / 100 ) ) : $this->category_discount ) ), 2 );

									}
								}
							}
						}
						if ( ! empty( $this->site_discount ) ) {
							return number_format( ( $product->get_regular_price() - ( $this->discount_rule == 'percentage' ? ( $product->get_regular_price() * ( $this->site_discount / 100 ) ) : $this->site_discount ) ), 2 );
						}
					}
					return $sale_price;
				}
			}

		}//end simple_sale_price()

		/**
		 * Site Wide Discount Banner.
		 */
		public function site_wide_banner() {

			$banner = get_option( 'pstds_settings_store_discount_banner' );
			wc_get_template( 'pstds-banner.php', array( 'banner' => $banner ), 'Store-Timing-Discount-wp', PSTDS_TEMP_DIR . '/' );

		}//end site_wide_banner()


		/**
		 * Change html of simple product.
		 *
		 * @param string $price_html price string.
		 * @param object $product product payload.
		 */
		public function simple_sale_price_html( $price_html, $product ) {
			if ( $product->is_type( 'variable' ) || $product->is_type( 'grouped' ) ) {
				return $price_html;
			}

			$product_id = $product->get_id();
			if ( in_array( $product_id, $this->product_ids, true ) || ! empty( $this->site_discount ) ) {
				$price_html = wc_format_sale_price(
					wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ),
					wc_get_price_to_display( $product, array( 'price' => $product->get_sale_price() ) )
				) . $product->get_price_suffix();

				return $price_html;
			}

			return $price_html;

		}//end simple_sale_price_html()



	}//end class

	new PSTDS_STORE_DISCOUNT_LOADER();
}//end if
