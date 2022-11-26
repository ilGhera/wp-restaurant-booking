<?php
/**
 * Reservation hours settings
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 1.1.8
 */

?>
<!-- Form -->
<form name="wprb-set-hours" class="wprb-set-hours wprb-form"  method="post" action="">

	<table class="form-table">
		<?php
		if ( is_array( WPRB_Admin::week() ) ) {

			$n = 0;

			foreach ( WPRB_Admin::week() as $key => $value ) {
				$n++;
				$class = 7 !== $n ? ' class=one-of' : '';
				?>
				<tr<?php echo esc_html( $class ); ?>>
					<th scope="row"><?php echo esc_html( ucfirst( $value ) ); ?></th>
					<td>
						<?php
						$admin->display_hours_elements( $key );
						?>
						<p class="description">
							<?php
								/* Translators: %s: the day */
								printf( esc_html__( 'Times available for reservations on %s.', 'wp-restaurant-booking' ), esc_html( $value ) );
							?>
						</p>
					</td>
				</tr>
				<?php
			}

		}
		?>
	</table>

	<?php wp_nonce_field( 'wprb-set-hours', 'wprb-set-hours-nonce' ); ?>

	<input type="hidden" name="wprb-set-hours-sent" value="1">
	<input type="submit" class="button-primary wprb" value="<?php esc_html_e( 'Save settings', 'wp-restaurant-booking' ); ?>">

</form>
