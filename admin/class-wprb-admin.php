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
	public function __construct( $init = false ) {

		if ( $init ) {
			
			add_action( 'admin_enqueue_scripts', array( $this, 'wprb_admin_scripts' ) );
			add_action( 'admin_menu', array( $this, 'wprb_add_menu' ) );
			add_action( 'wp_ajax_wprb-add-hours', array( $this, 'hours_element' ) );

		}

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

			/*Nonce*/
			$add_hours_nonce = wp_create_nonce( 'wprb-add-hours' );

			/*Pass data to the script file*/
			wp_localize_script(
				'wprb-js',
				'wprbSettings',
				array(
					'addHoursNonce' => $add_hours_nonce,
				)
			);


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
	 * Return a single hours element as ajax callback
	 */
	public function hours_element_callback() {

		if( isset( $_POST['number'], $_POST['wprb-add-hours-nonce'] ) && wp_verify_nonce( $_POST['wprb-add-hours-nonce'], 'wprb-add-hours' ) ) {

			$number = sanitize_text_field( wp_unslash( $_POST['number'] ) );

			$this->hours_element( $number );

		}

		exit;

	}


	/**
	 * Display a single hours element
	 */
	public function hours_element( $number = 1 ) {

		echo '<div class="wprb-hours-element-1">';
					
			echo '<label for="wprb-bookable-hours-from-' . esc_attr( wp_unslash( $number ) ) . '">' . esc_html( wp_unslash( __( 'From', 'wprb' ) ) ) . '</label>';
			echo '<input type="time" name="wprb-bookable-hours-from-' . esc_attr( wp_unslash( $number ) ) . '" id="wprb-bookable-hours-from" class="wprb-bookable-hours-from" min="12:00" max="23:00">'; // temp.					
			
			echo '<label for="wprb-bookable-hours-to-' . esc_attr( wp_unslash( $number ) ) . '">' . esc_html( wp_unslash( __( 'to', 'wprb' ) ) ) . '</label>';
			echo '<input type="time" name="wprb-bookable-hours-to-' . esc_attr( wp_unslash( $number ) ) . '" id="wprb-bookable-hours-to" class="wprb-bookable-hours-to" min="12:00" max="23:00">'; // temp.					
			
			echo '<label for="wprb-bookable-hours-every-' . esc_attr( wp_unslash( $number ) ) . '">' . esc_html( wp_unslash( __( 'every (minutes)', 'wprb' ) ) ) . '</label>';
			echo '<input type="number" name="wprb-bookable-hours-every-' . esc_attr( wp_unslash( $number ) ) . '" id="wprb-bookable-hours-every" class="wprb-bookable-hours-every" min="5" max="60" step="5">'; // temp.					
			echo '<div class="wprb-add-hours-container">';
				echo '<img class="add-hours" src="' . esc_url( wp_unslash( WPRB_URI . 'images/add-icon.png' ) ) . '">';
    			echo '<img class="add-hours-hover" src="' . esc_url( wp_unslash( WPRB_URI . 'images/add-icon-hover.png' ) ) . '">';
			echo '</div>';
		echo '</div>';

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
new WPRB_Admin( true );
