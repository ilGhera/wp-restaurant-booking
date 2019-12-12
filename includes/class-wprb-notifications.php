<?php
/**
 * Notifications class
 *
 * @author ilGhera
 * @package wp-restaurant-booking/includes
 * @since 0.9.0
 */
class WPRB_Notifications {


	/**
	 * Class constructor
	 *
	 * @param array $values the reservations details.
	 */
	public function __construct( $values ) {

		$this->values     = $values;
		$this->first_name = isset( $values['first-name-field'] ) ? $values['first-name-field'] : '';
		$this->last_name  = isset( $values['last-name-field'] ) ? $values['last-name-field'] : '';
		$this->email      = isset( $values['email-field'] ) ? $values['email-field'] : '';
		$this->phone      = isset( $values['phone-field'] ) ? $values['phone-field'] : '';
		$this->people     = isset( $values['people-field'] ) ? $values['people-field'] : '';
		$this->date       = isset( $values['date-field'] ) ? $values['date-field'] : '';
		$this->time       = isset( $values['time-field'] ) ? $values['time-field'] : '';
		$this->notes      = isset( $values['notes-field'] ) ? $values['notes-field'] : '';
		$this->until      = isset( $values['until-field'] ) ? $values['until-field'] : '';

		$this->define_shortcodes();
		$this->send_admin_notification();
		$this->send_user_notification();

	}


	/**
	 * Shortcodes definition
	 */
	public function define_shortcodes() {

		add_shortcode( 'first-name', array( $this, 'render_shortcode' ) );
		add_shortcode( 'last-name', array( $this, 'render_shortcode' ) );
		add_shortcode( 'email', array( $this, 'render_shortcode' ) );
		add_shortcode( 'phone', array( $this, 'render_shortcode' ) );
		add_shortcode( 'people', array( $this, 'render_shortcode' ) );
		add_shortcode( 'date', array( $this, 'render_shortcode' ) );
		add_shortcode( 'time', array( $this, 'render_shortcode' ) );
		add_shortcode( 'notes', array( $this, 'render_shortcode' ) );
		add_shortcode( 'until', array( $this, 'render_shortcode' ) );

	}

	/**
	 * The default user email object
	 *
	 * @return string
	 */
	public static function default_user_object() {

		return __( 'Thanks for your reservation [first-name]', 'wprb' );

	}


	/**
	 * The default user email message
	 *
	 * @return string
	 */
	public static function default_user_message() {

		$output  = __( "Hi [first-name],\nhere are the details of your reservation:\n\n", 'wprb' );
		$output .= __( "Day: [date]\nTime: [time]\nPeople: [people]\n", 'wprb' );
		$output .= __( "Name: [first-name] [last-name]\nEmail: [email]\nPhone: [phone]\nNotes: [notes]\n\n", 'wprb' );
		$output .= __( 'We are waiting for you!', 'wprb' );

		return $output;

	}

	/**
	 * The default admin email message
	 *
	 * @return string
	 */
	public static function default_admin_message() {

		$output  = __( "Hi,\nhere are the details of the new reservation:\n\n", 'wprb' );
		$output .= __( "Day: [date]\nTime: [time]\nPeople: [people]\n", 'wprb' );
		$output .= __( "Name: [first-name] [last-name]\nEmail: [email]\nPhone: [phone]\nNotes: [notes]\n\n", 'wprb' );

		return $output;

	}


	/**
	 * Shortcodes function callback returning the reservation detail
	 *
	 * @param  array  $attributes the shortcode attributes (empty).
	 * @param  string $content    the shortcode content (null).
	 * @param  string $tag        the shortcode name.
	 * @return string             the reservation detail value
	 */
	public function render_shortcode( $attributes, $content, $tag ) {

		switch ( $tag ) {
			case 'first-name':
				return $this->first_name;
				break;
			case 'last-name':
				return $this->last_name;
				break;
			case 'email':
				return $this->email;
				break;
			case 'phone':
				return $this->phone;
				break;
			case 'people':
				return $this->people;
				break;
			case 'date':
				return $this->date;
				break;
			case 'time':
				return $this->time;
				break;
			case 'notes':
				return $this->notes;
				break;
			case 'until':
				return $this->until;
				break;
		}

	}


	/**
	 * Change from email address and name
	 */
	public function mail_from_filters() {

		/*Email addres to noreplay*/
		add_filter(
			'wp_mail_from',
			function( $email ) {
				$output = str_replace( 'wordpress', 'noreplay', $email );
				return $output;
			}
		);

		/*Email from to site name*/
		add_filter(
			'wp_mail_from_name',
			function( $name ) {
				return get_bloginfo( 'name' );
			}
		);
	}


	/**
	 * Send admin notification if activated
	 */
	public function send_admin_notification() {

		if ( get_option( 'wprb-activate-admin-notification' ) ) {

			$to = get_option( 'wprb-admin-recipients' ) ? get_option( 'wprb-admin-recipients' ) : get_option( 'admin_email' );

			/* Translators: 1: the user first name 2: the date */
			$subject = sprintf( __( 'New reservation from %1$s for %2$s', 'wprb' ), $this->first_name, $this->date );

			$message = do_shortcode( self::default_admin_message() );

			$this->mail_from_filters();

			$sent = wp_mail( $to, $subject, $message );

		}

	}


	/**
	 * Send user notification if activated
	 */
	public function send_user_notification() {

		if ( get_option( 'wprb-activate-user-notification' ) ) {

			$to = $this->email;
			$get_subject = get_option( 'wprb-user-notification-subject' ) ? get_option( 'wprb-user-notification-subject' ) : self::default_user_object();
			$subject     = do_shortcode( $get_subject );

			$get_content = get_option( 'wprb-user-notification-message' ) ? get_option( 'wprb-user-notification-message' ) : self::default_user_message();

			ob_start();
			// include( WPRB_INCLUDES . 'email/wprb-email-header-template.php' );.
			echo do_shortcode( str_replace( '\n', '<br>', $get_content ) );
			// include( WPRB_INCLUDES . 'email/wprb-email-content-template.php' );.
			// include( WPRB_INCLUDES . 'email/wprb-email-footer-template.php' );.
			$message  = ob_get_clean();
			// $headers = array('Content-Type: text/html; charset=UTF-8');
			$headers = null;

			$this->mail_from_filters();

			$sent = wp_mail( $to, $subject, $message, $headers );

		}

	}

}
