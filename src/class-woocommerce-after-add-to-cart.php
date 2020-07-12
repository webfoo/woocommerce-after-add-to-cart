<?php

class WooCommerce_After_Add_To_Cart
{
	protected $file;

	public function __construct(string $file)
	{
		$this->file = $file;

		// register plugin activation/deactivation hooks
		register_activation_hook( $this->file, array( $this, 'activate' ) );
		register_deactivation_hook( $this->file, array( $this, 'deactivate' ) );
	}

	/**
	 * Activate the plugin.
	 *
	 * @return void
	 */
	public function activate()
	{

	}

	/**
	 * Deactivate the plugin.
	 *
	 * @return void
	 */
	public function deactivate()
	{

	}

	/**
	 * The core function.
	 *
	 * @return void
	 */
	public function run() {

		/**
		 * Performs an AJAX admin call to find pages/products/posts.
		 */
		add_action(
			'wp_ajax_woocommerce_after_add_to_cart_item_search',
			function() {
				global $wpdb;

				check_ajax_referer( 'search-products', 'security' );

				if ( ! isset( $_GET['term'], $_GET['exclude'] ) ) {
					wp_die();
				}

				$return    = array();
				$term      = sanitize_text_field( wp_unslash( $_GET['term'] ) );
				$exclude   = absint( wp_unslash( $_GET['exclude'] ) );

				$return[ 'default_action' ] = "No action, continue to cart";

				// variants should have this additional value
				if ( "product_variation" === get_post_type( $exclude ) ) {
					$return[ 'as_parent' ] = "Same as parent";
				}

				// @codingStandardsIgnoreLine
				$results = $wpdb->get_results(
					$wpdb->prepare(
						'select id, post_title, post_type from wp_posts ' .
						'where post_title like %s and post_status="publish" ' .
						'and post_type in ("post", "page", "product") and id != %d',
						'%' . $wpdb->esc_like( $term ) . '%',
						$exclude
					)
				);

				foreach ( $results as $result ) {
					$return[ $result->id ] = sprintf(
						'%s [%s]',
						$result->post_title,
						strtoupper( $result->post_type )
					);
				}

				wp_die(
					wp_json_encode( $return )
				);
			}
		);

		/**
		 * Displays a select2 box in the "advance" product data.
		 */
		add_action(
			'woocommerce_product_options_advanced',
			function () {
				$this->generate_select2_box( get_the_id() );
			}
		);

		/**
		 * Displays a select2 box in the "variations" panel for each product.
		 */
		add_action(
			'woocommerce_product_after_variable_attributes',
			function ( $loop, $variation_data, $variation ) {
				$this->generate_select2_box( $variation->ID );
			},
			10,
			3
		);

		/**
		 * Fixes select2 for variants.
		 */
		add_action(
			'admin_head',
			function() {
				print "<style>
					p.form-field[class*='woocommerce_after_add_to_cart_redirect_to'] .select2 {width: 100% !important; display: block;}
					#advanced_product_data p.form-field[class*='woocommerce_after_add_to_cart_redirect_to'] .select2 {width: 80% !important;}
				</style>";
			}
		);

		/**
		 * Update 'woocommerce_after_add_to_cart_redirect_to' meta key for a standard product.
		 */
		add_action(
			'woocommerce_process_product_meta',
			function ( $post_id ) {
				if ( isset( $_POST['woocommerce_meta_nonce'] ) ) {
					if ( ! wp_verify_nonce( sanitize_key( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) {
						wp_die( 'Malformed Nonce.' );
					}
				}

				if ( isset( $_POST['woocommerce_after_add_to_cart_redirect_to'] ) ) {
					update_post_meta(
						$post_id,
						'woocommerce_after_add_to_cart_redirect_to',
						sanitize_text_field( wp_unslash( $_POST['woocommerce_after_add_to_cart_redirect_to'] ) )
					);
				}
			}
		);

		/**
		 * Update 'woocommerce_after_add_to_cart_redirect_to' meta key for the variations.
		 */
		add_action(
			'woocommerce_save_product_variation',
			function ( $variation_id ) {
				check_ajax_referer( 'save-variations', 'security' );

				if ( isset( $_POST['woocommerce_after_add_to_cart_redirect_to'][ $variation_id ] ) ) {
					update_post_meta(
						$variation_id,
						'woocommerce_after_add_to_cart_redirect_to',
						sanitize_text_field( wp_unslash( $_POST['woocommerce_after_add_to_cart_redirect_to'][ $variation_id ] ) )
					);
				}
			}
		);

		// FE


		/**
		 * Redirects to the URL selected for this product.
		 */
		add_action(
			'woocommerce_add_to_cart_redirect',
			function( $url ) {
				// phpcs:disable WordPress.Security.NonceVerification
				if ( ! isset( $_POST['add-to-cart'] ) ) {
					return;
				}

				$post_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : absint( $_POST['add-to-cart'] );

				return $this->resolve_redirection_object( $post_id );
				// phpcs:enable WordPress.Security.NonceVerification
			}
		);

		/**
		 * Redirects to the URL selected for this product, ajax version.
		 */
		add_action(
			'woocommerce_ajax_added_to_cart',
			function ( $product_id ) {
				$url = $this->resolve_redirection_object( $product_id );

				$data = array(
					'error'       => true,
					'product_url' => $url,
				);

				wp_send_json( $data );
			}
		);
	}

	private function resolve_redirection_object ( $post_id ) {
		$redirection_object = get_post_meta( $post_id, 'woocommerce_after_add_to_cart_redirect_to', true );

		if ( 'as_parent' === $redirection_object ) {
			$variant = wc_get_product( $post_id );

			return $this->resolve_redirection_object( $variant->get_parent_id() );
		}

		if ( 'default_action' === $redirection_object ) {
			return;
		}

		return get_permalink( $redirection_object );
	}

	private function generate_select2_box( $post_id )
	{
		$subject = wc_get_product( $post_id );
		$is_variant = 'product_variation' === $subject->post_type;
		$field_name = 'woocommerce_after_add_to_cart_redirect_to';
		$field_class = 'wc-product-search';
		$options = array();

		$current_redirect = get_post_meta( $subject->get_id(), 'woocommerce_after_add_to_cart_redirect_to', true );

		if ( 'as_parent' === $current_redirect ) {
			$options = [ 'as_parent' => 'Same as parent' ];
		}

		if ( 'default_action' === $current_redirect ) {
			$options = [ 'default_action' => 'No action, continue to cart' ];
		}

		if ( empty( $options ) ) {
			$current_redirect = get_post( $current_redirect );

			$options[ $current_redirect->ID ] = sprintf(
				"%s [%s]",
				$current_redirect->post_title,
				strtoupper($current_redirect->post_type)
			);
		}

		if ($is_variant) {
			print '<div class="options_group form-row form-row-full">';

			$field_name = 'woocommerce_after_add_to_cart_redirect_to[' . $subject->get_id() . ']';
			$field_class = 'wc-product-search wc-variation-select2';
		}

		woocommerce_wp_select(
			array(
				'name'              => $field_name,
				'id'                => $field_name,
				'class'             => $field_class,
				'label'             => 'Redirect To',
				'desc_tip'          => true,
				'description'       => 'Where should you be redirected to after adding this product to the cart?',
				'options'           => $options,
				'custom_attributes' => array(
					'data-placeholder'  => 'Select URL',
					'data-action'       => 'woocommerce_after_add_to_cart_item_search',
					'data-exclude'      => $subject->get_id(),
					'data-product-type' => $subject->get_type(),
				),
			)
		);

		if ( $is_variant ) {
			print "</div>";
		}
	}
}