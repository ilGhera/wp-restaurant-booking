<?php
/**
 * Closing days
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 0.9.0
 */

$closing_days = get_option( 'wprb-closing-days' );
?>
<!-- Form -->
<form name="wprb-closing-days" class="wprb-closing-days wprb-form"  method="post" action="">

	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Week days', 'wp-restaurant-booking' ); ?></th>
			<td>
				<select name="wprb-closing-days[]" id="wprb-button-position" multiple>
					<?php
					foreach ( WPRB_Admin::week() as $key => $value ) {
						
						echo '<option value="' . esc_attr( $key ) . '"' . ( is_array( $closing_days ) && in_array( $key , $closing_days) ? ' selected="selected"' : '' ) . '>' . esc_html( $value ) . '</option>';

					}
					?>
				</select>
				<p class="description"><?php esc_html_e( 'Select the closing day(s) of your business.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr class="wprb-add-closing-periods-field">
			<th scope="row"><?php esc_html_e( 'Add', 'wp-restaurant-booking' ); ?></th>
			<td>
				<?php
				$admin->display_closing_period_elements();
				?>
				<p class="description"><?php esc_html_e( 'Add a new closing period.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
	</table>

	<?php wp_nonce_field( 'wprb-set-closing-days', 'wprb-set-closing-days-nonce' ); ?>

	<input type="hidden" name="wprb-set-closing-days-sent" value="1">
	<input type="submit" class="button-primary wprb" value="<?php esc_html_e( 'Save settings', 'wp-restaurant-booking' ); ?>">

</form>
