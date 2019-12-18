<?php
/**
 * Reservation settings
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 0.9.0
 */

$last_minute_activate    = get_option( 'wprb-activate-last-minute' );
$last_minute_description = get_option( 'wprb-last-minute-description' );
$last_minute             = get_option( 'wprb-last-minute' );
?>
<!-- Form -->
<form name="wprb-set-last-minute" class="wprb-set-last-minute wprb-form"  method="post" action="">

	<table class="form-table">
		<tr class="wprb-activate-last-minute-field">
			<th scope="row"><?php esc_html_e( 'Activate', 'wp-restaurant-booking' ); ?></th>
			<td>
				<input type="checkbox" name="wprb-activate-last-minute" id="wprb-activate-last-minute" value="1"<?php echo ( 1 == $last_minute_activate ? ' checked="checked"' : '' ); ?>>
				<p class="description"><?php esc_html_e( 'Activate last minute bookable seats.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr class="wprb-last-minute-description-field">
			<th scope="row"><?php esc_html_e( 'Description', 'wp-restaurant-booking' ); ?></th>
			<td>
				<textarea name="wprb-last-minute-description" class="wprb-last-minute-description regular-text" rows="3" placeholder="<?php esc_html_e( 'Last minutes are tables available only for a limited time', 'wp-restaurant-booking' ); ?>" disabled><?php echo esc_html( $last_minute_description ); ?></textarea>
				<p class="description"><?php esc_html_e( 'A short description for the user about last minute.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr class="wprb-add-last-minute-field" style="display: none;">
			<th scope="row"><?php esc_html_e( 'Add', 'wp-restaurant-booking' ); ?></th>
			<td>
				<?php
				$admin->display_last_minute_elements();
				?>
				<p class="description"><?php esc_html_e( 'Last minute time and people details.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr>
			<th></th>
			<td><?php $admin->go_premium(); ?></td>
		</tr>
	</table>


	<?php wp_nonce_field( 'wprb-set-last-minute', 'wprb-set-last-minute-nonce' ); ?>

	<input type="hidden" name="wprb-set-last-minute-sent" value="1">
	<input type="submit" class="button-primary wprb" value="<?php esc_html_e( 'Save settings', 'wp-restaurant-booking' ); ?>" disabled>

</form>
