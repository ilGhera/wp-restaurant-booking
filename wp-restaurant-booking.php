<?php
/**
 * Plugin Name: WordPress Restaurant Booking - Premium
 * Plugin URI: https://www.ilghera.com/product/wordpress-restaurant-booking-premium
 * Description: A feature-rich and easy to use reservation system for bars and restaurants.
 * Author: ilGhera
 * Version: 0.9.0
 * Author URI: https://ilghera.com
 * Requires at least: 4.0
 * Tested up to: 5
 * WC tested up to: 3
 * Text Domain: wprb
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

	/*Create "Reservations manager" role if doesn't exist*/
	WPRB_Admin::add_user_role();

}
add_action( 'after_setup_theme', 'load_wp_restaurant_booking', 10 );

/**
 * Update checker Builder
 */
require( plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php' );
$wprb_update_checker = Puc_v4_Factory::buildUpdateChecker(
	'https://www.ilghera.com/wp-update-server-2/?action=get_metadata&slug=wp-restaurant-booking-premium',
	__FILE__,
	'wp-restaurant-booking-premium'
);

/**
 * Add secure check filter
 */
$wprb_update_checker->addQueryArgFilter( 'wprb_secure_update_check' );

/**
 * Add Premium Key to update data
 *
 * @param  array $query_args update data.
 * @return array
 */
function wprb_secure_update_check( $query_args ) {

	$key = base64_encode( get_option( 'wprb-premium-key' ) );

	if ( $key ) {

		$query_args['premium-key'] = $key;

	}

	return $query_args;

}

