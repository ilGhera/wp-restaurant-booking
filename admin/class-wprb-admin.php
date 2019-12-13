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
	 *
	 * @param bool $init if true execute the hooks.
	 */
	public function __construct( $init = false ) {

		if ( $init ) {

			add_action( 'admin_init', array( $this, 'save_reservations_settings' ) );
			add_action( 'admin_init', array( $this, 'save_last_minute_settings' ) );
			add_action( 'admin_init', array( $this, 'save_notifications_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'wprb_admin_scripts' ) );
			add_action( 'admin_menu', array( $this, 'register_wprb_admin' ) );
			add_action( 'wp_ajax_wprb-add-hours', array( $this, 'hours_element_callback' ) );
			add_action( 'wp_ajax_wprb-add-last-minute', array( $this, 'last_minute_element_callback' ) );

			add_action(
				'in_plugin_update_message-' . basename( WPRB_DIR ) . '/wp-restaurant-booking.php',
				array(
					$this,
					'wprb_update_message',
				),
				10,
				2
			);

		}

	}


	/**
	 * Back-end scripts and style
	 */
	public function wprb_admin_scripts() {

		/*css*/
		wp_enqueue_style( 'wprb-dashicons', WPRB_URI . 'css/wprb-dashicons.css' );

		$admin_page = get_current_screen();

		$pages = array( 'wprb_page_wprb-settings', 'edit-reservation', 'reservation' );

		if ( in_array( $admin_page->id, $pages ) ) {

			/*css*/
			wp_enqueue_style( 'chosen-style', WPRB_URI . '/vendor/harvesthq/chosen/chosen.min.css' );
			wp_enqueue_style( 'modal-style', WPRB_URI . 'css/jquery.modal.min.css' );

			/*js*/
			wp_enqueue_script( 'chosen', WPRB_URI . '/vendor/harvesthq/chosen/chosen.jquery.min.js' );
			wp_enqueue_script( 'modal-js', WPRB_URI . 'js/jquery.modal.min.js', array( 'jquery' ), '0.9.0', true );

			/*Nonce*/
			$add_hours_nonce       = wp_create_nonce( 'wprb-add-hours' );
			$add_last_minute_nonce = wp_create_nonce( 'wprb-add-last-minute' );

		}

		if ( 'wprb_page_wprb-settings' === $admin_page->id ) {

			/*css*/
			wp_enqueue_style( 'wprb-admin-style', WPRB_URI . 'css/wprb-admin.css' );
			wp_enqueue_style( 'tzcheckbox-style', WPRB_URI . 'js/tzCheckbox/jquery.tzCheckbox/jquery.tzCheckbox.css' );

			/*js*/
			wp_enqueue_script( 'wprb-admin-js', WPRB_URI . 'js/wprb-admin.js', array( 'jquery' ), '1.0', true );
			wp_enqueue_script( 'tzcheckbox', WPRB_URI . 'js/tzCheckbox/jquery.tzCheckbox/jquery.tzCheckbox.js', array( 'jquery' ) );

			/*Pass data to the script file*/
			wp_localize_script(
				'wprb-admin-js',
				'wprbSettings',
				array(
					'addHoursNonce'      => $add_hours_nonce,
					'addLastMinuteNonce' => $add_last_minute_nonce,
				)
			);

		}

		if ( 'plugins' === $admin_page->id ) {

			/*css*/
			wp_enqueue_style( 'wprb-plugins-style', WPRB_URI . 'css/wprb-plugins.css' );
		
		}

	}


	/**
	 * Add Reservations Manager user role
	 */
	public static function add_user_role() {

		$wp_roles = wp_roles();

		if ( $wp_roles && ! isset( $wp_roles->roles['wprb_manager'] ) ) {

			add_role(
				'wprb_manager',
				__( 'Reservations Manager', 'wprb' ),
				array(
					'moderate_comments'      => 1,
					'manage_categories'      => 1,
					'manage_links'           => 1,
					'upload_files'           => 1,
					'unfiltered_html'        => 1,
					'edit_posts'             => 1,
					'edit_others_posts'      => 1,
					'edit_published_posts'   => 1,
					'publish_posts'          => 1,
					'edit_pages'             => 1,
					'read'                   => 1,
					'level_7'                => 1,
					'level_6'                => 1,
					'level_5'                => 1,
					'level_4'                => 1,
					'level_3'                => 1,
					'level_2'                => 1,
					'level_1'                => 1,
					'level_0'                => 1,
					'edit_others_pages'      => 1,
					'edit_published_pages'   => 1,
					'publish_pages'          => 1,
					'delete_pages'           => 1,
					'delete_others_pages'    => 1,
					'delete_published_pages' => 1,
					'delete_posts'           => 1,
					'delete_others_posts'    => 1,
					'delete_published_posts' => 1,
					'delete_private_posts'   => 1,
					'edit_private_posts'     => 1,
					'read_private_posts'     => 1,
					'delete_private_pages'   => 1,
					'edit_private_pages'     => 1,
					'read_private_pages'     => 1,
					'wprb_edit_reservations' => 1,
				)
			);

			/*Add cap to admin*/
			$wp_roles->add_cap( 'administrator', 'wprb_edit_reservations' );

		}

	}


	/**
	 * Get the unread reservations
	 *
	 * @return int
	 */
	public function get_unread_reservations() {

		$args = array(
			'post_type'      => 'reservation',
			'posts_per_page' => 150,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => 'wprb-status',
					'value'   => 'received',
					'compare' => '=',
				),
			),
		);

		$reservations = get_posts( $args );

		return count( $reservations );

	}


	/**
	 * Add all plugin admin pages and menu items
	 */
	public function register_wprb_admin() {

		$unread_reservations = $this->get_unread_reservations();
		$bouble_count = '<span class="wprb update-plugins count-' . $unread_reservations . '" title="' . $unread_reservations . '""><span class="update-count">' . $unread_reservations . '</span></span>';

		$menu_label = sprintf( 'WPRB %s', $bouble_count );

		/*Main menu item*/
		$hook = add_menu_page( 'WP Restaurant Booking', $menu_label, 'wprb_edit_reservations', 'edit.php?post_type=reservation', null, 'dashicons-food', 59 );

		/*Reservations*/
		add_submenu_page( 'edit.php?post_type=reservation', __( 'All reservations', 'wprb' ), __( 'All reservations', 'wprb' ), 'wprb_edit_reservations', 'edit.php?post_type=reservation' );

		/*New Reservation*/
		add_submenu_page( 'edit.php?post_type=reservation', __( 'Add New', 'wprb' ), __( 'Add New', 'wprb' ), 'wprb_edit_reservations', 'post-new.php?post_type=reservation' );

		/*Options*/
		add_submenu_page( 'edit.php?post_type=reservation', __( 'Settings', 'wprb' ), __( 'Settings', 'wprb' ), 'wprb_edit_reservations', 'wprb-settings', array( $this, 'wprb_settings' ) );

	}


	/**
	 * The days of the week
	 *
	 * @return array
	 */
	public function week() {

		return array(
			'mon' => __( 'Monday', 'wprb' ),
			'tue' => __( 'Tuesday', 'wprb' ),
			'wed' => __( 'Wednesday', 'wprb' ),
			'thu' => __( 'Thursday', 'wprb' ),
			'fri' => __( 'Friday', 'wprb' ),
			'sat' => __( 'Saturday', 'wprb' ),
			'sun' => __( 'Sunday', 'wprb' ),
		);

	}


	/**
	 * Return a single hours element as ajax callback
	 */
	public function hours_element_callback() {

		if ( isset( $_POST['number'], $_POST['wprb-add-hours-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wprb-add-hours-nonce'] ), 'wprb-add-hours' ) ) {

			$number = sanitize_text_field( wp_unslash( $_POST['number'] ) );

			$this->hours_element( $number );

		}

		exit;

	}


	/**
	 * Display a single hours element
	 *
	 * @param int   $number the number of hours element.
	 * @param array $data single element data coming from the db.
	 */
	public function hours_element( $number = 1, $data = array() ) {

		$from  = isset( $data['from'] ) ? $data['from'] : '';
		$to    = isset( $data['to'] ) ? $data['to'] : '';
		$every = isset( $data['every'] ) ? $data['every'] : '';

		echo '<div class="wprb-hours-element-' . esc_attr( $number ) . ' hours-element">';

			echo '<label for="wprb-bookable-hours-from">' . esc_html__( 'From', 'wprb' ) . '</label>';
			echo '<input type="time" name="wprb-bookable-hours-from-' . esc_attr( $number ) . '" id="wprb-bookable-hours-from" class="wprb-bookable-hours-from" min="12:00" max="23:00" value="' . esc_attr( $from ) . '" required>';

			echo '<label for="wprb-bookable-hours-to">' . esc_html__( 'to', 'wprb' ) . '</label>';
			echo '<input type="time" name="wprb-bookable-hours-to-' . esc_attr( $number ) . '" id="wprb-bookable-hours-to" class="wprb-bookable-hours-to" min="12:00" max="23:00" value="' . esc_attr( $to ) . '" required>';

			echo '<label for="wprb-bookable-hours-every">' . esc_html__( 'every (minutes)', 'wprb' ) . '</label>';
			echo '<input type="number" name="wprb-bookable-hours-every-' . esc_attr( $number ) . '" id="wprb-bookable-hours-every" class="wprb-bookable-hours-every" min="5" max="60" step="5" value="' . esc_attr( $every ) . '" required>';

			if ( 1 === $number ) {

				echo '<div class="wprb-add-hours-container">';
					echo '<img class="add-hours" src="' . esc_url( WPRB_URI . 'images/add-icon.png' ) . '">';
					echo '<img class="add-hours-hover" src="' . esc_url( WPRB_URI . 'images/add-icon-hover.png' ) . '">';
				echo '</div>';

			} else {

				echo '<div class="wprb-remove-hours-container">';
					echo '<img class="remove-hours" src="' . esc_url( WPRB_URI . 'images/remove-icon.png' ) . '">';
					echo '<img class="remove-hours-hover" src="' . esc_url( WPRB_URI . 'images/remove-icon-hover.png' ) . '">';
				echo '</div>';

			}

		echo '</div>';

	}


	/**
	 * Display the hours element in the plugin admin area
	 */
	public function display_hours_elements() {

		$saved_hours = get_option( 'wprb-hours' );

		if ( $saved_hours ) {

			$count = count( $saved_hours );

			for ( $i = 1; $i <= $count; $i++ ) {

				$this->hours_element( $i, $saved_hours[ $i ] );

			}

		} else {

			$this->hours_element();

		}

	}


	/**
	 * Return a single last minute element as ajax callback
	 */
	public function last_minute_element_callback() {

		if ( isset( $_POST['number'], $_POST['wprb-add-last-minute-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wprb-add-last-minute-nonce'] ), 'wprb-add-last-minute' ) ) {

			$number = sanitize_text_field( wp_unslash( $_POST['number'] ) );

			$this->last_minute( $number );

		}

		exit;

	}


	/**
	 * Display a single last minute
	 *
	 * @param int   $number the number of hours element.
	 * @param array $data single element data coming from the db.
	 */
	public function last_minute( $number = 0, $data = array() ) {

		$date   = isset( $data['date'] ) ? $data['date'] : '';
		$from   = isset( $data['from'] ) ? $data['from'] : '';
		$to     = isset( $data['to'] ) ? $data['to'] : '';
		$people = isset( $data['people'] ) ? $data['people'] : '';

		echo '<div class="wprb-last-minute-element-' . esc_attr( $number ) . ' last-minute-element">';

			echo '<label for="wprb-last-minute-date">' . esc_html__( 'On', 'wprb' ) . '</label>';
			echo '<input type="date" name="wprb-last-minute-date-' . esc_attr( $number ) . '" id="wprb-last-minute-date" class="wprb-last-minute-date" min="' . esc_html( date( 'Y-m-d' ) ) . '" value="' . esc_attr( $date ) . '">';

			echo '<label for="wprb-last-minute-from">' . esc_html__( 'from', 'wprb' ) . '</label>';
			echo '<input type="time" name="wprb-last-minute-from-' . esc_attr( $number ) . '" id="wprb-last-minute-from" class="wprb-last-minute-from" min="12:00" max="23:00" value="' . esc_attr( $from ) . '" required>';

			echo '<label for="wprb-last-minute-to">' . esc_html__( 'to', 'wprb' ) . '</label>';
			echo '<input type="time" name="wprb-last-minute-to-' . esc_attr( $number ) . '" id="wprb-last-minute-to" class="wprb-last-minute-to" min="12:00" max="23:00" value="' . esc_attr( $to ) . '" required>';

			echo '<label for="wprb-last-minute-people">' . esc_html__( 'people', 'wprb' ) . '</label>';
			echo '<input type="number" name="wprb-last-minute-people-' . esc_attr( $number ) . '" id="wprb-last-minute-people" class="wprb-last-minute-people" step="1" value="' . esc_attr( $people ) . '" required>';

			if ( 0 === $number ) {

				echo '<div class="wprb-add-last-minute-container">';
					echo '<img class="add-last-minute" src="' . esc_url( WPRB_URI . 'images/add-icon.png' ) . '">';
					echo '<img class="add-last-minute-hover" src="' . esc_url( WPRB_URI . 'images/add-icon-hover.png' ) . '">';
				echo '</div>';

			} else {

				echo '<div class="wprb-remove-last-minute-container">';
					echo '<img class="remove-last-minute" src="' . esc_url( WPRB_URI . 'images/remove-icon.png' ) . '">';
					echo '<img class="remove-last-minute-hover" src="' . esc_url( WPRB_URI . 'images/remove-icon-hover.png' ) . '">';
				echo '</div>';

			}

		echo '</div>';

	}


	/**
	 * Auto-delete the last minute out of date
	 */
	public function get_filtered_last_minute() {

		$output = null;

		$last_minute = get_option( 'wprb-last-minute' );

		if ( $last_minute ) {

			$count = count( $last_minute );

			for ( $i = 0; $i < $count; $i++ ) {

				$date = isset( $last_minute[ $i ]['date'] ) ? $last_minute[ $i ]['date'] : '';
				$from = isset( $last_minute[ $i ]['from'] ) ? $last_minute[ $i ]['from'] : '';

				/*Check for expired elements*/
				if ( $date && $from ) {

					$time = strtotime( $date . ' ' . $from );
					$now  = strtotime( 'now' );

					if ( $now > $time ) {

						unset( $last_minute[ $i ] );

					}

				}

			}

			$output = array_values( $last_minute );

			update_option( 'wprb-last-minute', $output );

			return $output;

		}

	}


	/**
	 * Display the last minute element in the plugin admin area
	 */
	public function display_last_minute_elements() {

		$last_minute = $this->get_filtered_last_minute();

		if ( $last_minute ) {

			$count = count( $last_minute );

			for ( $i = 0; $i < $count; $i++ ) {

				$this->last_minute( $i, $last_minute[ $i ] );

			}

		} else {

			$this->last_minute();

		}

	}


	/**
	 * Save the serervation option in the db
	 */
	public function save_reservations_settings() {

		if ( isset( $_POST['wprb-set-reservations-sent'], $_POST['wprb-set-reservations-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wprb-set-reservations-nonce'] ), 'wprb-set-reservations' ) ) {

			/*Power on*/
			$power_on = isset( $_POST['wprb-power-on'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-power-on'] ) ) : '';
			update_option( 'wprb-power-on', $power_on );

			/*Button position*/
			$button_position = isset( $_POST['wprb-button-position'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-button-position'] ) ) : '';
			update_option( 'wprb-button-position', $button_position );

			/*Display availables option*/
			$display_number_availables = isset( $_POST['wprb-display-number-availables'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-display-number-availables'] ) ) : 0;
			update_option( 'wprb-display-number-availables', $display_number_availables );

			/*External seats option*/
			$external_seats = isset( $_POST['wprb-activate-external-seats'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-activate-external-seats'] ) ) : 0;
			update_option( 'wprb-activate-external-seats', $external_seats );

			/*Bookable seats*/
			$save_bookable = array();

			$days = array_keys( $this->week() );

			foreach ( $days as $day ) {

				$bookable = isset( $_POST[ 'wprb-bookable-seats-' . $day ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wprb-bookable-seats-' . $day ] ) ) : 0;

				$save_bookable[ $day ]['bookable'] = $bookable;

				/*External seats*/
				if ( $external_seats ) {

					$externals = isset( $_POST[ 'wprb-external-seats-' . $day ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wprb-external-seats-' . $day ] ) ) : 0;

					$save_bookable[ $day ]['externals'] = $externals;

				}

				/*Max number bookable*/
				$max_bookable = isset( $_POST[ 'wprb-max-bookable-' . $day ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wprb-max-bookable-' . $day ] ) ) : 0;

				$save_bookable[ $day ]['max'] = $max_bookable;

			}

			update_option( 'wprb-bookable', $save_bookable );

			/*Margin time*/
			$margin_time = isset( $_POST['wprb-margin-time'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-margin-time'] ) ) : '';
			update_option( 'wprb-margin-time', $margin_time );

			/*Medium time*/
			$medium_time = isset( $_POST['wprb-medium-time'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-medium-time'] ) ) : '';
			update_option( 'wprb-medium-time', $medium_time );

			/*Expiration time*/
			$expiration_time = isset( $_POST['wprb-expiration-time'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-expiration-time'] ) ) : '';
			update_option( 'wprb-expiration-time', $expiration_time );

			/*Hours*/
			$save_hours = array();

			/*20 is the current limit*/
			for ( $i = 1; $i <= 20; $i++ ) {

				$from = isset( $_POST[ 'wprb-bookable-hours-from-' . $i ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wprb-bookable-hours-from-' . $i ] ) ) : null;

				$to = isset( $_POST[ 'wprb-bookable-hours-to-' . $i ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wprb-bookable-hours-to-' . $i ] ) ) : null;

				$every = isset( $_POST[ 'wprb-bookable-hours-every-' . $i ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wprb-bookable-hours-every-' . $i ] ) ) : null;

				if ( $from && $to && $every ) {

					$save_hours[ $i ] = array(
						'from'  => $from,
						'to'    => $to,
						'every' => $every,
					);

				} else {

					break;

				}

			}

			update_option( 'wprb-hours', $save_hours );

		}

	}


	/**
	 * Save last minute
	 */
	public function save_last_minute_settings() {

		if ( isset( $_POST['wprb-set-last-minute-sent'], $_POST['wprb-set-last-minute-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wprb-set-last-minute-nonce'] ), 'wprb-set-last-minute' ) ) {

			/*Last minute activate*/
			$activate = isset( $_POST['wprb-activate-last-minute'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-activate-last-minute'] ) ) : 0;
			update_option( 'wprb-activate-last-minute', $activate );

			/*Last minute description*/
			$description = isset( $_POST['wprb-last-minute-description'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-last-minute-description'] ) ) : '';
			update_option( 'wprb-last-minute-description', $description );

			/*Set last minute*/
			$last_minute = array();

			/*20 is the current limit*/
			for ( $i = 0; $i <= 20; $i++ ) {

				$date = isset( $_POST[ 'wprb-last-minute-date-' . $i ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wprb-last-minute-date-' . $i ] ) ) : null;
				$from = isset( $_POST[ 'wprb-last-minute-from-' . $i ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wprb-last-minute-from-' . $i ] ) ) : null;
				$to = isset( $_POST[ 'wprb-last-minute-to-' . $i ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wprb-last-minute-to-' . $i ] ) ) : null;
				$people = isset( $_POST[ 'wprb-last-minute-people-' . $i ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wprb-last-minute-people-' . $i ] ) ) : null;
				if ( $date && $from && $to && $people ) {

					$last_minute[ $i ] = array(
						'date'   => $date,
						'from'   => $from,
						'to'     => $to,
						'people' => $people,
					);

				} else {

					break;

				}

			}

			update_option( 'wprb-last-minute', $last_minute );

		}

	}


	/**
	 * Save notifications
	 */
	public function save_notifications_settings() {

		if ( isset( $_POST['wprb-set-notifications-sent'], $_POST['wprb-set-notifications-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wprb-set-notifications-nonce'] ), 'wprb-set-notifications' ) ) {

			$admin_activate = isset( $_POST['wprb-activate-admin-notification'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-activate-admin-notification'] ) ) : '';
			update_option( 'wprb-activate-admin-notification', $admin_activate );

			$admin_recipients = isset( $_POST['wprb-admin-recipients'] ) && '' !== $_POST['wprb-admin-recipients'] ? explode( ' ', sanitize_text_field( wp_unslash( $_POST['wprb-admin-recipients'] ) ) ) : '';
			update_option( 'wprb-admin-recipients', $admin_recipients );

			$user_activate = isset( $_POST['wprb-activate-user-notification'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-activate-user-notification'] ) ) : '';
			update_option( 'wprb-activate-user-notification', $user_activate );

			$user_object   = isset( $_POST['wprb-user-notification-object'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-user-notification-object'] ) ) : '';
			update_option( 'wprb-user-notification-object', $user_object );

			$user_message  = isset( $_POST['wprb-user-notification-message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wprb-user-notification-message'] ) ) : '';
			update_option( 'wprb-user-notification-message', $user_message );

		}

	}


	/**
	 * Options page
	 */
	public function wprb_settings() {

		/*Right of access*/
		if ( ! current_user_can( 'wprb_edit_reservations' ) ) {
			wp_die( esc_html( __( 'It seems like you don\'t have permission to see this page', 'wprb' ) ) );
		}

		/*Page template start*/
		echo '<div class="wrap">';
			echo '<div class="wrap-left">';

				/*Header*/
				echo '<h1 class="wprb main">' . esc_html( __( 'WP Restaurant Booking - Premium', 'wprb' ) ) . '</h1>';

				/*Plugin premium key*/
				$key = sanitize_text_field( get_option( 'wprb-premium-key' ) );

				if ( isset( $_POST['wprb-premium-key'], $_POST['wprb-premium-key-nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wprb-premium-key-nonce'] ), 'wprb-premium-key' ) ) {

					$key = sanitize_text_field( wp_unslash( $_POST['wprb-premium-key'] ) );

					update_option( 'wprb-premium-key', $key );

				}

				/*Premium Key Form*/
				echo '<form id="wprb-premium-key" method="post" action="">';
					echo '<label>' . esc_html( __( 'Premium Key', 'wprb' ) ) . '</label>';
					echo '<input type="text" class="regular-text code" name="wprb-premium-key" id="wprb-premium-key" placeholder="' . esc_html( __( 'Add your Premium Key', 'wprb' ) ) . '" value="' . esc_attr( $key ) . '" />';
					echo '<p class="description">' . esc_html( __( 'Add your Premium Key and keep update your copy of WP Restaurant Booking - Premium.', 'wprb' ) ) . '</p>';
					wp_nonce_field( 'wprb-premium-key', 'wprb-premium-key-nonce' );
					echo '<input type="submit" class="button button-primary" value="' . esc_html( __( 'Save settings', 'wprb' ) ) . '" />';
				echo '</form>';

				/*Plugin options menu*/
				echo '<div class="icon32 icon32-woocommerce-settings" id="icon-woocommerce"><br></div>';
				echo '<h2 id="wprb-admin-menu" class="nav-tab-wrapper woo-nav-tab-wrapper">';
					echo '<a href="#" data-link="wprb-set-reservations" class="nav-tab nav-tab-active" onclick="return false;">' . esc_html( __( 'Reservations', 'wprb' ) ) . '</a>';
					echo '<a href="#" data-link="wprb-set-last-minute" class="nav-tab" onclick="return false;">' . esc_html( __( 'Last minute', 'wprb' ) ) . '</a>';
					echo '<a href="#" data-link="wprb-set-notifications" class="nav-tab" onclick="return false;">' . esc_html( __( 'Notifications', 'wprb' ) ) . '</a>';
				echo '</h2>';

				/*Set reservations*/
				echo '<div id="wprb-set-reservations" class="wprb-admin" style="display: block;">';

					include( WPRB_ADMIN . 'wprb-set-reservations-template.php' );

				echo '</div>';

				/*Set last minute*/
				echo '<div id="wprb-set-last-minute" class="wprb-admin">';

					include( WPRB_ADMIN . 'wprb-set-last-minute-template.php' );

				echo '</div>';

				/*Set notifications*/
				echo '<div id="wprb-set-notifications" class="wprb-admin">';

					include( WPRB_ADMIN . 'wprb-set-notifications-template.php' );

				echo '</div>';

			echo '</div>';
			echo '<div class="wrap-right"></div>';
		echo '</div>';

	}


	/**
	 * Plugin update message for the admin
	 *
	 * @param  array $plugin_data the plugin metadata.
	 * @param  array $response    an array of metadata about the available plugin update.
	 */
	public function wprb_update_message( $plugin_data, $response ) {

		$message = null;
		$key     = get_option( 'wprb-premium-key' );

		if ( ! $key ) {

			$message  = __( 'A <strong>Premium Key</strong> is required for keeping this plugin up to date. ', 'wprb' );

			/* Translators: the admin url */
			$message .= sprintf( __( 'Please, add yours in the <a href="%sadmin.php/?page=wprb-settings">options page</a> ', 'wprb' ), admin_url() );
			$message .= __( 'or click <a href="https://www.ilghera.com/product/wp-restaurant-booking-premium/" target="_blank">here</a> for prices and details.', 'wprb' );

		} else {

			$decoded_key = explode( '|', base64_decode( $key ) );
			$bought_date = isset( $decoded_key[1] ) ? date( 'd-m-Y', strtotime( $decoded_key[1] ) ) : '';
			$limit       = strtotime( $bought_date . ' + 365 day' );
			$now         = strtotime( 'today' );

			if ( $limit < $now ) {

				$message  = __( 'It seems like your <strong>Premium Key</strong> is expired. ', 'wprb' );
				$message .= __( 'Please, click <a href="https://www.ilghera.com/product/wp-restaurant-booking-premium/" target="_blank">here</a> for prices and details.', 'wprb' );

			} elseif ( ! isset( $decoded_key[2] ) || ( isset( $decoded_key[2] ) && 7302 !== $decoded_key[2] ) ) {

				$message  = __( 'It seems like your <strong>Premium Key</strong> is not valid. ', 'wprb' );
				$message .= __( 'Please, click <a href="https://www.ilghera.com/product/wp-restaurant-booking-premium/" target="_blank">here</a> for prices and details.', 'wprb' );

			}

		}

		$allowed_html = array(
			'a' => array(
				'href'   => [],
				'target' => [],
			),
			'br'     => [],
			'strong' => [],
		);

		echo ( $message ) ? '<br><span class="wprb-alert">' . wp_kses( $message, $allowed_html ) . '</span>' : '';

	}

}
new WPRB_Admin( true );
