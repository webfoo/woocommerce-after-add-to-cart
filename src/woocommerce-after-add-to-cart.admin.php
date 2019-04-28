<?php
/**
 * Admin for woocommerce-after-add-to-cart
 *
 * @package woocommerce-after-add-to-cart
 * @author Kevin Ruscoe
 */

/**
 * Update '_after_add_to_cart_redirection_id' meta key for a standard product.
 */
add_action(
	'woocommerce_process_product_meta',
	function ( $post_id ) {
		if ( isset( $_POST['woocommerce_meta_nonce'], $_POST['_after_add_to_cart_redirection_id'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) {
				exit;
			}

			update_post_meta(
				$post_id,
				'_after_add_to_cart_redirection_id',
				sanitize_text_field( wp_unslash( $_POST['_after_add_to_cart_redirection_id'] ) )
			);
		}
	}
);

/**
 * Displays a select2 box in the "advance" product data.
 */
add_action(
	'woocommerce_product_options_advanced',
	function () {
		$options = [];

		$current_value = get_post_meta( get_the_ID(), '_after_add_to_cart_redirection_id', true );

		if ( $current_value ) {
			if ( 'default_action' === $current_value ) {
				$default = get_option( 'woocommerce_cart_redirect_after_add' );

				if ( 'yes' === $default ) {
					$options['default_action'] = '<span class="option">' .
						'<span class="post_title">Redirect to cart</span>' .
						'<small class="post_type">DEFAULT</small>' .
						'</span>';
				} else {
					$options['default_action'] = '<span class="option">' .
						'<span class="post_title">Stay on product page</span>' .
						'<small class="post_type">DEFAULT</small>' .
						'</span>';
				}
			} else {
				$options[ $current_value ] = sprintf(
					'<span class="option"><span class="post_title">%s</span> <small class="post_type">%s</small></span>',
					get_the_title( $current_value ),
					strtoupper( get_post_type( $current_value ) )
				);
			}
		}

		woocommerce_wp_select(
			[
				'name'              => '_after_add_to_cart_redirection_id',
				'id'                => '_after_add_to_cart_redirection_id',
				'class'             => 'wc-product-search',
				'style'             => 'width: 80%;',
				'label'             => 'Redirect To',
				'desc_tip'          => true,
				'description'       => 'Where should you be redirected to after adding this product to the cart?',
				'options'           => $options,
				'custom_attributes' => [
					'data-placeholder' => 'Select URL',
					'data-action'      => 'after_add_to_cart_item_search',
					'data-exclude'     => get_the_ID(),
				],
			]
		);
	}
);


add_action(
	'admin_head',
	function() {
		print '<style>' .
			'.wc-variation-select2 ~ .select2 {max-width: 100%;min-width: 100%;}' .
			'.select2-container .option {display: flex}' .
			'.select2-container .option .post_title {flex: 1;}' .
			'.select2-container .option .post_type {font-size: 75%;margin-left: auto;letter-spacing: 1px;}' .
		'</style>';
	}
);

/**
 * Performs an AJAX admin call to find pages/products/posts.
 */
add_action(
	'wp_ajax_after_add_to_cart_item_search',
	function() {
		global $wpdb;

		check_ajax_referer( 'search-products', 'security' );

		if ( ! isset( $_GET['term'], $_GET['exclude'] ) ) {
			wp_die();
		}

		$return       = [];
		$term         = sanitize_text_field( wp_unslash( $_GET['term'] ) );
		$exclude      = absint( wp_unslash( $_GET['exclude'] ) );
		$is_variation = 'product_variation' === get_product( $exclude )->post_type;

		// @codingStandardsIgnoreLine
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'select id, post_title, post_type from wp_posts' .
				'where post_title like %s and post_status="publish"' .
				'and post_type in ("post", "page", "product")' .
				'and id != %d',
				'%' . $wpdb->esc_like( $term ) . '%',
				$exclude
			)
		);

		$default = get_option( 'woocommerce_cart_redirect_after_add' );

		if ( 'yes' === $default ) {
			$return['default_action'] = '<span class="option">' .
				'<span class="post_title">Redirect to cart</span>' .
				'<small class="post_type">DEFAULT</small>' .
				'</span>';
		} else {
			$return['default_action'] = '<span class="option">' .
				'<span class="post_title">Stay on product page</span>' .
				'<small class="post_type">DEFAULT</small>' .
				'</span>';
		}

		if ( $is_variation ) {
			$return['as_parent'] = '<span class="option">' .
				'<span class="post_title">Same as parent</span>' .
				'<small class="post_type">PARENT</small>' .
				'</span>';
		}

		foreach ( $results as $result ) {
			$return[ $result->id ] = sprintf(
				'<span class="option"><span class="post_title">%s</span> <small class="post_type">%s</small></span>',
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
 * Displays a select2 box in the "variations" panel for each product.
 */
add_action(
	'woocommerce_product_after_variable_attributes',
	function ( $loop, $variation_data, $variation ) {
		$options = [];

		$current_value = get_post_meta( $variation->ID, '_after_add_to_cart_redirection_id', true );

		if ( $current_value ) {
			if ( 'default_action' === $current_value ) {
				$default = get_option( 'woocommerce_cart_redirect_after_add' );

				if ( 'yes' === $default ) {
					$options['default_action'] = '<span class="option">' .
						'<span class="post_title">Redirect to cart</span>' .
						'<small class="post_type">DEFAULT</small>' .
						'</span>';
				} else {
					$options['default_action'] = '<span class="option">' .
						'<span class="post_title">Stay on product page</span>' .
						'<small class="post_type">DEFAULT</small>' .
						'</span>';
				}
			} elseif ( 'as_parent' === $current_value ) {
				$options['as_parent'] = '<span class="option">' .
					'<span class="post_title">Same as parent</span>' .
					'<small class="post_type">PARENT</span>' .
					'</span>';
			} else {
				$options[ $current_value ] = sprintf(
					'<span class="option"><span class="post_title">%s</span><small class="post_type">%s</small></span>',
					get_the_title( $current_value ),
					strtoupper( get_post_type( $current_value ) )
				);
			}
		}

		print '<div class="options_group form-row form-row-full">';

		woocommerce_wp_select(
			[
				'name'              => '_after_add_to_cart_variation_redirection_id[' . $variation->ID . ']',
				'id'                => '_after_add_to_cart_variation_redirection_id[' . $variation->ID . ']',
				'class'             => 'wc-product-search wc-variation-select2',
				'label'             => 'Redirect To',
				'desc_tip'          => true,
				'description'       => 'Where should you be redirected to after adding this product to the cart?',
				'custom_attributes' => [
					'data-placeholder' => 'Select URL',
					'data-action'      => 'after_add_to_cart_item_search',
					'data-exclude'     => $variation->ID,
				],
				'options'           => $options,
			]
		);

		print '</div>';
	},
	10,
	3
);

/**
 * Update '_after_add_to_cart_redirection_id' meta key for the variations.
 */
add_action(
	'woocommerce_save_product_variation',
	function ( $variation_id ) {
		check_ajax_referer( 'save-variations', 'security' );

		if ( isset( $_POST['_after_add_to_cart_variation_redirection_id'][ $variation_id ] ) ) {
			update_post_meta(
				$variation_id,
				'_after_add_to_cart_redirection_id',
				sanitize_text_field( wp_unslash( $_POST['_after_add_to_cart_variation_redirection_id'][ $variation_id ] ) )
			);
		}
	}
);
