<?php
/**
 * Admin for Add To Cart Fine Control.
 *
 * @package woocommerce-add-to-cart-fine-control
 * @author Kevin Ruscoe
 */

/**
 * Update '_after_add_to_cart_redirection_id' meta key.
 */
add_action(
	'woocommerce_process_product_meta',
	function ( $post_id ) {
		if ( isset( $_POST['woocommerce_meta_nonce'], $_POST['_after_add_to_cart_redirection_id'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) {
				exit;
			}

			$product = wc_get_product( $post_id );
			$product->update_meta_data(
				'_after_add_to_cart_redirection_id',
				sanitize_text_field( wp_unslash( $_POST['_after_add_to_cart_redirection_id'] ) )
			);
			$product->save();
		}
	}
);

/**
 * Displays content in the 'Sample Product' new tab.
 */
add_action(
	'woocommerce_product_options_advanced',
	function () {
		$options = [];

		$current_value = get_post_meta( get_the_ID(), '_after_add_to_cart_redirection_id' )[0];

		if ( $current_value ) {
			if ( 'NULL' === $current_value ) {
				$default = get_option( 'woocommerce_cart_redirect_after_add' );

				$default_label = "Default Action (don't redirect to cart)";
				if ( 'yes' === $default ) {
					$default_label = 'Default Action (redirect to cart)';
				}

				$options['NULL'] = $default_label;
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
				'id'                => '_after_add_to_cart_redirection_id',
				'class'             => 'wc-product-search',
				'label'             => 'Redirect To',
				'name'              => '_after_add_to_cart_redirection_id',
				'style'             => 'width: 80%;',
				'desc_tip'          => true,
				'description'       => 'Where should you be redirected to after adding this product to the cart?',
				'custom_attributes' => [
					'data-placeholder' => 'Select URL',
					'data-action'      => 'add_to_cart_fine_control_search_things',
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
	'wp_ajax_add_to_cart_fine_control_search_things',
	function() {
		global $wpdb;

		check_ajax_referer( 'search-products', 'security' );

		if ( ! isset( $_GET['term'], $_GET['exclude'] ) ) {
			wp_die();
		}

		$term    = sanitize_text_field( wp_unslash( $_GET['term'] ) );
		$exclude = absint( wp_unslash( $_GET['exclude'] ) );

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
			'NULL' => $default_label,
		];

		foreach ( $results as $result ) {
			$return[ $result->id ] = sprintf(
				"%s <span style='float: right; color: #000'>%s</span>",
				$result->post_title,
				strtoupper( $result->post_type )
			);
		}

		wp_die(
			wp_json_encode( $return )
		);
	}
);
