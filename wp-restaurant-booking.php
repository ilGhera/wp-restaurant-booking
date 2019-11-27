<?php
/**
 * Plugin Name: WordPress Restaurant Booking - Premium
 * Plugin URI: https://www.ilghera.com/product/wordpress-restaurant-booking-premium
 * Description: xxxxxxx
 * Author: ilGhera
 * Version: 0.9.0
 * Author URI: https://ilghera.com
 * Requires at least: 4.0
 * Tested up to: 5
 * WC tested up to: 3
 * Text Domain: wprb
 */


/**
 * Handles the plugin activation
 * @return void
 */
function load_wp_restaurant_booking() {

	/*Function check */
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}

	/*Internationalization*/
	load_plugin_textdomain( 'wprb', false, basename( dirname( __FILE__ ) ) . '/languages' );

	/*Constants declaration*/
	define( 'WPRB_DIR', plugin_dir_path( __FILE__ ) );
	define( 'WPRB_URI', plugin_dir_url( __FILE__ ) );
	define( 'WPRB_INCLUDES', WPRB_DIR . 'includes/' );
	define( 'WPRB_ADMIN', WPRB_DIR . 'admin/' );
	define( 'WPRB_SETTINGS', admin_url( 'admin.php?page=wp-restaurant-booking' ) );

	/*Files required*/
	require( WPRB_ADMIN . 'class-wprb-admin.php' );
	require( WPRB_INCLUDES . 'class-wprb-reservations.php' );
	require( WPRB_INCLUDES . 'class-wprb-reservation-widget.php' );
	require( WPRB_INCLUDES . 'class-wprb-notifications.php' );

}
add_action( 'plugins_loaded', 'load_wp_restaurant_booking', 10 );
