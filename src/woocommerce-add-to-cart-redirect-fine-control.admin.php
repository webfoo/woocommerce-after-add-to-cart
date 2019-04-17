<?php


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
            
            
            
		</div>
		<?php
	}
);