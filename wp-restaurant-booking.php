<?php
/**
 * Plugin Name: WP Restaurant Booking
 * Plugin URI: https://www.ilghera.com/product/wordpress-restaurant-booking-premium
 * Description: A feature-rich and easy to use reservation system for bars and restaurants.
 * Author: ilGhera
 * Version: 0.9.0
 * Author URI: https://ilghera.com
 * Requires at least: 4.0
 * Tested up to: 5.3
 * WC tested up to: 3
 * Text Domain: wp-restaurant-booking
 * Domain Path: /languages
 */

/**
 * Handles the plugin activation
 *
 * @return void
 */
function load_wp_restaurant_booking() {

	/*Function check */
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}

	/*Constants declaration*/
	define( 'WPRB_DIR', plugin_dir_path( __FILE__ ) );
	define( 'WPRB_URI', plugin_dir_url( __FILE__ ) );
	define( 'WPRB_INCLUDES', WPRB_DIR . 'includes/' );
	define( 'WPRB_ADMIN', WPRB_DIR . 'admin/' );
	define( 'WPRB_SETTINGS', admin_url( 'admin.php?page=wp-restaurant-booking' ) );

	/*Internationalization*/
	$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-restaurant-booking' );
	load_plugin_textdomain( 'wp-restaurant-booking', false, basename( WPRB_DIR ) . '/languages' );
	load_textdomain( 'wp-restaurant-booking', trailingslashit( WP_LANG_DIR ) . basename( WPRB_DIR ) . '/wp-restaurant-booking-' . $locale . '.mo' );

	/*Files required*/
	require( WPRB_ADMIN . 'class-wprb-admin.php' );
	require( WPRB_INCLUDES . 'class-wprb-reservations.php' );
	require( WPRB_INCLUDES . 'class-wprb-reservation-widget.php' );
	require( WPRB_INCLUDES . 'class-wprb-notifications.php' );

	/*Create "Reservations manager" role if doesn't exist*/
	WPRB_Admin::add_user_role();

}
add_action( 'plugins_loaded', 'load_wp_restaurant_booking', 100 );
