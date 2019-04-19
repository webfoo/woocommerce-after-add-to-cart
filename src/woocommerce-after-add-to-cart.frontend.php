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
		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_POST['add-to-cart'] ) ) {
			return;
		}

		$is_variation = isset( $_POST['variation_id'] );

		$post_id = $is_variation ? absint( $_POST['variation_id'] ) : absint( $_POST['add-to-cart'] );

		$current_value = get_post_meta( $post_id, '_after_add_to_cart_redirection_id', true );

		if ( $current_value ) {
			if ( 'as_parent' === $current_value ) {
				$current_value = get_post_meta( absint( $_POST['add-to-cart'] ), '_after_add_to_cart_redirection_id', true );
			}

			if ( 'default_action' !== $current_value ) {
				return get_permalink( intval( $current_value ) );
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification
	}
);
