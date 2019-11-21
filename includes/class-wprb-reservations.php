<?php
/**
 * The single reservation handle
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
			add_action( 'save_post', array( __CLASS__, 'save_single_reservation' ), 10, 1 );
			add_filter( 'manage_edit-reservation_columns', array( $this, 'edit_reservation_columns' ) );
			add_action( 'manage_reservation_posts_custom_column', array( $this, 'manage_reservation_columns' ), 10, 2 );
			add_filter( 'manage_edit-reservation_sortable_columns', array( $this, 'reservation_sortable_columns' ) );
			add_action( 'load-edit.php', array( $this, 'edit_reservations_load' ) );
			add_action( 'admin_footer', array( $this, 'status_modal' ) );
			add_action( 'wp_ajax_wprb-change-status', array( $this, 'wprb_change_status_callback' ) );
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


			/*Pass data to the script file*/
			wp_localize_script(
				'wprb-edit-js',
				'wprbSettings',
				array(
					'changeStatusNonce' => $change_status_nonce,
					'changeDateNonce'   => $change_date_nonce,
				)
			);

		}

	}


	/**
	 * Custom post type reservation
	 */
	public function register_post_type() {

		$labels = array(
			'name'               => __( 'Reservations', 'wprb' ),
			'singular_name'      => __( 'Reservation', 'wprb' ),
			'menu_name'          => __( 'Reservations', 'wprb' ),
			'name_admin_bar'     => __( 'Reservation', 'wprb' ),
			'add_new'            => __( 'New reservation', 'wprb' ),
			'add_new_item'       => __( 'New reservation', 'wprb' ),
			'new_item'           => __( 'New reservation', 'wprb' ),
			'edit_item'          => __( 'Edit reservation', 'wprb' ),
			'view_item'          => __( 'View reservation', 'wprb' ),
			'all_items'          => __( 'All reservations', 'wprb' ),
			'search_items'       => __( 'Search reservation', 'wprb' ),
			'parent_item_colon'  => __( 'Parent reservation:', 'wprb' ),
			'not_found'          => __( 'No reservations found.', 'wprb' ),
			'not_found_in_trash' => __( 'No reservations found in Trash.', 'wprb' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'wprb' ),
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
	 * Add meta box
	 *
	 * @param  string $post_type reservations.
	 */
	public function wprb_add_meta_box( $post_type ) {

		add_meta_box( 'wprb-box', __( 'Reservation details', 'wprb' ), array( $this, 'wprb_add_meta_box_callback' ), 'reservation' );

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
	 * @param  string $date the date.
	 * @return array hours as key and people as value
	 */
	public static function get_day_reservations( $date ) {

		$outtput = array();

		$args = array(
			'post_type' => 'reservation',
			'meta_query' => array(
				array(
					'key'     => 'wprb-date',
					'value'   => $date,
					'compare' => '=',
				),
			),
		);

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

			// error_log( 'DAY RESERVATIONS: ' . print_r( $output, true ) );

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

		// error_log( 'HOURS SET: ' . print_r( $output, true ) );
		return $output;

	}


	/**
	 * Get bookables set for the day specified
	 *
	 * @param  string $date the date.
	 * @return array hour as key and people as value
	 */
	public static function get_initial_bookables( $date ) {

		$hours        = self::get_hours_set();
		$values       = array();
		$output       = array();
		$get_bookable = get_option( 'wprb-bookable' );
		$day          = strtolower( date( 'D', strtotime( $date ) ) );

		$bookable = $get_bookable[ $day ]['bookable'];

		// error_log( 'BOOKABLE: ' . print_r( $bookable, true ) );
		// error_log( 'DATE: ' . strtolower( $day ) );
		if ( is_array( $hours ) ) {

			$count = count( $hours );

			for ( $i = 0; $i < $count; $i++ ) {

				$values[] = $bookable; // temp.

			}

			$output = array_combine( $hours, $values );

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

		// error_log( 'TIME: ' . print_r( $hours, true ) );
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

		// error_log( 'HOUR: ' . $hour );
		// error_log( 'THE INTERVAL: ' . $get_interval );
		/*Create the margin time*/
		$margin = new DateInterval( 'PT' . $medium_time .'M' );

		// error_log( 'HOUR: ' . print_r( $booked, true ) );

		/*Create the end time*/
		$end->add( $margin );
		// error_log( 'END: ' . print_r( $end, true ) );

		/*Invert*/
		$margin->invert = 1;

		/*Create the begin time*/
		$begin->add( $margin );
		// error_log( 'BEGIN: ' . print_r( $begin, true ) );

		/*Create the interval*/
		$interval = DateInterval::createFromDateString( $get_interval . ' min' ); // temp.

		/*Define the hours*/
		$times = new DatePeriod( $begin, $interval, $end, DatePeriod::EXCLUDE_START_DATE );

		foreach ( $times as $time ) {

			$output[] = $time->format( 'H:i' );

		}

		// error_log( 'TEMPORAL SPACE: ' . print_r( $output, true ) );

		return $output;

	}


	/**
	 * Get the bookable hours available based on the date provided
	 *
	 * @param  string $date the reservation date.
	 * @return array time as key and bookables as value
	 */
	public static function get_available_hours( $date = null ) {

		$bookables = self::get_initial_bookables( $date );

		// error_log( 'BOOKABLES: ' . print_r( $bookables, true ) );
		if ( $date ) {

			$day_reservations = self::get_day_reservations( $date );

			if ( $day_reservations ) {

				foreach ( $day_reservations as $key => $value ) {

					$temporal_space = self::get_temporal_space( $key );

					if ( is_array( $temporal_space ) ) {

						foreach ( $temporal_space as $time ) {

							if ( isset( $bookables[ $time ] ) ) {

								$bookables[ $time ] = $bookables[ $time ] - $value;

							}

						}

					}

				}

			}

		}

		// error_log( 'UPDATED BOOKABLES: ' . print_r( $bookables, true ) );
		return $bookables;

	}


	/**
	 * Save the single reservations
	 *
	 * @param  int $post_id the post id.
	 * @return void
	 */
	public static function save_single_reservation( $post_id ) {

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
			update_post_meta( $post_id, 'wprb-notes', $notes );
			update_post_meta( $post_id, 'wprb-status', $status );

			/*temp*/
			// self::get_available_hours( $date );
			if ( ! $post_title ) {

				self::default_reservation_title( $post_id, $first_name, $last_name );

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
			'day'    => __( 'Day', 'wprb' ),
			'time'   => __( 'Time', 'wprb' ),
			'people' => __( 'People', 'wprb' ),
			'table'  => __( 'Table', 'wprb' ),
			'status'  => __( 'Status', 'wprb' ),
			// 'date'   => __( 'Date' )
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

		$data_post_id = $post_id ? ' data-post-id="' . $post_id . '"' : '';
		$class_active = $active ? 'active ' : '';

		return '<a href="#wprb-status-modal" rel="modal:open" class="' . esc_attr( $class_active ) . 'wprb-status-label ' . esc_html( $status ) . '" ' . esc_attr( $data_post_id ) . ' data-status="' . esc_html( $status ) . '">' . ucfirst( esc_html__( $status, 'wprb' ) ) . '</a>';

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
				$time = get_post_meta( $post_id, 'wprb-time', true );

				echo esc_html( $time );

				break;

			case 'people':
				$people = get_post_meta( $post_id, 'wprb-people', true );

				echo esc_html( $people );

				break;

			case 'table':
				$table = get_post_meta( $post_id, 'wprb-table', true );

				echo $table ? esc_html( $table ) : esc_html__( 'No table assigned', 'wprb' );

				break;

			case 'status':
				$status = get_post_meta( $post_id, 'wprb-status', true );

				echo $this->get_status_label( $status, $post_id );

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

		$columns['day'] = 'day';

		return $columns;

	}


	/**
	 * Activate the filter only in the edit page in the admin
	 */
	public function edit_reservations_load() {

		add_filter( 'request', array( $this, 'my_sort_reservations' ) );

	}


	/**
	 * Sort reservations by day
	 *
	 * @param  array $vars the query.
	 * @return array       the query updated
	 */
	public function my_sort_reservations( $vars ) {

		/* Check if we're viewing the 'reservation' post type. */
		if ( isset( $vars['post_type'] ) && 'reservation' === $vars['post_type'] ) {

			/* Check if 'orderby' is set to 'duration'. */
			if ( isset( $vars['orderby'] ) && 'day' === $vars['orderby'] ) {

				/* Merge the query vars with our custom variables. */
				$vars = array_merge(
					$vars,
					array(
						'meta_key' => 'wprb-date',
						'orderby'  => 'meta_value',
					)
				);
			}
		}

		return $vars;

	}


	/**
	 * The modal window to change the reservation status
	 */
	public function status_modal() {

		$admin_page = get_current_screen();

		if ( 'edit-reservation' === $admin_page->id ) {

			$statuses = array( 'received', 'managed', 'completed', 'expired' );

			echo '<div id="wprb-status-modal" class="wprb_modal">';
				echo '<h3>' . esc_html__( 'Select the new reservation status', 'wprb' ) . '</h3>';
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

		if ( isset( $_POST['reservation-id'] ) && isset( $_POST['status'] ) && isset( $_POST['wprb-change-status-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wprb-change-status-nonce'] ), 'wprb-change-status' ) ) {

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
