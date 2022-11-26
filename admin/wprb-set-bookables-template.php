<?php
/**
 * Bookables settings
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 1.1.8
 */

$bookable       = get_option( 'wprb-bookable' );
?>
<!-- Form -->
<form name="wprb-set-bookables" class="wprb-set-bookables wprb-form"  method="post" action="">

	<table class="form-table">
		<tr class="wprb-activate-external-seats-field">
			<th scope="row"><?php esc_html_e( 'External seats', 'wp-restaurant-booking' ); ?></th>
			<td>
				<input type="checkbox" name="wprb-activate-external-seats" id="wprb-activate-external-seats" value="1">
				<p class="description"><?php esc_html_e( 'Activate if external seats are available in your restaurant in this season.', 'wp-restaurant-booking' ); ?></p>
				<?php $admin->go_premium(); ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Bookable seats', 'wp-restaurant-booking' ); ?></th>
			<td>
				<?php
				foreach ( $admin->week() as $key => $value ) {
					echo '<div class="wprb-set-reservations-day">';

						/*The cols name*/
						if ( 'mon' === $key ) {
							echo '<div class="wprb-col"><h4>' . esc_html__( 'Internal seats', 'wp-restaurant-booking' ) . '</h4></div>';
							echo '<div class="wprb-col external"><h4>' . esc_html__( 'External seats', 'wp-restaurant-booking' ) . '</h4></div>';
							echo '<div class="wprb-col max-bookable"><h4>' . esc_html__( 'Maximum bookable number', 'wp-restaurant-booking' ) . '</h4></div>';
							echo '<div class="clear"></div>';
						}

						/*The day*/
						echo '<lable for="wprb-bookable-seats">' . esc_html( ucfirst( $value ) ) . '</lable><br>';

						echo '<div class="wprb-col">';

							/*Single data from the db*/
							$bookable_value = isset( $bookable[ $key ]['bookable'] ) ? $bookable[ $key ]['bookable'] : 0;

							echo '<input type="number" name="wprb-bookable-seats-' . esc_attr( $key ) . '" id="wprb-bookable-seats" class="' . esc_attr( $key ) . '" placeholder="0" value="' . esc_attr( $bookable_value ) . '">';
						echo '</div>';

						echo '<div class="wprb-col external">';

							echo '<input type="number" name="wprb-external-seats-' . esc_attr( $key ) . '" id="wprb-external-seats" class="' . esc_attr( $key ) . '" placeholder="0" value="0" disabled>';

						echo '</div>';

						echo '<div class="wprb-col max-bookable">';

							/*Single data from the db*/
							$max_bookable = isset( $bookable[ $key ]['max'] ) ? $bookable[ $key ]['max'] : 0;

							echo '<input type="number" name="wprb-max-bookable-' . esc_attr( $key ) . '" id="wprb-max-bookable" class="' . esc_attr( $key ) . '" placeholder="0" value="' . esc_html( $max_bookable ) . '">';
						echo '</div>';

						echo '<div class="wprb-col">';
						echo '</div>';

						echo '<div class="wprb-col">';
						echo '</div>';

						echo '<div class="clear"></div>';

					echo '</div>';
				}
				?>
				<p class="description"><?php esc_html_e( 'Set seats that can be booked for each day of the week.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>

	</table>

	<?php wp_nonce_field( 'wprb-set-bookables', 'wprb-set-bookables-nonce' ); ?>

	<input type="hidden" name="wprb-set-bookables-sent" value="1">
	<input type="submit" class="button-primary wprb" value="<?php esc_html_e( 'Save settings', 'wp-restaurant-booking' ); ?>">

</form>
