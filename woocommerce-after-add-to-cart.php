<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              kevinruscoe.me
 * @since             1.0.0
 * @package           Woocommerce_After_Add_To_Cart
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce - After Add To Cart
 * Plugin URI:        https://github.com/kevinruscoe/woocommerce-after-add-to-cart
 * Description:       A woocommerce plugin that allows finer control over the action that is performed after adding a product to the cart.
 * Version:           1.0.0
 * Author:            Kevin Ruscoe
 * Author URI:        kevinruscoe.me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-after-add-to-cart
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOOCOMMERCE_AFTER_ADD_TO_CART_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-after-add-to-cart-activator.php
 */
function activate_woocommerce_after_add_to_cart() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-after-add-to-cart-activator.php';
	Woocommerce_After_Add_To_Cart_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-after-add-to-cart-deactivator.php
 */
function deactivate_woocommerce_after_add_to_cart() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-after-add-to-cart-deactivator.php';
	Woocommerce_After_Add_To_Cart_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woocommerce_after_add_to_cart' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_after_add_to_cart' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-after-add-to-cart.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woocommerce_after_add_to_cart() {

	$plugin = new Woocommerce_After_Add_To_Cart();
	$plugin->run();

}
run_woocommerce_after_add_to_cart();
