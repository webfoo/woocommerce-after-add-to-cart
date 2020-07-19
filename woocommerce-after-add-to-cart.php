<?php
/**
 * Plugin Name: Woocommerce After Add To Cart
 * Description: A woocommerce plugin that allows finer control over the action that is performed after adding a product to the cart.
 * Plugin URI: https://github.com/kevinruscoe/woocommerce-after-add-to-cart
 * Version: 2.0
 * Author: Kevin Ruscoe
 * Author URI: https://github.com/kevinruscoe
 *
 * @package Woocommerce After Add To Cart
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

require __DIR__ . '/src/class-woocommerce-after-add-to-cart.php';

$plugin = new WooCommerce_After_Add_To_Cart(__FILE__);

$plugin->run();