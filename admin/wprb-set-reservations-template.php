<?php
/**
 * Reservation settings
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 0.9.0
 */

$external_seats = get_option( 'wprb-activate-external-seats' );
if ( isset( $_POST['wprb-set-reservations-sent'], $_POST['wprb-set-reservations-nonce'] ) && wp_verify_nonce( $_POST['wprb-set-reservations-nonce'], 'wprb-set-reservations' ) ) {
	$external_seats = isset( $_POST['wprb-activate-external-seats'] ) ? sanitize_text_field( wp_unslash( $_POST['wprb-activate-external-seats'] ) ) : 0;
	update_option( 'wprb-activate-external-seats', $external_seats );
}
error_log( '$external_seats: ' . $external_seats );
?>
<!-- Form -->
<form name="wprb-set-reservations" class="wprb-set-reservations wprb-form"  method="post" action="">

	<table class="form-table">
		<tr class="wprb-activate-external-seats-field">
			<th scope="row"><?php esc_html_e( 'External seats', 'wprb' ); ?></th>
			<td>
				<input type="checkbox" name="wprb-activate-external-seats" id="wprb-activate-external-seats" value="1"<?php echo ( 1 == $external_seats ? ' checked="checked"' : '' ); ?>>
				<p class="description"><?php esc_html_e( 'Activate if external seats are available in your restaurant in this season.', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Reservations', 'wprb' ); ?></th>
			<td>
				<?php
				$week = array(
					'mon' => __( 'Monday', 'wprb' ),
					'tue' => __( 'Tuesday', 'wprb' ),
					'wed' => __( 'Wednesday', 'wprb' ),
					'thu' => __( 'Thursday', 'wprb' ),
					'fri' => __( 'Friday', 'wprb' ),
					'sat' => __( 'Saturday', 'wprb' ),
					'sun' => __( 'Sunday', 'wprb' ),
				);
				foreach ( $week as $key => $value ) {
					echo '<div class="wprb-set-reservations-day">';
						echo '<h4>' . esc_html( $value ) . '</h4>';

						echo '<div class="wprb-col">';
							echo '<lable for="wprb-bookable-seats">' . esc_html( 'Bookable seats', 'wprb' ) . '</lable><br>';
							echo '<input type="number" name="wprb-bookable-seats ' . esc_attr( $key ) . '" id="wprb-bookable-seats ' . esc_attr( $key ) . '" placeholder="0">';
						echo '</div>';

						echo '<div class="wprb-col external">';
							echo '<lable for="wprb-external-seats">' . esc_html( 'External seats', 'wprb' ) . '</lable><br>';
							echo '<input type="number" name="wprb-external-seats ' . esc_attr( $key ) . '" id="wprb-external-seats ' . esc_attr( $key ) . '" placeholder="0">';
						echo '</div>';
	
						echo '<div class="wprb-col">';
						echo '</div>';

						echo '<div class="wprb-col">';
						echo '</div>';

						echo '<div class="clear"></div>';

					echo '</div>';
				}
				?>
				<p class="description"><?php esc_html_e( 'Set seats that can be booked for each day of the week.', 'wprb' ); ?></p>
			</td>
		</tr>
	</table>

	<?php wp_nonce_field( 'wprb-set-reservations', 'wprb-set-reservations-nonce' ); ?>

	<input type="hidden" name="wprb-set-reservations-sent" value="1">
	<input type="submit" class="button-primary wprb" value="<?php esc_html_e( 'Save settings', 'wprb' ); ?>">

</form>
