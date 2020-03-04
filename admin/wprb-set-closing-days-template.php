<?php
/**
 * Closing days
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 1.0.0
 */

?>
<!-- Form -->
<form name="wprb-closing-days" class="wprb-closing-days wprb-form"  method="post" action="">

	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Week days', 'wp-restaurant-booking' ); ?></th>
			<td>
				<select name="wprb-closing-days[]" data-placeholder="<?php esc_html_e( 'Select some options', 'wp-restaurant-booking' ); ?>" id="wprb-button-position" class="wprb-select" multiple>
					<?php
					foreach ( WPRB_Admin::week() as $key => $value ) {
						
						echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';

					}
					?>
				</select>
				<p class="description"><?php esc_html_e( 'Select the closing day(s) of your business.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr class="wprb-add-closing-periods-field">
			<th scope="row"><?php esc_html_e( 'Periods', 'wp-restaurant-booking' ); ?></th>
			<td>
				<?php
				$admin->display_closing_period_elements();
				?>
				<p class="description"><?php esc_html_e( 'Add a new closing period.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr>
		   	<th></th>
	    	<td><?php $admin->go_premium(); ?></td>
		</tr>
	</table>

	<?php wp_nonce_field( 'wprb-set-closing-days', 'wprb-set-closing-days-nonce' ); ?>

	<input type="hidden" name="wprb-set-closing-days-sent" value="1">
	<input type="submit" class="button-primary wprb" value="<?php esc_html_e( 'Save settings', 'wp-restaurant-booking' ); ?>" disabled>

</form>
