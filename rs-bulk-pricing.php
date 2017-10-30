<?php
/*
Plugin Name: RS Bulk Pricing
Version:     1.0.0
Plugin URI:  http://radleysustaire.com/
Description: Allows you to set bulk pricing classes which offer automatic discounts when purchasing multiple products.
Author:      Radley Sustaire
Author URI:  mailto:radleygh@gmail.com
License:     Copyright (c) 2017 Radley Sustaire
*/

if ( !defined( 'ABSPATH' ) ) exit;

define( 'RSBP_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define( 'RSBP_PATH', dirname(__FILE__) );
define( 'RSBP_VERSION', '1.0.0' );

add_action( 'plugins_loaded', 'rsbp_init_plugin' );
register_activation_hook( __FILE__, 'rsbp_plugin_activate' );
register_deactivation_hook( __FILE__, 'rsbp_plugin_deactivate' );

// Initialize plugin: Load plugin files
function rsbp_init_plugin() {
	if ( !class_exists('acf') ) {
		add_action( 'admin_notices', 'rsbp_warn_no_acf' );
		return;
	}
	
	include_once( RSBP_PATH . '/includes/bulk-price.php' ); // Add bulk price groups to the website, and some functionality
	include_once( RSBP_PATH . '/includes/products.php' ); // Add dropdown to products to pick bulk price group
	include_once( RSBP_PATH . '/includes/enqueue.php' ); // Add CSS to the website
	include_once( RSBP_PATH . '/includes/cart.php' ); // Add functionality to calculate cart prices and handle cart item data
	include_once( RSBP_PATH . '/includes/cart-item-functions.php' ); // Functions to handle cart item and session data
}

// Display ACF required warning on admin if ACF is not activated
function rsbp_warn_no_acf() {
	?>
	<div class="error">
		<p><strong>RS Bulk Pricing:</strong> This plugin requires Advanced Custom Fields PRO in order to operate. Please install and activate ACF Pro, or disable this plugin.</p>
	</div>
	<?php
}

// When activating the plugin: flush rewrite rules
function rsbp_plugin_activate() {
	include_once( RSBP_PATH . '/includes/bulk-price.php' );
	
	flush_rewrite_rules();
}

// When deactivating the plugin: flush rewrite rules
function rsbp_plugin_deactivate() {
	flush_rewrite_rules();
}