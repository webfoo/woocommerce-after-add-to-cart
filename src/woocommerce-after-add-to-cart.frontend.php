<?php
/**
 * Fronend for woocommerce-after-add-to-cart
 *
 * @package woocommerce-after-add-to-cart
 * @author Kevin Ruscoe
 */

/**
 * Redirects to the URL selected for this product.
 */
add_action(
	'woocommerce_add_to_cart_redirect',
	function() {
		$product_id    = end( WC()->cart->cart_contents )['product_id'];
		$current_value = get_post_meta( $product_id, '_after_add_to_cart_redirection_id', true );

		if ( $current_value ) {
			if ( 'default_action' !== $current_value ) {
				return get_permalink( intval( $current_value ) );
			}
		}
	}
);
