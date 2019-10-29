<?php
/**
 * Notification settings
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 0.9.0
 */

?>

<!-- Export form -->
<form name="wprb-set-notifications" class="wprb-form"  method="post" action="">

	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Notifications', 'wprb' ); ?></th>
			<td>
				<p class="description"><?php esc_html_e( '....', 'wprb' ); ?></p>
			</td>
		</tr>
	</table>

	<input type="submit" class="button-primary wprb" value="<?php esc_html_e( 'Save settings', 'wprb' ); ?>">

</form>
