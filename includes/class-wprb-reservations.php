<?php
/**
 * Reservation class
 *
 * @author ilGhera
 * @package wp-restaurant-booking/includes
 * @since 0.9.0
 */
class WPRB_Reservations {


	/**
	 * Class constructor
	 */
	public function __construct( $init = false ) {

		add_action( 'wp_enqueue_scripts', array( $this, 'wprb_scripts' ) );
		add_action( 'wp_head', array( $this, 'booking_button' ) );
		add_action( 'wp_footer', array( $this, 'booking_modal' ) );

	}


	/**
	 * Scripts and style
	 */
	public function wprb_scripts() {

		/*css*/
		wp_enqueue_style( 'wprb-style', WPRB_URI . 'css/wprb.css' );
		wp_enqueue_style( 'modal-style', WPRB_URI . 'css/jquery.modal.min.css' );
	    wp_enqueue_style( 'font-awesome', '//use.fontawesome.com/releases/v5.8.1/css/all.css' );
		wp_enqueue_style( 'datepicker-css', WPRB_URI . 'js/air-datepicker/dist/css/datepicker.min.css' );
		// wp_enqueue_style( 'font-awesome', WPRB_URI . 'css/all.min.css' );

		/*js*/
		wp_enqueue_script( 'modal-js', WPRB_URI . 'js/jquery.modal.min.js', array( 'jquery' ), '0.9.1', true );
		wp_enqueue_script( 'wprb-js', WPRB_URI . 'js/wprb.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script( 'datepicker-js', WPRB_URI . 'js/air-datepicker/dist/js/datepicker.min.js', array( 'jquery' ), '2.2.3', true );
		wp_enqueue_script( 'datepicker-eng', WPRB_URI . 'js/air-datepicker/dist/js/i18n/datepicker.en.js', array( 'jquery' ), '2.2.3', true );
		wp_enqueue_script( 'datepicker-it', WPRB_URI . 'js/air-datepicker/dist/js/i18n/datepicker.it.js', array( 'jquery' ), '2.2.3', true );
		// wp_enqueue_script( 'font-awsome-js', 'https://kit.fontawesome.com/cd62aa417e.js', '1.0', true );

	}
	

	/**
	 * The boking button
	 */
	public function booking_button() {

		echo '<div class="wprb-booking-button">';
			echo '<a href="#wprb-booking-modal" rel="modal:open">' . esc_html( wp_unslash( __( 'Book now', 'wprb' ) ) ) . '</a>';
		echo '</div>';
	
	}


	/**
	 * The modal window 
	 */
	public function booking_modal() {

		echo '<div id="wprb-booking-modal" class="wprb_modal">';
			
			echo '<h2>' . esc_html( wp_unslash( __( 'Booking now', 'wprb' ) ) ) . '</h2>';

			/*Header*/
			echo '<div class="header-bar">';

				echo '<ul class="booking-steps">';

				echo '<li><span class="active"><i class="fas fa-user-friends"></i></span><br>' . esc_html( wp_unslash( __( 'People', 'wprb' ) ) ) . '</li>';
				echo '<li><span><i class="far fa-calendar-alt"></i></span><br>' . esc_html( wp_unslash( __( 'Date', 'wprb' ) ) ) . '</li>';
				echo '<li><span><i class="far fa-clock"></i></span><br>' . esc_html( wp_unslash( __( 'Time', 'wprb' ) ) ) . '</li>';
				echo '<li><span><i class="fas fa-check-circle"></i></span><br>' . esc_html( wp_unslash( __( 'Complete', 'wprb' ) ) ) . '</li>';
				echo '<div class="clear"></div>';
				echo '</ul>';
			
			echo '</div>';

			echo '<div class="padding-2">';

				/*People*/
				echo '<div class="booking-people">';

					echo '<p class="booking-label">' . esc_html( wp_unslash( __( 'People', 'wprb' ) ) ) . '</p>';

					// echo '<input>';

				echo '</div>';

				/*Date*/
				echo '<div class="booking-date">';

					echo '<div class="datepicker-here" data-language="it" data-inline="true">';

				echo '</div>';


			echo '</div>';

	
			// echo '<a href="#" rel="modal:close">Close</a>';
		echo '</div>';

	}

}
new WPRB_Reservations( true );
