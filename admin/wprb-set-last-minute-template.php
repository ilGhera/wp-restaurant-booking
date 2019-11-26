<?php
/**
 * Reservation settings
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 0.9.0
 */
$last_minute_activate = get_option( 'wprb-activate-last-minute' );
$last_minute          = get_option( 'wprb-last-minute' );
?>
<!-- Form -->
<form name="wprb-set-last-minute" class="wprb-set-last-minute wprb-form"  method="post" action="">

	<table class="form-table">
		<tr class="wprb-activate-last-minute-field">
			<th scope="row"><?php esc_html_e( 'Activate', 'wprb' ); ?></th>
			<td>
				<input type="checkbox" name="wprb-activate-last-minute" id="wprb-activate-last-minute" value="1"<?php echo ( 1 == $last_minute_activate ? ' checked="checked"' : '' ); ?>>
				<p class="description"><?php esc_html_e( 'Activate last minute bookable seats.', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr class="wprb-add-last-minute-field" style="display: none;">
			<th scope="row"><?php esc_html_e( 'test', 'wprb' ); ?></th>
			<td>
				<?php
				$admin->display_last_minute_elements();
				?>
				<p class="description"><?php esc_html_e( 'Last minute time and people details.', 'wprb' ); ?></p>
			</td>
		</tr>
	</table>

	<?php wp_nonce_field( 'wprb-set-last-minute', 'wprb-set-last-minute-nonce' ); ?>

	<input type="hidden" name="wprb-set-last-minute-sent" value="1">
	<input type="submit" class="button-primary wprb" value="<?php esc_html_e( 'Save settings', 'wprb' ); ?>">

</form>
