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

			/*css*/
			wp_enqueue_style( 'wprb-style', WPRB_URI . 'css/wp-restaurant-booking.css' );
			wp_enqueue_style( 'chosen-style', WPRB_URI . '/vendor/harvesthq/chosen/chosen.min.css' );
			wp_enqueue_style( 'tzcheckbox-style', WPRB_URI . 'js/tzCheckbox/jquery.tzCheckbox/jquery.tzCheckbox.css' );

			/*js*/
			wp_enqueue_script( 'wprb-js', WPRB_URI . 'js/wprb.js', array( 'jquery' ), '1.0', true );
			wp_enqueue_script( 'chosen', WPRB_URI . '/vendor/harvesthq/chosen/chosen.jquery.min.js' );
			wp_enqueue_script( 'tzcheckbox', WPRB_URI . 'js/tzCheckbox/jquery.tzCheckbox/jquery.tzCheckbox.js', array( 'jquery' ) );

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
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( __( 'It seems like you don\'t have permission to see this page', 'wprb' ) ) );
		}

		/*Page template start*/
		echo '<div class="wrap">';
			echo '<div class="wrap-left">';

				/*Header*/
				echo '<h1 class="wprb main">' . esc_html( __( 'WordPress Restaurant Booking - Premium', 'wprb' ) ) . '</h1>';

				/*Plugin premium key*/
				$key = sanitize_text_field( get_option( 'wprb-premium-key' ) );

				if ( isset( $_POST['wprb-premium-key'], $_POST['wprb-premium-key-nonce'] ) && wp_verify_nonce( $_POST['wprb-premium-key-nonce'], 'wprb-premium-key' ) ) {

					$key = sanitize_text_field( wp_unslash( $_POST['wprb-premium-key'] ) );

					update_option( 'wprb-premium-key', $key );

				}

				/*Premium Key Form*/
				echo '<form id="wprb-premium-key" method="post" action="">';
				echo '<label>' . esc_html( __( 'Premium Key', 'wprb' ) ) . '</label>';
				echo '<input type="text" class="regular-text code" name="wprb-premium-key" id="wprb-premium-key" placeholder="' . esc_html( __( 'Add your Premium Key', 'wprb' ) ) . '" value="' . esc_attr( $key ) . '" />';
				echo '<p class="description">' . esc_html( __( 'Add your Premium Key and keep update your copy of Woocommerce Exporter for Reviso - Premium.', 'wprb' ) ) . '</p>';
				wp_nonce_field( 'wprb-premium-key', 'wprb-premium-key-nonce' );
				echo '<input type="submit" class="button button-primary" value="' . esc_html( __( 'Save ', 'wprb' ) ) . '" />';
				echo '</form>';

				/*Plugin options menu*/
				echo '<div class="icon32 icon32-woocommerce-settings" id="icon-woocommerce"><br /></div>';
				echo '<h2 id="wprb-admin-menu" class="nav-tab-wrapper woo-nav-tab-wrapper">';
					echo '<a href="#" data-link="wprb-set-reservations" class="nav-tab nav-tab-active" onclick="return false;">' . esc_html( __( 'Reservations', 'wprb' ) ) . '</a>';
					echo '<a href="#" data-link="wprb-set-notifications" class="nav-tab" onclick="return false;">' . esc_html( __( 'Notifications', 'wprb' ) ) . '</a>';
				echo '</h2>';

				/*Set reservations*/
				echo '<div id="wprb-set-reservations" class="wprb-admin" style="display: block;">';

					include( WPRB_ADMIN . 'wprb-set-reservations-template.php' );

				echo '</div>';

				/*Set notifications*/
				echo '<div id="wprb-set-notifications" class="wprb-admin">';

					include( WPRB_ADMIN . 'wprb-set-notifications-template.php' );

				echo '</div>';

			echo '</div>';
			echo '<div class="wrap-right"></div>';
		echo '</div>';

	}

}
new WPRB_Admin();
