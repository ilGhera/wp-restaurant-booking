<?php
/**
 * Admin class
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 0.9.0
 */
class WPRB_Admin {


	/**
	 * Class constructor
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'wprb_admin_scripts' ) );
		add_action( 'admin_menu', array( $this, 'wprb_add_menu' ) );

	}


	/**
	 * Back-end scripts and style
	 */
	public function wprb_admin_scripts() {
		
		/*css*/
		wp_enqueue_style( 'wprb-dashicons', WPRB_URI . 'css/wprb-dashicons.css' );

		$admin_page = get_current_screen();
		
		if ( 'toplevel_page_wp-restaurant-booking' === $admin_page->base ) {

			// ...

		}

	}
	

	/**
	 * Plugin menu item
	 */
	public function wprb_add_menu() {

		$wprb_page = add_menu_page( 'WP Restaurant Booking', 'WPRB Options', 'manage_options', 'wp-restaurant-booking', array( $this, 'wprb_options' ), 'dashicons-food', 59 );

		return $wprb_page;

	}


	/**
	 * Options page
	 */
	public function wprb_options() {

		/*Right of access*/
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html( __( 'It seems like you don\'t have permission to see this page', 'wprb' ) ) );
		}

		/*Page template start*/
		echo '<div class="wrap">';
			echo '<div class="wrap-left">';

			echo '</div>';
		echo '</div>';

	}

}
new WPRB_Admin();
