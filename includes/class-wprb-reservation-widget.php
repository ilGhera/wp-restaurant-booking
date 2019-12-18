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

		$this->power_on           = get_option( 'wprb-power-on' );

		add_action( 'wp_enqueue_scripts', array( $this, 'wprb_scripts' ) );
		add_action( 'wp_head', array( $this, 'booking_button' ) );
		add_action( 'wp_footer', array( $this, 'booking_modal' ) );

		add_action( 'wp_ajax_wprb-hours-available', array( $this, 'hours_select_element_callback' ) );
		add_action( 'wp_ajax_nopriv_wprb-hours-available', array( $this, 'hours_select_element_callback' ) );

		add_action( 'wp_ajax_wprb-check-for-external-seats', array( $this, 'external_seats_element_callback' ) );
		add_action( 'wp_ajax_nopriv_wprb-check-for-external-seats', array( $this, 'external_seats_element_callback' ) );

		add_action( 'wp_ajax_wprb-get-max-bookable', array( $this, 'get_max_bookable' ) );
		add_action( 'wp_ajax_nopriv_wprb-get-max-bookable', array( $this, 'get_max_bookable' ) );

		add_action( 'wp_ajax_wprb-reservation', array( $this, 'wprb_save_reservation' ) );
		add_action( 'wp_ajax_nopriv_wprb-reservation', array( $this, 'wprb_save_reservation' ) );

		add_shortcode( 'booking-button', array( $this, 'booking_button_shortcode' ) );

	}


	/**
	 * Scripts and style
	 */
	public function wprb_scripts() {

		/*css*/
		wp_enqueue_style( 'wprb-style', WPRB_URI . 'css/wprb.css' );
		wp_enqueue_style( 'modal-style', WPRB_URI . 'css/jquery.modal.min.css' );
		wp_enqueue_style( 'font-awesome', WPRB_URI . 'css/fontawesome/all.min.css' );
		wp_enqueue_style( 'datepicker-css', WPRB_URI . 'js/air-datepicker/dist/css/datepicker.min.css' );

		/*js*/
		wp_enqueue_script( 'modal-js', WPRB_URI . 'js/jquery.modal.min.js', array( 'jquery' ), '0.9.1', true );
		wp_enqueue_script( 'wprb-js', WPRB_URI . 'js/wprb.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script( 'datepicker-js', WPRB_URI . 'js/air-datepicker/dist/js/datepicker.min.js', array( 'jquery' ), '2.2.3', true );
		wp_enqueue_script( 'datepicker-eng', WPRB_URI . 'js/air-datepicker/dist/js/i18n/datepicker.en.js', array( 'jquery' ), '2.2.3', true );
		wp_enqueue_script( 'datepicker-it', WPRB_URI . 'js/air-datepicker/dist/js/i18n/datepicker.it.js', array( 'jquery' ), '2.2.3', true );

		$change_date_nonce      = wp_create_nonce( 'wprb-change-date' );
		$external_nonce         = wp_create_nonce( 'wprb-external' );
		$max_bookable_nonce     = wp_create_nonce( 'wprb-max-bookable' );
		$save_reservation_nonce = wp_create_nonce( 'wprb-save-reservation' );
		$date_first_message     = esc_html__( 'Please select a date first', 'wp-restaurant-booking' );
		$locale                 = str_replace( '_', '-', get_locale() );

		/*Pass data to the script file*/
		wp_localize_script(
			'wprb-js',
			'wprbSettings',
			array(
				'ajaxURL'              => admin_url( 'admin-ajax.php' ),
				'changeDateNonce'      => $change_date_nonce,
				'externalNonce'        => $external_nonce,
				'maxBookableNonce'     => $max_bookable_nonce,
				'saveReservationNonce' => $save_reservation_nonce,
				'dateFirstMessage'     => $date_first_message,
				'locale'               => $locale,
			)
		);

	}


	/**
	 * Check if external seats option is activated
	 */
	public static function are_externals_active() {

		$output = get_option( 'wprb-activate-external-seats' );

		return $output;

	}


	/**
	 * The boking button
	 *
	 * @param bool $shortcode true when called by the shordcode response function.
	 */
	public function booking_button( $shortcode = false ) {

		if ( $this->power_on ) {

			/*Button position*/
			$position  = get_option( 'wprb-button-position' );
			$class     = 'custom' !== $position ? ' top ' . $position : '';

			/*Change position if admin-bar is visible*/
			$admin_bar = is_admin_bar_showing();
			$class     = $admin_bar ? $class . ' admin-bar' : $class;

			if ( $class || $shortcode ) {

				echo '<div class="wprb-booking-button' . esc_attr( $class ) . '">';

					echo '<a href="#wprb-booking-modal" rel="modal:open">' . esc_html( wp_unslash( __( 'Book now', 'wp-restaurant-booking' ) ) ) . '</a>';

				echo '</div>';

			}

		}

	}


	/**
	 * The booking button shortcode
	 *
	 * @return mixed the button
	 */
	public function booking_button_shortcode() {

		ob_start();

		$this->booking_button( true );

		$output = ob_get_clean();

		return $output;

	}

	/**
	 * Get the max number of people bookable in the specified day of the week
	 *
	 * @return mixed the people select options of the widget
	 */
	public function get_max_bookable() {

		if ( isset( $_POST['date'], $_POST['wprb-max-bookable-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wprb-max-bookable-nonce'] ), 'wprb-max-bookable' ) ) {

			$date = $_POST['date'] ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';

			if ( $date ) {

				$the_date = strtolower( date( 'D', strtotime( $date ) ) );
				$bookable = get_option( 'wprb-bookable' );

				$max = isset( $bookable[ $the_date ]['max'] ) ? $bookable[ $the_date ]['max'] : 12;

				if ( $max ) {

					echo '<option value="" style="display: none;">+</option>';

					for ( $i = 4; $i <= $max; $i++ ) {
						echo '<option value="' . esc_attr( $i ) . '">' . esc_html( $i ) . '</option>';
					}

				}

			}

		}

		exit;

	}


	/**
	 * First step reservation
	 *
	 * Includes number of people and calendar
	 */
	public function step_1() {

		/*Date*/
		echo '<div class="booking-step booking-date active">';

			echo '<p class="wprb-step-description">' . esc_html__( 'Select the date', 'wp-restaurant-booking' ) . '</p>';

			echo '<div class="datepicker-here" data-language="it" data-inline="true"></div>';

		echo '</div>';

		/*People*/
		echo '<div class="booking-step booking-people active">';

			echo '<div class="people-container">';

				echo '<div class="booking-label left-20">' . esc_html( wp_unslash( __( 'People', 'wp-restaurant-booking' ) ) ) . '</div>';

				echo '<ul class="booking-people_numbers">';
					echo '<li class="booking-people_numbers__number"><input type="button" value="1"></li>';
					echo '<li class="booking-people_numbers__number"><input type="button" value="2"></li>';
					echo '<li class="booking-people_numbers__number"><input type="button" value="3"></li>';
					echo '<li class="booking-people_numbers__number">';
						echo '<select disabled="disabled">';
							echo '<option value="" style="display: none;">+</option>';
						echo '</select>';
					echo '</li>';
				echo '</ul>';
				echo '<div class="clear"></div>';

			echo '</div>';

		echo '</div>';

	}


	/**
	 * Print the widget description for last minute
	 */
	public static function last_minute_text() {

		$default     = __( 'Last minutes are tables available only for a limited time', 'wp-restaurant-booking' );
		$description = get_option( 'wprb-last-minute-description' ) ? get_option( 'wprb-last-minute-description' ) : $default;

		if ( $description ) {

			echo '<p class="last-minute-text">';

				echo esc_html( $description );

			echo '</p>';

		}

	}


	/**
	 * Display the attribute title of the single hour element
	 *
	 * @param int  $internals     the internal seats available.
	 * @param int  $externals     the external seats availbrle.
	 * @param bool $not_available hour not available.
	 * @param bool $back_end      different if used in back-end.
	 * @return string
	 */
	public static function get_hour_title( $internals, $externals, $not_available, $back_end ) {

		$title              = null;
		$display_availables = get_option( 'wprb-display-number-availables' );

		if ( $not_available ) {

			$title = __( 'Not available', 'wp-restaurant-booking' );

		} else {

			if ( $display_availables || $back_end ) {

				if ( ! $externals ) {

					/* Translators: number of available seats */
					$title = sprintf( __( 'Available seats: %d', 'wp-restaurant-booking' ), $internals + $externals );

				} else {

					$title  = __( "AVAILABLE SEATS\n", 'wp-restaurant-booking' );

					/* Translators: number of internals available */
					$title .= sprintf( __( "Indor: %d\n", 'wp-restaurant-booking' ), $internals );

					/* Translators: number of externals available */
					$title .= sprintf( __( 'Outdor: %d', 'wp-restaurant-booking' ), $externals );

				}

			}

		}

		return $title;

	}


	/**
	 * Display the hours available in the specific day
	 *
	 * @param int    $people      number of people of the reservation.
	 * @param srting $date        the reservation date.
	 * @param bool   $back_end    different if used in back-end.
	 * @param bool   $last_minute define is the current reservation (back-end) is a last minute.
	 * @param bool   $external    define is the current reservation (back-end) is external.
	 * @param string $time        the current booking time if editing an existing reservation.
	 */
	public static function hours_select_element( $people = 0, $date = null, $back_end = false, $last_minute = false, $external = false, $time = null ) {

		/*Hours*/
		$hours_available     = WPRB_Reservations::get_available_hours( $date, $time, $people );
		$externals_available = WPRB_Reservations::get_available_hours( $date, $time, $people, true, $external );

		if ( is_array( $hours_available ) ) {

			if ( false === $back_end ) {

				echo '<p class="wprb-step-description">' . esc_html__( 'Select the time', 'wp-restaurant-booking' ) . '</p>';

			}

			echo '<ul>';

				/*The current hour*/
				$now = current_time( 'timestamp' );

				/*The margin time set by the admin*/
				$margin_time = get_option( 'wprb-margin-time' ) ? get_option( 'wprb-margin-time' ) : 60;

				foreach ( $hours_available as $key => $value ) {

					$is_available = true;

					/*Check if it's too late*/
					if ( $date ) {

						if ( ( strtotime( $date . ' ' . $key ) - ( 60 * $margin_time ) ) < $now ) {

							$is_available = false;

						}

					}

					/*Define internal and external available seats*/
					$ext = self::are_externals_active() && isset( $externals_available[ $key ] ) ? $externals_available[ $key ] : 0;
					$int = max( $value - $ext, 0 );

					/*Check if it's available for this number of people*/
					if ( $people > $ext && $people > $int ) {

						$is_available = false;

					}

					$not_available = false === $is_available ? ' not-available' : '';
					$title         = self::get_hour_title( $int, $ext, $not_available, $back_end );

					/*Define data for li element*/
					$data = 'data-internal=' . $int;

					if ( self::are_externals_active() ) {

						$data .= ' data-external=' . $ext;

					}

					echo '<li class="wprb-hour regular' . esc_attr( $not_available ) . '" title="' . esc_attr( $title ) . '" ' . esc_attr( $data ) . '><input type="button" value="' . esc_attr( $key ) . '"></li>';

				}

			echo '</ul>';

		}

		/*External seats*/
		self::external_seats_element();

		/*Last minute available*/
		$last_minute_av = WPRB_Reservations::get_available_last_minute( $date, $people, $back_end, $last_minute, $time );

		if ( is_array( $last_minute_av ) && ! empty( $last_minute_av ) ) {

			$description = $back_end ? __( 'Last minute', 'wp-restaurant-booking' ) : __( 'Last minute available', 'wp-restaurant-booking' );

			echo '<p class="wprb-step-description last-minute">' . esc_html( $description ) . '</p>';

			echo '<ul class="last-minute">';

				foreach ( $last_minute_av as $last ) {

					/* Translators: %s: the time until the table booked will be available */
					$title = sprintf( __( 'Available until %s', 'wp-restaurant-booking' ), $last['to'] );

					echo '<li class="wprb-hour' . esc_attr( $not_available ) . '" title="' . esc_attr( $title ) . '"><input type="button" class="last-minute" data-until="' . esc_attr( $last['to'] ) . '" value="' . esc_attr( $last['from'] ) . '"></li>';

				}

			echo '</ul>';

			if ( false === $back_end ) {

				self::last_minute_text();

			}

		}

	}


	/**
	 * Get the hours element after date selection by the user
	 */
	public function hours_select_element_callback() {

		if ( isset( $_POST['people'], $_POST['date'], $_POST['wprb-change-date-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wprb-change-date-nonce'] ), 'wprb-change-date' ) ) {

			$people      = sanitize_text_field( wp_unslash( $_POST['people'] ) );
			$date        = sanitize_text_field( wp_unslash( $_POST['date'] ) );
			$back_end    = isset( $_POST['back-end'] ) ? sanitize_text_field( wp_unslash( $_POST['back-end'] ) ) : false;
			$the_date    = date( 'Y-m-d', strtotime( $date ) );
			$last_minute = isset( $_POST['last-minute'] ) ? sanitize_text_field( wp_unslash( $_POST['last-minute'] ) ) : null;
			$time        = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : null;

			$this->hours_select_element( $people, $the_date, $back_end, $last_minute, $time );

		}

		exit;

	}


	/**
	 * Display the external seats option
	 */
	public static function external_seats_element() {

		echo '<div class="wprb-external-container choise">';
			echo '<p>' . esc_html__( 'Outdoor table available', 'wp-restaurant-booking' ) . '</p>';
			echo '<a class="yes">' . esc_html__( 'Yes', 'wp-restaurant-booking' ) . '</a>';
			echo '<a class="no">' . esc_html__( 'No', 'wp-restaurant-booking' ) . '</a>';
		echo '</div>';

		echo '<div class="wprb-external-container only">';
			echo '<p>' . esc_html__( 'Only available outdoor', 'wp-restaurant-booking' ) . '</p>';
			echo '<a class="yes only">' . esc_html__( 'Yes, no problem', 'wp-restaurant-booking' ) . '</a>';
		echo '</div>';

	}


	/**
	 * Get the external seats element after time selection by the user
	 */
	public function external_seats_element_callback() {

		if ( isset( $_POST['date'], $_POST['time'], $_POST['people'], $_POST['wprb-external-nonce'] ) && wp_verify_nonce( $_POST['wprb-external-nonce'], 'wprb-external' ) ) {

			$date        = sanitize_text_field( wp_unslash( $_POST['date'] ) );
			$the_date    = date( 'Y-m-d', strtotime( $date ) );
			$time        = sanitize_text_field( wp_unslash( $_POST['time'] ) );
			$people      = sanitize_text_field( wp_unslash( $_POST['people'] ) );
			$back_end    = isset( $_POST['back-end'] ) ? sanitize_text_field( wp_unslash( $_POST['back-end'] ) ) : '';
			$is_external = isset( $_POST['is_external'] ) ? sanitize_text_field( wp_unslash( $_POST['is_external'] ) ) : '';

			$bookable = WPRB_Reservations::get_available_externals_seats( $the_date, $time, $people, $back_end, $is_external );

			echo esc_html( $bookable );

			exit;

		}

	}


	/**
	 * Second step reservation
	 *
	 * Includes time
	 */
	public function step_2() {

		echo '<div class="booking-step booking-hours"></div>';

	}


	/**
	 * Third step reservation
	 *
	 * Includes the form with the customer informations
	 */
	public function step_3() {

		echo '<div class="booking-step booking-complete">';

			echo '<p class="wprb-step-description">' . esc_html__( 'Complete your reservation', 'wp-restaurant-booking' ) . '</p>';

			echo '<form id="wprb-reservation">';

				echo '<input type="text" name="first-name-field" required placeholder="' . esc_attr__( 'First name*', 'wp-restaurant-booking' ) . '" value="">';
				echo '<input type="text" name="last-name-field" required placeholder="' . esc_attr__( 'Last name*', 'wp-restaurant-booking' ) . '" value="">';
				echo '<input type="email" name="email-field" required placeholder="' . esc_attr__( 'Email*', 'wp-restaurant-booking' ) . '" value="">';
				echo '<input type="tel" name="phone-field" required placeholder="' . esc_attr__( 'Phone number*', 'wp-restaurant-booking' ) . '" value="">';

				echo '<textarea name="notes-field" class="notes-field" placeholder="' . esc_attr__( 'Add more details', 'wp-restaurant-booking' ) . '"></textarea>';

				echo '<input type="hidden" name="people-field" class="people-field" value="">';
				echo '<input type="hidden" name="date-field" class="date-field" value="">';
				echo '<input type="hidden" name="time-field" class="time-field" value="">';
				echo '<input type="hidden" name="external-field" class="external-field" value="">';
				echo '<input type="hidden" name="until-field" class="until-field" value="">';

				echo '<input type="submit" class="wprb-complete-reservation" value="' . esc_attr__( 'Book now', 'wp-restaurant-booking' ) . '">';

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

		echo '<div class="booking-end" data-title="' . esc_html__( 'Reservation completed', 'wp-restaurant-booking' ) . '">';

			echo '<p class="wprb-step-description">';
				echo '<i class="far fa-check-circle"></i><br>';

				/* Translators: %s: customer first name */
				printf( esc_html__( "Thanks %s!\n", 'wp-restaurant-booking' ), esc_html( $first_name ) );

				echo esc_html__( 'Your reservation has been received.', 'wp-restaurant-booking' );

			echo '</p>';

		echo '</div>';

	}


	/**
	 * The modal window
	 */
	public function booking_modal() {

		echo '<div id="wprb-booking-modal" class="wprb_modal">';

			echo '<h2 class="wprb-widget-title">' . esc_html( wp_unslash( __( 'Book now', 'wp-restaurant-booking' ) ) ) . '</h2>';

			/*Header*/
			echo '<div class="header-bar">';

				echo '<ul class="header-bar_steps">';

					/*Date*/
					echo '<li class="date active" data-step="booking-date">';
						echo '<span>';
							echo '<i class="far fa-calendar-alt"></i>';
						echo '</span><br>';
						echo '<div class="value">' . esc_html( wp_unslash( __( 'Date', 'wp-restaurant-booking' ) ) ) . '</div>';
					echo '</li>';

					/*People*/
					echo '<li class="people" data-step="booking-people">';
						echo '<span>';
							echo '<i class="fas fa-user-friends"></i>';
						echo '</span><br>';
						echo '<div class="value"></div>';
						echo esc_html( wp_unslash( __( 'People', 'wp-restaurant-booking' ) ) );
					echo '</li>';

					/*Time*/
					echo '<li class="time" data-step="booking-hours">';
						echo '<span>';
							echo '<i class="far fa-clock"></i>';
						echo '</span><br>';
						echo '<div class="value">' . esc_html( wp_unslash( __( 'Time', 'wp-restaurant-booking' ) ) ) . '</div>';
					echo '</li>';

					/*Complete*/
					echo '<li class="complete" data-step="booking-complete">';
						echo '<span>';
							echo '<i class="fas fa-check-circle"></i>';
						echo '</span><br>';
						echo '<div class="value">' . esc_html( wp_unslash( __( 'Complete', 'wp-restaurant-booking' ) ) ) . '</div>';
					echo '</li>';

					echo '<div class="clear"></div>';

				echo '</ul>';

			echo '</div>';

			echo '<div class="padding-2">';

				$this->step_1();
				$this->step_2();
				$this->step_3();

			echo '</div>';

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

			$first_name = isset( $values['first-name-field'] ) ? sanitize_text_field( wp_unslash( $values['first-name-field'] ) ) : '';
			$last_name  = isset( $values['last-name-field'] ) ? sanitize_text_field( wp_unslash( $values['last-name-field'] ) ) : '';
			$email      = isset( $values['email-field'] ) ? sanitize_text_field( wp_unslash( $values['email-field'] ) ) : '';
			$phone      = isset( $values['phone-field'] ) ? sanitize_text_field( wp_unslash( $values['phone-field'] ) ) : '';
			$people     = isset( $values['people-field'] ) ? sanitize_text_field( wp_unslash( $values['people-field'] ) ) : '';
			$date       = isset( $values['date-field'] ) ? sanitize_text_field( wp_unslash( $values['date-field'] ) ) : '';
			$time       = isset( $values['time-field'] ) ? sanitize_text_field( wp_unslash( $values['time-field'] ) ) : '';
			$notes      = isset( $values['notes-field'] ) ? sanitize_text_field( wp_unslash( $values['notes-field'] ) ) : '';
			$external   = isset( $values['external-field'] ) ? sanitize_text_field( wp_unslash( $values['external-field'] ) ) : '';
			$until      = isset( $values['until-field'] ) ? sanitize_text_field( wp_unslash( $values['until-field'] ) ) : '';

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

			/*Externals*/
			if ( $external ) {

				$args['meta_input']['wprb-external'] = $external;

			}

			/*Last minute*/
			if ( $until ) {

				$args['meta_input']['wprb-until'] = $until;

			}

			$post_id = wp_insert_post( $args );

			/*Add the default title*/
			WPRB_Reservations::default_reservation_title( $post_id, $first_name, $last_name );

			if ( is_int( $post_id ) ) {

				$sent = new WPRB_Notifications( $values );

				echo $this->step_4( $first_name, $email );

			}

		}

		exit;

	}

}
new WPRB_Reservation_Widget( true );
