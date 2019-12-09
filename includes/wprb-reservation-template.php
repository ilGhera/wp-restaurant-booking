<?php
/**
 * Single reservation template
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 0.9.0
 */

$reservation_id    = get_the_ID();
$first_name        = get_post_meta( $reservation_id, 'wprb-first-name', true );
$last_name         = get_post_meta( $reservation_id, 'wprb-last-name', true );
$email             = get_post_meta( $reservation_id, 'wprb-email', true );
$phone             = get_post_meta( $reservation_id, 'wprb-phone', true );
$people            = get_post_meta( $reservation_id, 'wprb-people', true );
$table             = get_post_meta( $reservation_id, 'wprb-table', true );
$date              = get_post_meta( $reservation_id, 'wprb-date', true );
$time              = get_post_meta( $reservation_id, 'wprb-time', true );
$external          = get_post_meta( $reservation_id, 'wprb-external', true );
$until             = get_post_meta( $reservation_id, 'wprb-until', true );
$notes             = get_post_meta( $reservation_id, 'wprb-notes', true );
$res_status        = get_post_meta( $reservation_id, 'wprb-status', true );
$last_minute       = $until ? true : false;
$last_minute_class = $last_minute ? ' last-minute' : '';
$external_class    = $external ? ' external' : '';
?>

<table class="wprb-reservation form-table<?php echo esc_html( $last_minute_class . $external_class ); ?>" data-date="<?php echo esc_attr( $date ); ?>" data-time="<?php echo esc_attr( $time ); ?>">
	<tr>
		<th scope="row"><?php esc_html_e( 'First name', 'wprb' ); ?></th>
		<td>
			<input type="text" name="wprb-first-name" class="wprb-first-name" value="<?php echo esc_attr( wp_unslash( $first_name ) ); ?>" placeholder="<?php esc_html_e( 'John', 'wprb' ); ?>" required>
			<p class="description"><?php esc_html_e( 'The customer first name', 'wprb' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Last name', 'wprb' ); ?></th>
		<td>
			<input type="text" name="wprb-last-name" class="wprb-last-name" value="<?php echo esc_attr( wp_unslash( $last_name ) ); ?>" placeholder="<?php esc_html_e( 'Doe', 'wprb' ); ?>" required>
			<p class="description"><?php esc_html_e( 'The customer last name', 'wprb' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Email', 'wprb' ); ?></th>
		<td>
			<input type="email" name="wprb-email" class="wprb-email" value="<?php echo esc_attr( wp_unslash( $email ) ); ?>" placeholder="<?php esc_html_e( 'john@doe.com', 'wprb' ); ?>" required>
			<p class="description"><?php esc_html_e( 'The customer email', 'wprb' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Phone', 'wprb' ); ?></th>
		<td>
			<input type="tel" name="wprb-phone" class="wprb-phone" value="<?php echo esc_attr( wp_unslash( $phone ) ); ?>" placeholder="00465688345" required>
			<p class="description"><?php esc_html_e( 'The customer phone number', 'wprb' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'People', 'wprb' ); ?></th>
		<td>
			<input type="number" name="wprb-people" class="wprb-people" value="<?php echo esc_attr( wp_unslash( $people ) ); ?>" min="1" placeholder="2" required>
			<p class="description"><?php esc_html_e( 'The number of people', 'wprb' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Table', 'wprb' ); ?></th>
		<td>
			<input type="text" name="wprb-table" class="wprb-table" value="<?php echo esc_attr( wp_unslash( $table ) ); ?>" placeholder="5A">
			<p class="description"><?php esc_html_e( 'The table assigned to this reservation', 'wprb' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Date', 'wprb' ); ?></th>
		<td>
			<input type="date" name="wprb-date" class="wprb-date" min="<?php echo esc_html( wp_unslash( date( 'Y-m-d' ) ) ); ?>" value="<?php echo esc_attr( wp_unslash( $date ) ); ?>" required>
			<p class="description"><?php esc_html_e( 'The reservation date', 'wprb' ); ?></p>
		</td>
	</tr>
	<tr class="wprb-hours">
		<th scope="row"><?php esc_html_e( 'Time', 'wprb' ); ?></th>
		<td>
			<input type="text" name="wprb-time" class="wprb-time" value="<?php echo esc_attr( wp_unslash( $time ) ); ?>" required>
			<input type="hidden" name="wprb-until" class="wprb-until" value="<?php echo esc_attr( wp_unslash( $until ) ); ?>">
			<input type="hidden" name="wprb-external" class="wprb-external" value="<?php echo esc_attr( wp_unslash( $external ) ); ?>">
			<div class="booking-hours">
				<?php
				if ( $time && $people ) {

					WPRB_Reservation_Widget::hours_select_element( $people, $date, true, $last_minute, $external, $time );

				}
				?>
			</div>
			<p class="description"><?php esc_html_e( 'The time of the reservation', 'wprb' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Notes', 'wprb' ); ?></th>
		<td>
			<textarea name="wprb-notes" class="wprb-notes" rows="6"><?php echo esc_html( wp_unslash( $notes ) ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Notes of the customer for this reservation', 'wprb' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Status', 'wprb' ); ?></th>
		<td>
			<select name="wprb-status" class="wprb-status">
				<option value="received"<?php echo 'received' === $res_status ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Received', 'wprb' ); ?></option>
				<option value="managed"<?php echo 'managed' === $res_status ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Managed', 'wprb' ); ?></option>
				<option value="completed"<?php echo 'completed' === $res_status ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Completed', 'wprb' ); ?></option>
				<option value="expired"<?php echo 'expired' === $res_status ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Expired', 'wprb' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'The status of the reservation', 'wprb' ); ?></p>
		</td>
	</tr>
	<?php wp_nonce_field( 'wprb-save-reservation', 'wprb-save-reservation-nonce' ); ?>
</table>
