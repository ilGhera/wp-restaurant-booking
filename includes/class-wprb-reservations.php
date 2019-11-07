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
	 * @param bool $init if true execute the hooks
	 */
	public function __construct( $init = false ) {

		if ( $init ) {

			add_action( 'init', array( $this, 'register_post_type' ) );
			add_action( 'add_meta_boxes', array( $this, 'wprb_add_meta_box' ) );
			add_action( 'save_post', array( __CLASS__, 'save_single_reservation' ), 10, 1 );
			add_filter( 'manage_edit-reservation_columns', array( $this, 'edit_reservation_columns' ) ) ;
			add_action( 'manage_reservation_posts_custom_column', array( $this, 'manage_reservation_columns' ), 10, 2 );
			add_filter( 'manage_edit-reservation_sortable_columns', array( $this, 'reservation_sortable_columns' ) );
			add_action( 'load-edit.php', array( $this, 'edit_reservations_load' ) );


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
				'not_found_in_trash' => __( 'No reservations found in Trash.', 'wprb' )
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
				'supports'           => array( 'title' )
			);

			register_post_type( 'reservation', $args );

	}


	/**
	 * Add meta box
	 *
	 * @param  string $post_type reservations
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
	 * @param  int    $people     the number of people for the current reservation.
	 * @param  string $date       the booking gate.
	 * @return void
	 */
	public static function default_reservation_title( $post_id, $first_name, $last_name, $people, $date ) {

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
	 * Save the single reservations
	 *
	 * @param  int $post_id the post id.
	 * @return void
	 */
	public static function save_single_reservation( $post_id ) {

		if ( isset( $_POST['wprb-first-name'] ) || isset( $_POST['wprb-people'] ) ) {

			$post_title = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
			$first_name = isset( $_POST['wprb-first-name'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-first-name'] ) ) : '';
			$last_name  = isset( $_POST['wprb-last-name'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-last-name'] ) ) : '';
			$email      = isset( $_POST['wprb-email'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-email'] ) ) : '';
			$phone      = isset( $_POST['wprb-phone'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-phone'] ) ) : '';
			$people     = isset( $_POST['wprb-people'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-people'] ) ) : '';
			$date       = isset( $_POST['wprb-date'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-date'] ) ) : '';
			$time       = isset( $_POST['wprb-time'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-time'] ) ) : '';
			
			update_post_meta( $post_id, 'wprb-first-name', $first_name );
			update_post_meta( $post_id, 'wprb-last-name', $last_name );
			update_post_meta( $post_id, 'wprb-email', $email );
			update_post_meta( $post_id, 'wprb-phone', $phone );
			update_post_meta( $post_id, 'wprb-people', $people );
			update_post_meta( $post_id, 'wprb-date', $date );
			update_post_meta( $post_id, 'wprb-time', $time );

			if ( ! $post_title ) {

				self::default_reservation_title( $post_id, $first_name, $last_name, $people, $date );

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
			// 'date'   => __( 'Date' )
		);

		return $columns;

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
				
				echo $day;
				
				break;
		
			case 'time':
				
				$time = get_post_meta( $post_id, 'wprb-time', true );

				echo $time;
				
				break;
		
			case 'people':
				
				$people = get_post_meta( $post_id, 'wprb-people', true );
				
				echo $people;
				
				break;

			case 'table':

				$table = get_post_meta( $post_id, 'wprb-table', true );
			
				echo $table ? $table : __( 'No table assigned', 'wprb' );

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
						'orderby' => 'meta_value'
					)
				);
			}
		}

		return $vars;

	}

}
new WPRB_Reservations( true );
