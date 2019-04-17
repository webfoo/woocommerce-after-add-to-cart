<?php
/**
 * Admin for Add To Cart Fine Control.
 *
 * @package woocommerce-add-to-cart-fine-control
 * @author Kevin Ruscoe
 */

/**
 * Update '_redirect_to_cart_action' meta key.
 */
add_action(
	'woocommerce_process_product_meta',
	function ( $post_id ) {
		if ( isset( $_POST['woocommerce_meta_nonce'], $_POST['_redirect_to_cart_action'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) {
				exit;
			}

			$product = wc_get_product( $post_id );
			$product->update_meta_data(
				'_redirect_to_cart_action',
				isset( $_POST['_redirect_to_cart_action'] ) ? 'yes' : 'no'
			);
			$product->save();
		}
	}
);

/**
 * Adds a new tab named 'Sample Product' to the admin product data panel.
 */
add_filter(
	'woocommerce_product_data_tabs',
	function ( $default_tabs ) {
		$default_tabs['redirect_to_cart_action'] = array(
			'label'    => 'Add To Cart Action',
			'target'   => 'redirect_to_cart_action_tab_data',
			'priority' => 100,
		);
		return $default_tabs;
	}
);

/**
 * Displays content in the 'Sample Product' new tab.
 */
add_action(
	'woocommerce_product_data_panels',
	function () {
		?>
		<div id="redirect_to_cart_action_tab_data" class="panel woocommerce_options_panel">
			<?php
			woocommerce_wp_select(
				[
					'id'                => '_add_to_cart_action',
					'class'             => 'wc-product-search',
					'label'             => 'Add To Cart Action',
					'name'              => '_add_to_cart_action',
					'style'             => 'width: 50%;',
					'custom_attributes' => [
						'data-placeholder' => 'Select action',
						'data-action'      => 'add_to_cart_search_products_pages',
					],
				]
			)
			?>
		</div>
		<?php
	}
);

/**
 * Performs an AJAX admin call to find pages/products/posts.
 */
add_action(
	'wp_ajax_add_to_cart_search_products_pages',
	function() {
		global $wpdb;

		// @codingStandardsIgnoreLine
		if ( ! isset( $_GET['term'] ) ) {
			wp_die();
		}

		// @codingStandardsIgnoreLine
		$term = sanitize_text_field( wp_unslash( $_GET['term'] ) );

		// @codingStandardsIgnoreLine
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"select id, post_title, post_type from wp_posts where post_title like %s and post_status='publish' and post_type in ('post', 'page', 'product')",
				'%' . $wpdb->esc_like( $term ) . '%'
			)
		);

		$return = [];

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
