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

				$default_label = "Default Action (don't redirect to cart)";
				if ( 'yes' === $default ) {
					$default_label = 'Default Action (redirect to cart)';
				}

				$options['default_action'] = $default_label;
			} else {
				$options[ $current_value ] = sprintf(
					"%s (%s)",
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
				'custom_attributes' => [
					'data-placeholder' => 'Select URL',
					'data-action'      => 'after_add_to_cart_item_search',
					'data-exclude'     => get_the_ID(),
				],
				'options'           => $options,
			]
		);
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

		$term         = sanitize_text_field( wp_unslash( $_GET['term'] ) );
		$exclude      = absint( wp_unslash( $_GET['exclude'] ) );
		$is_variation = 'product_variation' === get_product( $exclude )->post_type;

		// @codingStandardsIgnoreLine
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'select id, post_title, post_type from wp_posts ' .
				"where post_title like %s and post_status='publish' " .
				"and post_type in ('post', 'page', 'product')" .
				'and id != %d',
				'%' . $wpdb->esc_like( $term ) . '%',
				$exclude
			)
		);

		$default = get_option( 'woocommerce_cart_redirect_after_add' );

		$default_label = "Default Action (don't redirect to cart)";
		if ( 'yes' === $default ) {
			$default_label = 'Default Action (redirect to cart)';
		}

		$return = [
			'default_action' => $default_label,
		];

		if ( $is_variation ) {
			$return['as_parent'] = 'Same as parent';
		}

		foreach ( $results as $result ) {
			$return[ $result->id ] = sprintf(
				"%s (%s)",
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

				$default_label = "Default Action (don't redirect to cart)";
				if ( 'yes' === $default ) {
					$default_label = 'Default Action (redirect to cart)';
				}

				$options['default_action'] = $default_label;
			} elseif ( 'as_parent' === $current_value ) {
				$options['as_parent'] = 'Same as parent';
			} else {
				$options[ $current_value ] = sprintf(
					"%s <span style='float: right; color: #000'>%s</span>",
					get_the_title( $current_value ),
					strtoupper( get_post_type( $current_value ) )
				);
			}
		}

		woocommerce_wp_select(
			[
				'name'              => '_after_add_to_cart_variantion_redirection_id[' . $variation->ID . ']',
				'id'                => '_after_add_to_cart_variantion_redirection_id[' . $variation->ID . ']',
				'class'             => 'wc-product-search',
				'style'             => 'width: 80%;',
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

		if ( isset( $_POST['_after_add_to_cart_variantion_redirection_id'][ $variation_id ] ) ) {
			update_post_meta(
				$variation_id,
				'_after_add_to_cart_redirection_id',
				sanitize_text_field( wp_unslash( $_POST['_after_add_to_cart_variantion_redirection_id'][ $variation_id ] ) )
			);
		}
	}
);
