<?php
/**
 * Single reservation template
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 0.9.0
 */

$id         = get_the_ID();
$first_name = get_post_meta( $id, 'wprb-first-name', true );
$last_name  = get_post_meta( $id, 'wprb-last-name', true );
$email      = get_post_meta( $id, 'wprb-email', true );
$phone      = get_post_meta( $id, 'wprb-phone', true );
$people     = get_post_meta( $id, 'wprb-people', true );
$table      = get_post_meta( $id, 'wprb-table', true );
$date       = get_post_meta( $id, 'wprb-date', true );
$time       = get_post_meta( $id, 'wprb-time', true );
$notes      = get_post_meta( $id, 'wprb-notes', true );
$status     = get_post_meta( $id, 'wprb-status', true );

?>


	<table class="wprb-reservation form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'First name', 'wprb' ); ?></th>
			<td>
				<input type="text" name="wprb-first-name" class="wprb-first-name" value="<?php echo $first_name; ?>" placeholder="<?php esc_html_e( wp_unslash( 'John', 'wprb' ) ) ?>" required>
				<p class="description"><?php esc_html_e( 'The customer first name', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Last name', 'wprb' ); ?></th>
			<td>
				<input type="text" name="wprb-last-name" class="wprb-last-name" value="<?php echo $last_name; ?>" placeholder="<?php esc_html_e( wp_unslash( 'Doe', 'wprb' ) ) ?>" required>
				<p class="description"><?php esc_html_e( 'The customer last name', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Email', 'wprb' ); ?></th>
			<td>
				<input type="email" name="wprb-email" class="wprb-email" value="<?php echo $email; ?>" placeholder="<?php esc_html_e( wp_unslash( 'john@doe.com', 'wprb' ) ) ?>" required>
				<p class="description"><?php esc_html_e( 'The customer email', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Phone', 'wprb' ); ?></th>
			<td>
				<input type="tel" name="wprb-phone" class="wprb-phone" value="<?php echo $phone; ?>" placeholder="00465688345" required>
				<p class="description"><?php esc_html_e( 'The customer phone number', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'People', 'wprb' ); ?></th>
			<td>
				<input type="number" name="wprb-people" class="wprb-people" value="<?php echo $people; ?>" placeholder="2" required>
				<p class="description"><?php esc_html_e( 'The number of people', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Table', 'wprb' ); ?></th>
			<td>
				<input type="text" name="wprb-table" class="wprb-table" value="<?php echo $table; ?>" placeholder="5A">
				<p class="description"><?php esc_html_e( 'The table assigned to this reservation', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Date', 'wprb' ); ?></th>
			<td>
				<input type="date" name="wprb-date" class="wprb-date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo $date; ?>" required>
				<p class="description"><?php esc_html_e( 'The reservation date', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Time', 'wprb' ); ?></th>
			<td>
				<input type="time" name="wprb-time" class="wprb-time" value="<?php echo $time; ?>" placeholder="<?php esc_html_e( wp_unslash( 'xxxx', 'wprb' ) ) ?>" required>
				<p class="description"><?php esc_html_e( 'The time of the reservation', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Notes', 'wprb' ); ?></th>
			<td>
				<textarea name="wprb-notes" class="wprb-notes" rows="6"><?php echo $notes; ?></textarea>
				<p class="description"><?php esc_html_e( 'Notes of the customer for this reservation', 'wprb' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Status', 'wprb' ); ?></th>
			<td>
				<select name="wprb-status" class="wprb-status">
					<option value="received"><?php esc_html_e( 'Received', 'wprb' ) ?></option>
					<option value="managed"><?php esc_html_e( 'Managed', 'wprb' ) ?></option>
					<option value="completed"><?php esc_html_e( 'Completed', 'wprb' ) ?></option>
					<option value="expired"><?php esc_html_e( 'Expired', 'wprb' ) ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'The status of the reservation', 'wprb' ); ?></p>
			</td>
		</tr>

	</table>
