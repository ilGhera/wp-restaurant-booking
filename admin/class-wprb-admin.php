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
	 * @param bool $init if true execute the hooks
	 */
	public function __construct( $init = false ) {

		if ( $init ) {

			add_action( 'admin_init', array( $this, 'save_reservations_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'wprb_admin_scripts' ) );
			add_action( 'admin_menu', array( $this, 'register_wprb_admin' ) );
			add_action( 'wp_ajax_wprb-add-hours', array( $this, 'hours_element_callback' ) );

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

		if ( in_array(  $admin_page->id, $pages ) ) {

			/*css*/
			wp_enqueue_style( 'chosen-style', WPRB_URI . '/vendor/harvesthq/chosen/chosen.min.css' );
			wp_enqueue_style( 'modal-style', WPRB_URI . 'css/jquery.modal.min.css' );

			/*js*/
			wp_enqueue_script( 'chosen', WPRB_URI . '/vendor/harvesthq/chosen/chosen.jquery.min.js' );
			wp_enqueue_script( 'modal-js', WPRB_URI . 'js/jquery.modal.min.js', array( 'jquery' ), '0.9.1', true );


			/*Nonce*/
			$add_hours_nonce = wp_create_nonce( 'wprb-add-hours' );

			/*Pass data to the script file*/
			wp_localize_script(
				'wprb-admin-js',
				'wprbSettings',
				array(
					'addHoursNonce' => $add_hours_nonce,
				)
			);

		}

		if ( 'wprb_page_wprb-settings' === $admin_page->id ) {

			/*css*/
			wp_enqueue_style( 'wprb-admin-style', WPRB_URI . 'css/wprb-admin.css' );
			wp_enqueue_style( 'tzcheckbox-style', WPRB_URI . 'js/tzCheckbox/jquery.tzCheckbox/jquery.tzCheckbox.css' );

			/*js*/
			wp_enqueue_script( 'wprb-admin-js', WPRB_URI . 'js/wprb-admin.js', array( 'jquery' ), '1.0', true );
			wp_enqueue_script( 'tzcheckbox', WPRB_URI . 'js/tzCheckbox/jquery.tzCheckbox/jquery.tzCheckbox.js', array( 'jquery' ) );

		}

	}


	/**
	 * Get the unread reservations
	 *
	 * @return int
	 */
	public function get_unread_reservations() {
		
		global $wpdb;
		
		$query = "
			SELECT * FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'wprb-status' AND meta_value = 'received'
		";
		
		$reservations = $wpdb->get_results( $query );

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
	    $hook = add_menu_page( 'WP Restaurant Booking', $menu_label, 'manage_options', 'edit.php?post_type=reservation', null, 'dashicons-food', 59 );
	    
	    /*Reservations*/
	    add_submenu_page( 'edit.php?post_type=reservation', __( 'All reservations', 'wprb' ), __( 'All reservations', 'wprb' ), 'manage_options', 'edit.php?post_type=reservation' );

	    /*New Reservation*/
	    add_submenu_page( 'edit.php?post_type=reservation', __( 'Add New', 'wprb' ), __( 'Add New', 'wprb' ), 'manage_options', 'post-new.php?post_type=reservation' );
	    
	    /*Options*/
	    add_submenu_page( 'edit.php?post_type=reservation', __( 'Settings', 'wprb' ), __( 'Settings', 'wprb' ), 'manage_options', 'wprb-settings', array( $this, 'wprb_settings' ) );

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

		if( isset( $_POST['number'], $_POST['wprb-add-hours-nonce'] ) && wp_verify_nonce( $_POST['wprb-add-hours-nonce'], 'wprb-add-hours' ) ) {

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

		echo '<div class="wprb-hours-element-' . esc_attr( wp_unslash( $number ) ) . ' hours-element">';
					
			echo '<label for="wprb-bookable-hours-from">' . esc_html( wp_unslash( __( 'From', 'wprb' ) ) ) . '</label>';
			echo '<input type="time" name="wprb-bookable-hours-from-' . esc_attr( wp_unslash( $number ) ) . '" id="wprb-bookable-hours-from" class="wprb-bookable-hours-from" min="12:00" max="23:00" value="' . $from . '" required>'; // temp.					
			
			echo '<label for="wprb-bookable-hours-to">' . esc_html( wp_unslash( __( 'to', 'wprb' ) ) ) . '</label>';
			echo '<input type="time" name="wprb-bookable-hours-to-' . esc_attr( wp_unslash( $number ) ) . '" id="wprb-bookable-hours-to" class="wprb-bookable-hours-to" min="12:00" max="23:00" value="' . $to . '" required>'; // temp.					
			
			echo '<label for="wprb-bookable-hours-every">' . esc_html( wp_unslash( __( 'every (minutes)', 'wprb' ) ) ) . '</label>';
			echo '<input type="number" name="wprb-bookable-hours-every-' . esc_attr( wp_unslash( $number ) ) . '" id="wprb-bookable-hours-every" class="wprb-bookable-hours-every" min="5" max="60" step="5" value="15" value="' . $every . '" required>'; // temp.

			if ( 1 === $number ) {
				
				echo '<div class="wprb-add-hours-container">';
					echo '<img class="add-hours" src="' . esc_url( wp_unslash( WPRB_URI . 'images/add-icon.png' ) ) . '">';
	    			echo '<img class="add-hours-hover" src="' . esc_url( wp_unslash( WPRB_URI . 'images/add-icon-hover.png' ) ) . '">';
				echo '</div>';

			} else {

				echo '<div class="wprb-remove-hours-container">';
					echo '<img class="remove-hours" src="' . esc_url( wp_unslash( WPRB_URI . 'images/remove-icon.png' ) ) . '">';
	    			echo '<img class="remove-hours-hover" src="' . esc_url( wp_unslash( WPRB_URI . 'images/remove-icon-hover.png' ) ) . '">';
				echo '</div>';

			}

		echo '</div>';

	}


	public function display_hours_elements() {

		$saved_hours = get_option( 'wprb-hours' );

		if ( $saved_hours ) {

			for ( $i=1; $i <= count( $saved_hours ) ; $i++ ) { 

				$this->hours_element( $i, $saved_hours[ $i ] );

			}

		} else {
			
			$this->hours_element();

		}

	}


	/**
	 * Save the serervation oprion in the db
	 */
	public function save_reservations_settings() {

		if ( isset( $_POST['wprb-set-reservations-sent'], $_POST['wprb-set-reservations-nonce'] ) && wp_verify_nonce( $_POST['wprb-set-reservations-nonce'], 'wprb-set-reservations' ) ) {

			/*External seats option*/
			$external_seats = isset( $_POST['wprb-activate-external-seats'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-activate-external-seats'] ) ) : 0;
			
			update_option( 'wprb-activate-external-seats', $external_seats );

			
			/*Bookable seats*/
			$save_bookable = array();

			$days = array_keys( $this->week() );

			foreach ( $days as $day ) {
				
				$bookable = isset( $_POST[ 'wprb-bookable-seats-' . $day ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wprb-bookable-seats-' . $day ] ) ) : 0;

				$save_bookable[ $day ]['bookable'] = $bookable;

				if ( $external_seats ) {

					$externals = isset( $_POST[ 'wprb-external-seats-' . $day ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wprb-external-seats-' . $day ] ) ) : 0;

					$save_bookable[ $day ]['externals'] = $externals;

				}

			}
			
			update_option( 'wprb-bookable', $save_bookable );

			/*Hours*/
			$save_hours = array();


			for ($i=1; $i <= 20; $i++) { // temp.

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
	 * Options page
	 */
	public function wprb_settings() {

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
				echo '<p class="description">' . esc_html( __( 'Add your Premium Key and keep update your copy of WP Restaurant Booking - Premium.', 'wprb' ) ) . '</p>';
				wp_nonce_field( 'wprb-premium-key', 'wprb-premium-key-nonce' );
				echo '<input type="submit" class="button button-primary" value="' . esc_html( __( 'Save settings', 'wprb' ) ) . '" />';
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
