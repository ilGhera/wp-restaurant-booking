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
$date       = get_post_meta( $id, 'wprb-date', true );
$time       = get_post_meta( $id, 'wprb-time', true );

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

	</table>
