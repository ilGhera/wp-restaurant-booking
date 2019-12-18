<?php
/**
 * Back-end reservations handle
 *
 * @author ilGhera
 * @package wp-restaurant-booking/includes
 * @since 0.9.0
 */
class WPRB_Reservations {

	/**
	 * Class constructor
	 *
	 * @param bool $init if true execute the hooks.
	 */
	public function __construct( $init = false ) {

		if ( $init ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'wprb_edit_scripts' ) );
			add_action( 'init', array( $this, 'register_post_type' ) );
			add_action( 'add_meta_boxes', array( $this, 'wprb_add_meta_box' ) );
			add_action( 'save_post', array( __CLASS__, 'save_single_reservation' ), 10, 3 );
			add_filter( 'manage_edit-reservation_columns', array( $this, 'edit_reservation_columns' ) );
			add_action( 'manage_reservation_posts_custom_column', array( $this, 'manage_reservation_columns' ), 10, 2 );
			add_filter( 'manage_edit-reservation_sortable_columns', array( $this, 'reservation_sortable_columns' ) );
			add_action( 'load-edit.php', array( $this, 'edit_reservations_load' ) );
			add_action( 'admin_footer', array( $this, 'status_modal' ) );
			add_action( 'wp_ajax_wprb-change-status', array( $this, 'wprb_change_status_callback' ) );
			add_action( 'restrict_manage_posts', array( $this, 'filter_reservations' ) );
			add_filter( 'enter_title_here', array( $this, 'title_place_holder' ), 20, 2 );
			add_filter( 'months_dropdown_results', array( $this, 'remove_post_date_filter' ), 10, 2 );
			add_filter( 'parse_query', array( $this, 'filtered_reservations' ) );

		}

	}


	/**
	 * Edit reservations scripts and style
	 */
	public function wprb_edit_scripts() {

		$admin_page = get_current_screen();

		$pages = array( 'edit-reservation', 'reservation' );

		if ( in_array( $admin_page->id, $pages ) ) {

			/*css*/
			wp_enqueue_style( 'wprb-admin-style', WPRB_URI . 'css/wprb-admin.css' );

			/*js*/
			wp_enqueue_script( 'wprb-edit-js', WPRB_URI . 'js/wprb-edit.js', array( 'jquery' ), '1.0', true );

			/*Nonce*/
			$change_status_nonce = wp_create_nonce( 'wprb-change-status' );
			$change_date_nonce   = wp_create_nonce( 'wprb-change-date' );
			$external_nonce      = wp_create_nonce( 'wprb-external' );

			/*Pass data to the script file*/
			wp_localize_script(
				'wprb-edit-js',
				'wprbSettings',
				array(
					'changeStatusNonce' => $change_status_nonce,
					'changeDateNonce'   => $change_date_nonce,
					'externalNonce'     => $external_nonce,
				)
			);

		}

	}


	/**
	 * Custom post type reservation
	 */
	public function register_post_type() {

		$labels = array(
			'name'               => __( 'Reservations', 'wp-restaurant-booking' ),
			'singular_name'      => __( 'Reservation', 'wp-restaurant-booking' ),
			'menu_name'          => __( 'Reservations', 'wp-restaurant-booking' ),
			'name_admin_bar'     => __( 'Reservation', 'wp-restaurant-booking' ),
			'add_new'            => __( 'New reservation', 'wp-restaurant-booking' ),
			'add_new_item'       => __( 'New reservation', 'wp-restaurant-booking' ),
			'new_item'           => __( 'New reservation', 'wp-restaurant-booking' ),
			'edit_item'          => __( 'Edit reservation', 'wp-restaurant-booking' ),
			'view_item'          => __( 'View reservation', 'wp-restaurant-booking' ),
			'all_items'          => __( 'All reservations', 'wp-restaurant-booking' ),
			'search_items'       => __( 'Search reservation', 'wp-restaurant-booking' ),
			'parent_item_colon'  => __( 'Parent reservation:', 'wp-restaurant-booking' ),
			'not_found'          => __( 'No reservations found.', 'wp-restaurant-booking' ),
			'not_found_in_trash' => __( 'No reservations found in Trash.', 'wp-restaurant-booking' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'wp-restaurant-booking' ),
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_icon'          => 'dashicons-food',
			'menu_position'      => 59,
			'supports'           => array( 'title' ),
		);

			register_post_type( 'reservation', $args );

	}


	/**
	 * Custom placeholder for reservation title
	 *
	 * @param  string $title the placeholder.
	 * @param  object $post  the post.
	 * @return string
	 */
	public function title_place_holder( $title, $post ) {

		if ( 'reservation' === $post->post_type ) {

			$title = __( 'Add the reservation title or leave blank to use the customer name', 'wp-restaurant-booking' );

		}

		return $title;

	}


	/**
	 * Add meta box
	 *
	 * @param  string $post_type reservations.
	 */
	public function wprb_add_meta_box( $post_type ) {

		add_meta_box( 'wprb-box', __( 'Reservation details', 'wp-restaurant-booking' ), array( $this, 'wprb_add_meta_box_callback' ), 'reservation' );

	}


	/**
	 * The meta box content for reservation
	 */
	public function wprb_add_meta_box_callback() {

		echo '<div class="wrap">';

			include( WPRB_INCLUDES . 'wprb-reservation-template.php' );

		echo '</div>';

	}


	/**
	 * Generate the reservation title if empty
	 *
	 * @param  int    $post_id    the post id.
	 * @param  string $first_name the customer first name.
	 * @param  string $last_name  the customer last name.
	 * @return void
	 */
	public static function default_reservation_title( $post_id, $first_name, $last_name ) {

		$post_title  = $first_name . ' ' . $last_name;

		$args = array(
			'ID'         => $post_id,
			'post_title' => $post_title,
		);

		remove_action( 'save_post', array( __CLASS__, 'save_single_reservation' ) );

		wp_update_post( $args );

		add_action( 'save_post', array( __CLASS__, 'save_single_reservation' ) );

	}


	/**
	 * Get all the reservations of the date specified
	 *
	 * @param  string $date     the date.
	 * @param  bool   $external get only external reservations.
	 * @return array hours as key and people as value
	 */
	public static function get_day_reservations( $date, $external = false ) {

		$output = array();

		$args = array(
			'post_type'      => 'reservation',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => array(
				'relation' => 'and',
				array(
					'key'     => 'wprb-date',
					'value'   => $date,
					'compare' => '=',
				),
				array(
					'relation' => 'or',
					array(
						'key'     => 'wprb-until',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'wprb-until',
						'value' => '',
						'compare' => '=',
					),
				),
				array(
					'key'     => 'wprb-status',
					'value' => 'expired',
					'compare' => '!=',
				),
			),
		);

		/*Get only external reservations*/
		if ( $external ) {

			$args['meta_query'][] = array(
				'key'     => 'wprb-external',
				'value'   => 1,
				'compare' => '=',
			);

		}

		$reservations = get_posts( $args );

		if ( $reservations ) {

			foreach ( $reservations as $res ) {

				$time   = get_post_meta( $res->ID, 'wprb-time', true );
				$people = get_post_meta( $res->ID, 'wprb-people', true );

				if ( isset( $output[ $time ] ) ) {

					$output[ $time ] += $people;

				} else {

					$output[ $time ] = $people;

				}

			}

			ksort( $output );

			return $output;

		}

	}


	/**
	 * Get the reservation hours set by the admin
	 *
	 * @param bool $every return the every value as key if true.
	 * @return array
	 */
	public static function get_hours_set( $every = false ) {

		$output = array();

		$hours = get_option( 'wprb-hours' );

		if ( is_array( $hours ) ) {

			foreach ( $hours as $hour ) {

				if ( isset( $hour['from'] ) && isset( $hour['to'] ) && isset( $hour['every'] ) ) {

					$begin = new DateTime( $hour['from'] );
					$end   = new DateTime( $hour['to'] );

					/*Modify the end to include it*/
					$end = $end->modify( '+1 min' );

					$interval = DateInterval::createFromDateString( $hour['every'] . ' min' );
					$times    = new DatePeriod( $begin, $interval, $end );

					foreach ( $times as $time ) {

						if ( $every ) {

							$output[ $time->format( 'H:i' ) ] = $hour['every'];

						} else {

							$output[] = $time->format( 'H:i' );

						}

					}

				}

			}

		}

		return $output;

	}


	/**
	 * Get bookables set for the day specified
	 *
	 * @param  string $date      the reservation date.
	 * @param  bool   $externals if yes get external seats bookable.
	 * @param  string $time      the reservation time.
	 * @return array hour as key and people as value
	 */
	public static function get_initial_bookables( $date, $externals = false, $time = null ) {

		$hours              = self::get_hours_set();
		$values             = array();
		$output             = array();
		$get_bookable       = get_option( 'wprb-bookable' );
		$day                = strtolower( date( 'D', strtotime( $date ) ) );
		$external_activated = get_option( 'wprb-activate-external-seats' );
		$bookable = ( $externals && $external_activated ) ? $get_bookable[ $day ]['externals'] : $get_bookable[ $day ]['bookable'];

		/*The total bookable must include externals if activated*/
		if ( $external_activated && ! $externals ) {

			$bookable += $get_bookable[ $day ]['externals'];

		}

		if ( is_array( $hours ) ) {

			$count = count( $hours );

			for ( $i = 0; $i < $count; $i++ ) {

				$values[] = $bookable; // temp.

			}

			$output = array_combine( $hours, $values );

		}

		if ( $externals && $time ) {

			return $output[ $time ];

		}

		return $output;

	}


	/**
	 * Get the interval time in a specific hour
	 *
	 * @param  string $time the hour interested.
	 * @return int
	 */
	public static function get_time_interval( $time ) {

		$hours = self::get_hours_set( true );

		if ( is_array( $hours ) && isset( $hours[ $time ] ) ) {

			return $hours[ $time ];

		}

	}


	/**
	 * The range of time influenced by the reservation
	 *
	 * @param  string $hour the reservation time.
	 * @return array the bookable hours interested.
	 */
	public static function get_temporal_space( $hour ) {

		$output = array();

		$medium_time  = get_option( 'wprb-medium-time' ) ? get_option( 'wprb-medium-time' ) : 60;
		$get_interval = self::get_time_interval( $hour );
		$booked       = new DateTime( $hour );
		$end          = new DateTime( $hour );
		$begin        = new DateTime( $hour );

		$margin = new DateInterval( 'PT' . $medium_time . 'M' );

		/*Create the end time*/
		$end->add( $margin );

		/*Invert*/
		$margin->invert = 1;

		/*Create the begin time*/
		$begin->add( $margin );

		if ( $get_interval ) {
			
			/*Create the interval*/
			$interval = DateInterval::createFromDateString( $get_interval . ' min' ); // temp.

			/*Define the hours*/
			$times = new DatePeriod( $begin, $interval, $end, DatePeriod::EXCLUDE_START_DATE );

			foreach ( $times as $time ) {

				$output[] = $time->format( 'H:i' );

			}
			
		}

		return $output;

	}


	/**
	 * Get the bookable hours available based on the date provided
	 *
	 * @param  string $date     the reservation date.
	 * @param  string $time     the reservation time.
	 * @param  int    $people   the reservation people.
	 * @param  boool  $external get only external available hours.
	 * @return array time as key and bookables as value
	 */
	public static function get_available_hours( $date = null, $time = null, $people = null, $external = false, $is_external = false ) {

		$bookables = self::get_initial_bookables( $date, $external );

		if ( $date ) {

			$day_reservations = self::get_day_reservations( $date, $external );

			if ( $day_reservations ) {

				foreach ( $day_reservations as $key => $value ) {

					/*Exclude current reservation if editing an existing one*/
					if ( $time && $people ) {

						if ( $key === $time && isset( $day_reservations[ $time ] ) ) {

							if ( ( $external && $is_external ) || ! $external ) {

								$value = $value - $people;

							}

						}

					}

					$temporal_space = self::get_temporal_space( $key );

					if ( is_array( $temporal_space ) ) {

						foreach ( $temporal_space as $time ) {

							if ( isset( $bookables[ $time ] ) ) {

								$bookables[ $time ] = max( $bookables[ $time ] - $value, 0 );

							}

						}

					}

				}

			}

		}

		return $bookables;

	}


	/**
	 * Get the last minute already used for a specific date
	 *
	 * @param  string $date the date.
	 * @return int the number of last minute reservations of the day
	 */
	public static function get_day_last_minute( $date ) {

		$output = array();

		$args = array(
			'post_type'      => 'reservation',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => array(
				'relation' => 'and',
				array(
					'key'     => 'wprb-date',
					'value'   => $date,
					'compare' => '=',
				),
				array(
					'key'     => 'wprb-until',
					'value'   => '',
					'compare' => '!=',
				),
			),
		);

		$reservations = get_posts( $args );

		if ( $reservations ) {

			foreach ( $reservations as $res ) {

				$people = get_post_meta( $res->ID, 'wprb-people', true );
				$time   = get_post_meta( $res->ID, 'wprb-time', true );

				if ( $people && $time ) {

					if ( isset( $output[ $time ] ) ) {

						$output[ $time ] += $people;

					} else {

						$output[ $time ] = $people;

					}

				}

			}

		}

		return $output;

	}


	/**
	 * Get the last minute per hour of the day specified
	 *
	 * @param string $date        the date.
	 * @param int    $people      the number of people.
	 * @param bool   $edit        true if called from edit reservation.
	 * @param bool   $last_minute define is the current reservation (back-end) is a last minute.
	 * @param string $time        the booking time on editing a reservation.
	 * @return array
	 */
	public static function get_available_last_minute( $date, $people, $edit = false, $last_minute = false, $time = null ) {

		if ( get_option( 'wprb-activate-last-minute' ) ) {

			$output          = array();
			$last_minute_el  = get_option( 'wprb-last-minute' );
			$day_last_minute = self::get_day_last_minute( $date );
			$day_bookables   = self::get_available_hours( $date, $time, $people );

			if ( $last_minute_el ) {

				foreach ( $last_minute_el as $element ) {

					/*Difference between set and already booked*/
					$available = 0;

					if ( isset( $element['people'], $element['from'], $day_last_minute[ $element['from'] ] ) ) {

						$available = $element['people'] - $day_last_minute[ $element['from'] ];

					} else {

						$available = $element['people'];

					}

					/*If in edit reservation exclude current reservation people*/
					if ( $edit && $last_minute ) {

						if ( $element['from'] === $time ) {

							$available += $people;

						}

					}

					/*If available >= people*/
					if ( isset( $element['date'] ) && $date === $element['date'] && $available >= $people ) {

						/*Only if regular booking is not available*/
						if ( isset( $day_bookables[ $element['from'] ] ) && $day_bookables[ $element['from'] ] < $people ) {

							$output[] = $element;

						}

					}

				}

			}

			return $output;
		}

	}


	/**
	 * Get the external seats per hour of the specified day
	 *
	 * @param string $date        the reservation date.
	 * @param string $time        the reservation time.
	 * @param int    $people      the number of people.
	 * @param bool   $edit        true if called from edit reservation.
	 * @param bool   $is_external define is the current reservation (back-end) is external.
	 * @return array
	 */
	public static function get_available_externals_seats( $date, $time, $people, $edit = false, $is_external = false ) {

		if ( get_option( 'wprb-activate-external-seats' ) ) {

			$externals     = self::get_initial_bookables( $date, true, $time );
			$day_bookables = self::get_available_hours( $date, $time, $people, true );
			$available     = isset( $day_bookables[ $time ] ) ? $day_bookables[ $time ] : 0;

			return $available;

		}

		return false;

	}


	/**
	 * Save the single reservations
	 *
	 * @param  int    $post_id the post id.
	 * @param  object $post    the post.
	 * @param  bool   $update  whether this is an existing post being updated or not.
	 * @return void
	 */
	public static function save_single_reservation( $post_id, $post, $update ) {

		if ( ( isset( $_POST['wprb-first-name'] ) || isset( $_POST['wprb-people'] ) && isset( $_POST['wprb-save-reservation-nonce'] ) ) && wp_verify_nonce( wp_unslash( $_POST['wprb-save-reservation-nonce'] ), 'wprb-save-reservation' ) ) {

			$post_title = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
			$first_name = isset( $_POST['wprb-first-name'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-first-name'] ) ) : '';
			$last_name  = isset( $_POST['wprb-last-name'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-last-name'] ) ) : '';
			$email      = isset( $_POST['wprb-email'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-email'] ) ) : '';
			$phone      = isset( $_POST['wprb-phone'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-phone'] ) ) : '';
			$people     = isset( $_POST['wprb-people'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-people'] ) ) : '';
			$table      = isset( $_POST['wprb-table'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-table'] ) ) : '';
			$date       = isset( $_POST['wprb-date'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-date'] ) ) : '';
			$time       = isset( $_POST['wprb-time'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-time'] ) ) : '';
			$until      = isset( $_POST['wprb-until'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-until'] ) ) : '';
			$external   = isset( $_POST['wprb-external'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-external'] ) ) : '';
			$notes      = isset( $_POST['wprb-notes'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-notes'] ) ) : '';
			$status     = isset( $_POST['wprb-status'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-status'] ) ) : '';

			update_post_meta( $post_id, 'wprb-first-name', $first_name );
			update_post_meta( $post_id, 'wprb-last-name', $last_name );
			update_post_meta( $post_id, 'wprb-email', $email );
			update_post_meta( $post_id, 'wprb-phone', $phone );
			update_post_meta( $post_id, 'wprb-people', $people );
			update_post_meta( $post_id, 'wprb-table', $table );
			update_post_meta( $post_id, 'wprb-date', $date );
			update_post_meta( $post_id, 'wprb-time', $time );
			update_post_meta( $post_id, 'wprb-until', $until );
			update_post_meta( $post_id, 'wprb-external', $external );
			update_post_meta( $post_id, 'wprb-notes', $notes );
			update_post_meta( $post_id, 'wprb-status', $status );

			$values = array(
				'first-name-field' => $first_name,
				'last-name-field'  => $last_name,
				'email-field'      => $email,
				'phone-field'      => $phone,
				'people-field'     => $people,
				'date-field'       => $date,
				'time-field'       => $time,
				'notes-field'      => $notes,
				'until-field'      => $until,
				'external-field'   => $external,
			);

			if ( ! $post_title ) {

				self::default_reservation_title( $post_id, $first_name, $last_name );

			}

			if ( isset( $post->post_date, $post->post_modified ) && $post->post_date === $post->post_modified ) {

				$sent = new WPRB_Notifications( $values );

			}

		}

	}


	/**
	 * Customize the reservation post table columns
	 *
	 * @param  array $columns the default WP table columns.
	 * @return array          the updated columns
	 */
	public function edit_reservation_columns( $columns ) {

		$columns = array(
			'cb'     => '&lt;input type="checkbox" />',
			'title'  => __( 'Title' ),
			'day'    => __( 'Day', 'wp-restaurant-booking' ),
			'time'   => __( 'Time', 'wp-restaurant-booking' ),
			'people' => __( 'People', 'wp-restaurant-booking' ),
			'table'  => __( 'Table', 'wp-restaurant-booking' ),
			'notes'  => __( 'Notes', 'wp-restaurant-booking' ),
			'status' => __( 'Status', 'wp-restaurant-booking' ),
		);

		return $columns;

	}


	/**
	 * Get the status label of the reservation
	 *
	 * @param  string $status the reservation status.
	 * @param  int    $post_id the id reservation.
	 * @param  bool   $active used in the modal window.
	 * @return mixed
	 */
	public function get_status_label( $status, $post_id = null, $active = false ) {

		$data_post_id = $post_id ? ' data-post-id=' . $post_id : '';
		$class_active = $active ? 'active ' : '';

		return '<a href="#wprb-status-modal" rel="modal:open" class="' . esc_attr( $class_active ) . 'wprb-status-label ' . esc_html( $status ) . '" ' . esc_attr( $data_post_id ) . ' data-status="' . esc_html( $status ) . '">' . esc_html__( ucfirst( $status ), 'wp-restaurant-booking' ) . '</a>';

	}


	/**
	 * Get the reservation status and change it if expired
	 *
	 * @param  int $post_id the reservation id.
	 */
	public function get_filtered_status( $post_id ) {

		/*Reservations data*/
		$get_date        = get_post_meta( $post_id, 'wprb-date', true );
		$get_time        = get_post_meta( $post_id, 'wprb-time', true );
		$expiration_time = get_option( 'wprb-expiration-time' ) ? get_option( 'wprb-expiration-time' ) : 60;
		$status          = get_post_meta( $post_id, 'wprb-status', true );

		$time_string      = $get_date . ' ' . $get_time;
		$reservation_time = date( 'Y-m-d H:i', strtotime( $time_string ) );
		$time_limit       = strtotime( $time_string ) + ( 60 * $expiration_time );
		$now              = strtotime( 'now' );

		if ( in_array( $status, array( 'received', 'managed' ) ) && $now >= $time_limit ) {

			$status = 'expired';
			update_post_meta( $post_id, 'wprb-status', $status );

		}

		echo $this->get_status_label( $status, $post_id );

	}


	/**
	 * Manage the content of the reservatiosn post table columns
	 *
	 * @param  string $column the column name.
	 * @param  int    $post_id the reservations id.
	 * @return mixed
	 */
	public function manage_reservation_columns( $column, $post_id ) {

		global $post;

		switch ( $column ) {

			case 'day':
				$day = get_post_meta( $post_id, 'wprb-date', true );

				echo esc_html( $day );

				break;

			case 'time':
				$time     = get_post_meta( $post_id, 'wprb-time', true );
				$until    = get_post_meta( $post_id, 'wprb-until', true );
				$external = get_post_meta( $post_id, 'wprb-external', true );

				/*Last minute*/
				if ( $until ) {

					echo '<span class="last-minute">';

						echo esc_html( $time ) . ' - ' . esc_html( $until );

					echo '</span>';

				} elseif ( $external ) {

					echo esc_html( $time ) . '<span class="external" title="' . esc_attr__( 'Outdor table', 'wp-restaurant-booking' ) . '">' . esc_html__( 'EXT', 'wp-restaurant-booking' ) . '</span>';

				} else {

					echo esc_html( $time );

				}

				break;

			case 'people':
				$people = get_post_meta( $post_id, 'wprb-people', true );

				echo esc_html( $people );

				break;

			case 'table':
				$table = get_post_meta( $post_id, 'wprb-table', true );

				echo $table ? esc_html( $table ) : esc_html__( 'No table assigned', 'wp-restaurant-booking' );

				break;

			case 'notes':
				$notes = get_post_meta( $post_id, 'wprb-notes', true );

				if ( $notes ) {

					echo '<img class="wprb-notes-icon" src="' . esc_url( WPRB_URI ) . 'images/notepad.png" title="' . esc_html( $notes ) . '">';

				}

				break;

			case 'status':
				echo $this->get_filtered_status( $post_id );

				break;

			default:
				break;

		}

	}


	/**
	 * Make the custom columns sortable
	 *
	 * @param  array $columns the table columns.
	 * @return array
	 */
	public function reservation_sortable_columns( $columns ) {

		$columns['day']    = 'day';
		$columns['time']   = 'time';
		$columns['people'] = 'people';
		$columns['notes']  = 'notes';
		$columns['status'] = 'status';

		return $columns;

	}


	/**
	 * Activate the filter only in the edit page in the admin
	 */
	public function edit_reservations_load() {

		add_filter( 'request', array( $this, 'sort_reservations' ) );

	}


	/**
	 * Sort reservations by day
	 *
	 * @param  array $vars the query.
	 * @return array       the query updated
	 */
	public function sort_reservations( $vars ) {

		/* Check if we're viewing the 'reservation' post type. */
		if ( isset( $vars['post_type'] ) && 'reservation' === $vars['post_type'] ) {

			if ( isset( $vars['orderby'] ) ) {

				$meta_key = null;

				switch ( $vars['orderby'] ) {
					case 'day':
						$meta_key = 'wprb-date';
						break;
					case 'time':
						$meta_key = 'wprb-time';
						break;
					case 'people':
						$meta_key = 'wprb-people';
						break;
					case 'notes':
						$meta_key = 'wprb-notes';
						break;
					case 'status':
						$meta_key = 'wprb-status';
						break;
				}

				if ( $meta_key ) {

					$order_by = 'wprb-people' === $meta_key ? 'meta_value_num' : 'meta_value';

					$vars = array_merge(
						$vars,
						array(
							'meta_key' => $meta_key,
							'orderby'  => $order_by,
						)
					);

				}

			}

		}

		return $vars;

	}


	/**
	 * Remove the post date filter from the reservation index
	 *
	 * @param array  $months    the publication months available.
	 * @param string $post_type the current post type.
	 * @return array empty
	 */
	public function remove_post_date_filter( $months, $post_type ) {

		if ( 'reservation' === $post_type ) {

			$months = array();

		}

		return $months;

	}


	/**
	 * Add calendar field to filter reservations
	 *
	 * @param  string $post_type the post type slug.
	 * @return mixed
	 */
	public function filter_reservations( $post_type ) {

		if ( 'reservation' !== $post_type ) {

			return;

		}

		$date = isset( $_REQUEST['wprb-date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wprb-date'] ) ) : '';

		echo '<input type="date" name="wprb-date" class="wprb-date list" min="' . esc_html( date( 'Y-m-d' ) ) . '" value="' . esc_attr( $date ) . '">';

	}


	/**
	 * Reservations filtered by date by the admin
	 *
	 * @param  array $query all the reservations.
	 * @return array the reservations of the day
	 */
	public function filtered_reservations( $query ) {

		if ( ! ( is_admin() && $query->is_main_query() ) ) {

			return $query;

		}

		if ( isset( $_REQUEST['wprb-date'] ) && '' === $_REQUEST['wprb-date'] ) {

			return $query;

		}

		if ( ! ( 'reservation' === $query->query['post_type'] && isset( $_REQUEST['wprb-date'] ) ) ) {

			return $query;

		}

		$query->query_vars['post_type']  = 'reservation';
		$query->query_vars['meta_query'] = array(
			array(
				'key'     => 'wprb-date',
				'value'   => sanitize_text_field( wp_unslash( $_REQUEST['wprb-date'] ) ),
				'compare' => '=',
			),

		);

		return $query;

	}


	/**
	 * The modal window to change the reservation status
	 */
	public function status_modal() {

		$admin_page = get_current_screen();

		if ( 'edit-reservation' === $admin_page->id ) {

			$statuses = array( 'received', 'managed', 'completed', 'expired' );

			echo '<div id="wprb-status-modal" class="wprb_modal">';
				echo '<h3>' . esc_html__( 'Select the new reservation status', 'wp-restaurant-booking' ) . '</h3>';
				echo '<ul>';

					foreach ( $statuses as $status ) {

						echo '<li data-status="' . esc_attr( $status ) . '">' . $this->get_status_label( $status ) . '</li>';

					}

				echo '</ul>';
			echo '</div>';

		}

	}


	/**
	 * Change the reservations status in the db
	 */
	public function wprb_change_status_callback() {

		if ( isset( $_POST['reservation-id'], $_POST['status'], $_POST['wprb-change-status-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wprb-change-status-nonce'] ), 'wprb-change-status' ) ) {

			$post_id = sanitize_text_field( wp_unslash( $_POST['reservation-id'] ) );
			$status  = sanitize_text_field( wp_unslash( $_POST['status'] ) );

			update_post_meta( $post_id, 'wprb-status', $status );

			/* The new status label */
			echo $this->get_status_label( $status, $post_id, true );

		}

		exit;

	}


}
new WPRB_Reservations( true );
