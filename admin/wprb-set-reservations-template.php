<?php
/**
 * Reservation settings
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 0.9.0
 */

$admin           = new WPRB_Admin();
$power_on        = get_option( 'wprb-power-on' );
$external_seats  = get_option( 'wprb-activate-external-seats' );
$bookable        = get_option( 'wprb-bookable' );
$hours           = get_option( 'wprb-hours' );
$margin_time     = get_option( 'wprb-margin-time' );
$medium_time     = get_option( 'wprb-medium-time' );
$expiration_time = get_option( 'wprb-expiration-time' );
?>
<!-- Form -->
<form name="wprb-set-reservations" class="wprb-set-reservations wprb-form"  method="post" action="">

	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Activate', 'wprb' ); ?></th>
			<td>
				<input type="checkbox" name="wprb-power-on" id="wprb-activate-external-seats" value="1"<?php echo ( 1 == $power_on ? ' checked="checked"' : 1 ); ?>>
				<p class="description"><?php esc_html_e( 'Display the booking button and receive reservations.', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr class="wprb-activate-external-seats-field">
			<th scope="row"><?php esc_html_e( 'External seats', 'wprb' ); ?></th>
			<td>
				<input type="checkbox" name="wprb-activate-external-seats" id="wprb-activate-external-seats" value="1"<?php echo ( 1 == $external_seats ? ' checked="checked"' : '' ); ?>>
				<p class="description"><?php esc_html_e( 'Activate if external seats are available in your restaurant in this season.', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Bookable seats', 'wprb' ); ?></th>
			<td>
				<?php
				foreach ( $admin->week() as $key => $value ) {
					echo '<div class="wprb-set-reservations-day">';

						/*The cols name*/
						if ( 'mon' === $key ) {
							echo '<div class="wprb-col"><h4>' . esc_html__( 'Internal seats', 'wprb' ) . '</h4></div>';
							echo '<div class="wprb-col external"><h4>' . esc_html__( 'External seats', 'wprb' ) . '</h4></div>';
							echo '<div class="clear"></div>';
						}

						/*The day*/
						echo '<lable for="wprb-bookable-seats">' . esc_html( $value ) . '</lable><br>';

						echo '<div class="wprb-col">';

							/*Single data from the db*/
							$bookable_value = isset( $bookable[ $key ]['bookable'] ) ? $bookable[ $key ]['bookable'] : null;

							echo '<input type="number" name="wprb-bookable-seats-' . esc_attr( $key ) . '" id="wprb-bookable-seats" class="' . esc_attr( $key ) . '" placeholder="0" value="' . esc_attr( $bookable_value ) . '">';
						echo '</div>';

						echo '<div class="wprb-col external">';

							/*Single data from the db*/
							$externals_value = isset( $bookable[ $key ]['externals'] ) ? $bookable[ $key ]['externals'] : null;

							echo '<input type="number" name="wprb-external-seats-' . esc_attr( $key ) . '" id="wprb-external-seats" class="' . esc_attr( $key ) . '" placeholder="0" value="' . esc_html( $externals_value ) . '">';
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
		<tr>
			<th scope="row"><?php esc_html_e( 'Margin time', 'wprb' ); ?></th>
			<td>
				<input type="number" name="wprb-margin-time" step="5" min="10" max="1440" value="<?php echo esc_attr( $margin_time ); ?>" placeholder="Default 60">
				<p class="description"><?php esc_html_e( 'The time within which it is possible to book.', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Medium time', 'wprb' ); ?></th>
			<td>
				<input type="number" name="wprb-medium-time" step="5" min="10" max="180" value="<?php echo esc_attr( $medium_time ); ?>" placeholder="Default 60">
				<p class="description"><?php esc_html_e( 'An estimate about the minutes passed by the customer at the table.', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Expiration time', 'wprb' ); ?></th>
			<td>
				<input type="number" name="wprb-expiration-time" step="5" min="10" max="1440" value="<?php echo esc_attr( $expiration_time ); ?>" placeholder="Default 60">
				<p class="description"><?php esc_html_e( 'The delay time in minutes to consider a reservation as expired.', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Daily hours', 'wprb' ); ?></th>
			<td>
				<?php
				$admin->display_hours_elements();
				?>
				<p class="description"><?php esc_html_e( 'Times available every day for reservations.', 'wprb' ); ?></p>
			</td>
		</tr>
	</table>

	<?php wp_nonce_field( 'wprb-set-reservations', 'wprb-set-reservations-nonce' ); ?>

	<input type="hidden" name="wprb-set-reservations-sent" value="1">
	<input type="submit" class="button-primary wprb" value="<?php esc_html_e( 'Save settings', 'wprb' ); ?>">

</form>
