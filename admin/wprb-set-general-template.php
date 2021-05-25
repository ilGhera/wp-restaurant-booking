<?php
/**
 * Reservation general settings
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 1.1.3
 */

$admin                     = new WPRB_Admin();
$power_on                  = get_option( 'wprb-power-on' );
$button_position           = get_option( 'wprb-button-position' );
$display_number_availables = get_option( 'wprb-display-number-availables' );
$margin_time               = get_option( 'wprb-margin-time' );
$medium_time               = get_option( 'wprb-medium-time' );
$expiration_time           = get_option( 'wprb-expiration-time' );
$policy_page               = get_option( 'wprb-policy-page' );
?>
<!-- Form -->
<form name="wprb-set-generals" class="wprb-set-generals wprb-form"  method="post" action="">

	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Activate', 'wp-restaurant-booking' ); ?></th>
			<td>
				<input type="checkbox" name="wprb-power-on" id="wprb-power-on" value="1"<?php echo ( 1 == $power_on ? ' checked="checked"' : 1 ); ?>>
				<p class="description"><?php esc_html_e( 'Display the booking button and receive reservations.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Button position', 'wp-restaurant-booking' ); ?></th>
			<td>
				<select name="wprb-button-position" id="wprb-button-position" class="wprb-select">
					<option value="left"<?php echo 'left' === $button_position ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Top left', 'wp-restaurant-booking' ); ?></option>
					<option value="right"<?php echo 'right' === $button_position ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Top right', 'wp-restaurant-booking' ); ?></option>
					<option value="custom"<?php echo 'custom' === $button_position ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Custom', 'wp-restaurant-booking' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'Booking button position. Custom position requires use of shortcode [booking-button].', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'Privacy policy', 'wp-restaurant-booking' ); ?></th>
            <td>
                <select name="wprb-policy-page" id="wprb-policy-page" class="wprb-select">
                    <option value="0"><?php esc_html_e( 'No page', 'wp-restaurant-booking' ); ?></option>
                    <?php
                    $args = array(
                        'post_type'      => 'page',
                        'post_status'    => 'publish',
                        'posts_per_page' => -1,
                    );

                    $pages = get_posts( $args );

                    if ( is_array( $pages ) ) {
                    
                        foreach ( $pages as $page ) {

                            echo '<option value="' . esc_attr( $page->ID ) . '"' . ( $page->ID === intval( $policy_page ) ? ' selected="selected"' : null ) . '>';
                                echo esc_html( $page->post_title );
                            echo '</option>';

                        }
                    }
                    ?>
                </select>
                <p class="description"><?php esc_html_e( 'Select the page to use for the privacy policy', 'wp-restaurant-booking' ) ?></p>
            </td>
        </tr>
		<tr class="wprb-display-number-availables-field">
			<th scope="row"><?php esc_html_e( 'Display number', 'wp-restaurant-booking' ); ?></th>
			<td>
				<input type="checkbox" name="wprb-display-number-availables" id="wprb-display-number-availables" value="1"<?php echo ( 1 == $display_number_availables ? ' checked="checked"' : '' ); ?>>
				<p class="description"><?php esc_html_e( 'Display the number of available seats to the user.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Margin time', 'wp-restaurant-booking' ); ?></th>
			<td>
				<input type="number" name="wprb-margin-time" step="5" min="10" max="1440" value="<?php echo esc_attr( $margin_time ); ?>" placeholder="Default 60">
				<p class="description"><?php esc_html_e( 'The time within which it is possible to book.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Medium time', 'wp-restaurant-booking' ); ?></th>
			<td>
				<input type="number" name="wprb-medium-time" step="5" min="10" max="180" value="<?php echo esc_attr( $medium_time ); ?>" placeholder="Default 60">
				<p class="description"><?php esc_html_e( 'An estimate about the minutes passed by the customer at the table.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Expiration time', 'wp-restaurant-booking' ); ?></th>
			<td>
				<input type="number" name="wprb-expiration-time" step="5" min="10" max="1440" value="<?php echo esc_attr( $expiration_time ); ?>" placeholder="Default 60">
				<p class="description"><?php esc_html_e( 'The delay time in minutes to consider a reservation as expired.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
	</table>

	<?php wp_nonce_field( 'wprb-set-generals', 'wprb-set-generals-nonce' ); ?>

	<input type="hidden" name="wprb-set-generals-sent" value="1">
	<input type="submit" class="button-primary wprb" value="<?php esc_html_e( 'Save settings', 'wp-restaurant-booking' ); ?>">

</form>
