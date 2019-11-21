<?php
/**
 * The widget used by the user to make the reservation
 *
 * @author ilGhera
 * @package wp-restaurant-booking/includes
 * @since 0.9.0
 */
class WPRB_Reservation_Widget {


	/**
	 * Class constructor
	 *
	 * @param bool $init if true calls filters and actions.
	 */
	public function __construct( $init = false ) {

		add_action( 'wp_enqueue_scripts', array( $this, 'wprb_scripts' ) );
		add_action( 'wp_head', array( $this, 'booking_button' ) );
		add_action( 'wp_footer', array( $this, 'booking_modal' ) );

		add_action( 'wp_ajax_wprb-hours-available', array( $this, 'hours_select_element_callback' ) );
		add_action( 'wp_ajax_nopriv_wprb-hours-available', array( $this, 'hours_select_element_callback' ) );

		add_action( 'wp_ajax_wprb-reservation', array( $this, 'wprb_save_reservation' ) );
		add_action( 'wp_ajax_nopriv_wprb-reservation', array( $this, 'wprb_save_reservation' ) );

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

		/*js*/
		wp_enqueue_script( 'modal-js', WPRB_URI . 'js/jquery.modal.min.js', array( 'jquery' ), '0.9.1', true );
		wp_enqueue_script( 'wprb-js', WPRB_URI . 'js/wprb.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script( 'datepicker-js', WPRB_URI . 'js/air-datepicker/dist/js/datepicker.min.js', array( 'jquery' ), '2.2.3', true );
		wp_enqueue_script( 'datepicker-eng', WPRB_URI . 'js/air-datepicker/dist/js/i18n/datepicker.en.js', array( 'jquery' ), '2.2.3', true );
		wp_enqueue_script( 'datepicker-it', WPRB_URI . 'js/air-datepicker/dist/js/i18n/datepicker.it.js', array( 'jquery' ), '2.2.3', true );

		$change_date_nonce      = wp_create_nonce( 'wprb-change-date' );
		$save_reservation_nonce = wp_create_nonce( 'wprb-save-reservation' );

		/*Pass data to the script file*/
		wp_localize_script(
			'wprb-js',
			'wprbSettings',
			array(
				'ajaxURL'              => admin_url( 'admin-ajax.php' ),
				'changeDateNonce'      => $change_date_nonce,
				'saveReservationNonce' => $save_reservation_nonce,
			)
		);

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
	 * First step reservation
	 *
	 * Includes number of people and calendar
	 */
	public function step_1() {

		/*People*/
		echo '<div class="booking-step booking-people active">';

			echo '<div class="booking-label left-20">' . esc_html( wp_unslash( __( 'People', 'wprb' ) ) ) . '</div>';

			echo '<ul class="booking-people_numbers">';
				echo '<li class="booking-people_numbers__number"><input type="button" value="1"></li>';
				echo '<li class="booking-people_numbers__number"><input type="button" value="2"></li>';
				echo '<li class="booking-people_numbers__number"><input type="button" value="3"></li>';
				echo '<li class="booking-people_numbers__number">';
					echo '<select>';
						echo '<option value="" style="display: none;">+</option>';
						for ( $i = 4; $i < 13; $i++ ) {
							echo '<option value="' . esc_attr( $i ) . '">' . esc_html( $i ) . '</option>';
						}
					echo '</select>';
				echo '</li>';
			echo '</ul>';
			echo '<div class="clear"></div>';

		echo '</div>';

		/*Date*/
		echo '<div class="booking-step booking-date active">';

			echo '<div class="datepicker-here" data-language="it" data-inline="true"></div>';

		echo '</div>';

	}


	/**
	 * Display the hours available in the specific day
	 *
	 * @param int    $people number of people of the reservation.
	 * @param srting $date the reservation date.
	 */
	public static function hours_select_element( $people = 0, $date = null ) {

		$hours = WPRB_Reservations::get_available_hours( $date );

		if ( is_array( $hours ) ) {

			foreach ( $hours as $key => $value ) {

				$not_available = ( $people > $value || 0 === $value ) ? ' not-available' : '';
				$title         = $not_available ? __( 'Not available', 'mufwc' ) : $value;

				echo '<li class="wprb-hour' . esc_attr( $not_available ) . '" title="' . esc_attr( $title ) . '"><input type="button" value="' . esc_attr( $key ) . '"></li>';

			}

		}

	}


	/**
	 * Get the hours element after date selection by the admin
	 */
	public function hours_select_element_callback() {

		if ( isset( $_POST['people'], $_POST['date'], $_POST['wprb-change-date-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wprb-change-date-nonce'] ), 'wprb-change-date' ) ) {

			$people   = sanitize_text_field( wp_unslash( $_POST['people'] ) );
			$date     = sanitize_text_field( wp_unslash( $_POST['date'] ) );
			$the_date = date( 'Y-m-d', strtotime( $date ) );

			// error_log( 'TEST PEOPLE: ' . $people );
			// error_log( 'TEST DATA: ' . $the_date );
			$this->hours_select_element( $people, $the_date );

		}

		exit;

	}


	/**
	 * Second step reservation
	 *
	 * Includes time
	 */
	public function step_2() {

		echo '<div class="booking-step booking-hours">';

			echo '<p class="wprb-step-description">' . esc_html__( 'Select the time', 'wprb' ) . '</p>';

			echo '<ul></ul>';

		echo '</div>';

	}


	/**
	 * Third step reservation
	 *
	 * Includes the form with the customer informations
	 */
	public function step_3() {

		echo '<div class="booking-step booking-complete">';

			echo '<p class="wprb-step-description">' . esc_html__( 'Complete your reservation', 'wprb' ) . '</p>';

			echo '<form id="wprb-reservation">';

				echo '<input type="text" name="first-name-field" required placeholder="' . esc_attr__( 'First name*', 'wprb' ) . '" value="">';
				echo '<input type="text" name="last-name-field" required placeholder="' . esc_attr__( 'Last name*', 'wprb' ) . '" value="">';
				echo '<input type="email" name="email-field" required placeholder="' . esc_attr__( 'Email*', 'wprb' ) . '" value="">';
				echo '<input type="tel" name="phone-field" required placeholder="' . esc_attr__( 'Phone number*', 'wprb' ) . '" value="">';

				echo '<textarea name="notes-field" class="notes-field" placeholder="' . esc_attr__( 'Add more details', 'wprb' ) . '"></textarea>';

				echo '<input type="hidden" name="people-field" class="people-field" value="">';
				echo '<input type="hidden" name="date-field" class="date-field" value="">';
				echo '<input type="hidden" name="time-field" class="time-field" value="">';

				echo '<input type="submit" class="wprb-complete-reservation" value="' . esc_attr__( 'Book now', 'wprb' ) . '">';

			echo '</form>';

		echo '</div>';

	}


	/**
	 * Fourth step reservation
	 * The End
	 *
	 * @param string $first_name the customer first name.
	 * @param string $email      the customer email.
	 */
	public function step_4( $first_name, $email ) {

		echo '<div class="booking-end">';

			echo '<p class="wprb-step-description">';
				echo '<i class="far fa-check-circle"></i><br>';

				/* translators: %s: customer first name */
				printf( esc_html__( 'Thanks %s!', 'wprb' ), esc_html( $first_name ) ) . '<br>';

				echo esc_html__( 'We sent an e-mail with the confirmation data to', 'wprb' ) . '<br>';
				echo '<span class="wprb-confirmation-email">' . esc_html( $email ) . '</span>';
			echo '</p>';

		echo '</div>';

	}


	/**
	 * The modal window
	 */
	public function booking_modal() {

		echo '<div id="wprb-booking-modal" class="wprb_modal">';

			echo '<h2 class="wprb-widget-title">' . esc_html( wp_unslash( __( 'Book now', 'wprb' ) ) ) . '</h2>';

			/*Header*/
			echo '<div class="header-bar">';

				echo '<ul class="header-bar_steps">';

					/*People*/
					echo '<li class="people active" data-step="booking-people">';
						echo '<span>';
							echo '<i class="fas fa-user-friends"></i>';
						echo '</span><br>';
						echo '<div class="value"></div>';
						echo esc_html( wp_unslash( __( 'People', 'wprb' ) ) );
					echo '</li>';

					/*Date*/
					echo '<li class="date" data-step="booking-date">';
						echo '<span>';
							echo '<i class="far fa-calendar-alt"></i>';
						echo '</span><br>';
						echo '<div class="value">' . esc_html( wp_unslash( __( 'Date', 'wprb' ) ) ) . '</div>';
					echo '</li>';

					/*Time*/
					echo '<li class="time" data-step="booking-hours">';
						echo '<span>';
							echo '<i class="far fa-clock"></i>';
						echo '</span><br>';
						echo '<div class="value">' . esc_html( wp_unslash( __( 'Time', 'wprb' ) ) ) . '</div>';
					echo '</li>';

					/*Complete*/
					echo '<li class="complete" data-step="booking-complete">';
						echo '<span>';
							echo '<i class="fas fa-check-circle"></i>';
						echo '</span><br>';
						echo '<div class="value">' . esc_html( wp_unslash( __( 'Complete', 'wprb' ) ) ) . '</div>';
					echo '</li>';

					echo '<div class="clear"></div>';

				echo '</ul>';

			echo '</div>';

			echo '<div class="padding-2">';

				$this->step_1();
				$this->step_2();
				$this->step_3();

			echo '</div>';

		// echo '<a href="#" rel="modal:close">Close</a>';
		echo '</div>';

	}


	/**
	 * Returns the new field data
	 *
	 * @param  array $values the multy-dimensional array coming from js.
	 * @return array
	 */
	public function prepare_values( $values ) {

		$output = array();

		if ( is_array( $values ) && ! empty( $values ) ) {

			foreach ( $values as $value ) {

				$key   = sanitize_text_field( wp_unslash( $value['name'] ) );
				$value = sanitize_text_field( wp_unslash( $value['value'] ) );

				if ( ! in_array( $key, array( 'wprb-reservation-nonce', '_wp_http_referer' ) ) ) {

					$output[ $key ] = $value;

				}

			}

		}

		return $output;

	}


	/**
	 * Save the single reservation
	 *
	 * @return void
	 */
	public function wprb_save_reservation() {

		if ( isset( $_POST['values'], $_POST['wprb-save-reservation-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wprb-save-reservation-nonce'] ), 'wprb-save-reservation' ) ) {

			$values = $this->prepare_values( $_POST['values'] );
			// error_log( 'VALUES: ' . print_r( $values, true ) );

			$first_name = isset( $values['first-name-field'] ) ? sanitize_text_field( wp_unslash( $values['first-name-field'] ) ) : '';
			$last_name  = isset( $values['last-name-field'] ) ? sanitize_text_field( wp_unslash( $values['last-name-field'] ) ) : '';
			$email      = isset( $values['email-field'] ) ? sanitize_text_field( wp_unslash( $values['email-field'] ) ) : '';
			$phone      = isset( $values['phone-field'] ) ? sanitize_text_field( wp_unslash( $values['phone-field'] ) ) : '';
			$people     = isset( $values['people-field'] ) ? sanitize_text_field( wp_unslash( $values['people-field'] ) ) : '';
			$date       = isset( $values['date-field'] ) ? sanitize_text_field( wp_unslash( $values['date-field'] ) ) : '';
			$time       = isset( $values['time-field'] ) ? sanitize_text_field( wp_unslash( $values['time-field'] ) ) : '';
			$notes      = isset( $values['notes-field'] ) ? sanitize_text_field( wp_unslash( $values['notes-field'] ) ) : '';

			$the_date = date( 'Y-m-d', strtotime( $date ) );

			$args = array(
				'post_title'    => '',
				'post_content'  => '',
				'post_status'   => 'publish',
				'post_type'     => 'reservation',
				'meta_input'    => array(
					'wprb-first-name' => $first_name,
					'wprb-last-name'  => $last_name,
					'wprb-email'      => $email,
					'wprb-phone'      => $phone,
					'wprb-people'     => $people,
					'wprb-date'       => $the_date,
					'wprb-time'       => $time,
					'wprb-notes'      => $notes,
					'wprb-status'     => 'received',
				),
			);

			$post_id = wp_insert_post( $args );

			/*Add the default title*/
			WPRB_Reservations::default_reservation_title( $post_id, $first_name, $last_name );

			if ( is_int( $post_id ) ) {

				echo $this->step_4( $first_name, $email );

			}

		}

		exit;

	}

}
new WPRB_Reservation_Widget( true );
