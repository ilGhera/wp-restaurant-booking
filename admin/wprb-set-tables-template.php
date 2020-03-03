<?php
/**
 * Reservation tables settings
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 1.1.0
 */
?>
<!-- Form -->
<form name="wprb-set-rooms-tables" class="wprb-set-rooms-tables wprb-form"  method="post" action="">

	<table class="form-table">

		<?php $admin->display_rooms_tables_elements(); ?>

		<tr class="go-premium">
		   	<th></th>
	    	<td><?php $admin->go_premium(); ?></td>
		</tr>
	
	</table>

	<?php wp_nonce_field( 'wprb-set-rooms-tables', 'wprb-set-rooms-tables-nonce' ); ?>

	<input type="hidden" name="wprb-set-rooms-tables-sent" value="1">
	<input type="submit" class="button-primary wprb" value="<?php esc_html_e( 'Save settings', 'wp-restaurant-booking' ); ?>">

</form>
