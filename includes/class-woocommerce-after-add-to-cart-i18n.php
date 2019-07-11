<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       kevinruscoe.me
 * @since      1.0.0
 *
 * @package    Woocommerce_After_Add_To_Cart
 * @subpackage Woocommerce_After_Add_To_Cart/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woocommerce_After_Add_To_Cart
 * @subpackage Woocommerce_After_Add_To_Cart/includes
 * @author     Kevin Ruscoe <hello@kevinruscoe.me>
 */
class Woocommerce_After_Add_To_Cart_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woocommerce-after-add-to-cart',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
