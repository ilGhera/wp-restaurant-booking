<?php
/**
 * Notification settings
 *
 * @author ilGhera
 * @package wp-restaurant-booking/admin
 * @since 0.9.0
 */

$admin_activate       = get_option( 'wprb-activate-admin-notification' );
$admin_recipients     = get_option( 'wprb-admin-recipients' ) ? get_option( 'wprb-admin-recipients' ) : array( get_option( 'admin_email' ) );

/*Define the user object*/
$default_user_object  = WPRB_Notifications::default_user_object();
$user_object          = null;

/*Define the admin object*/
$default_user_message = WPRB_Notifications::default_user_message();
$user_message         = null;
?>

<form name="wprb-set-notifications" class="wprb-form"  method="post" action="">

	<table class="form-table">
		<tr class="wprb-activate-admin-notifications-field">
			<th scope="row"><?php esc_html_e( 'Admin notifications', 'wp-restaurant-booking' ); ?></th>
			<td>
				<input type="checkbox" name="wprb-activate-admin-notification" id="wprb-activate-admin-notification" value="1"<?php echo ( 1 == $admin_activate ? ' checked="checked"' : '' ); ?>>
				<p class="description"><?php esc_html_e( 'Activate email notifications for the admin.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr class="wprb-admin-recipients" style="display: none;">
			<th></th>
			<td>
				<?php
				echo '<textarea name="wprb-admin-recipients" class="wprb-admin-recipients wprb" cols="33">';
				if ( is_array( $admin_recipients ) && ! empty( $admin_recipients ) ) {

					foreach ( $admin_recipients as $email ) {

						echo esc_html( $email ) . "\n";

					}

				}
				echo '</textarea>';
				?>
				<p class="description"><?php esc_html_e( 'Add all the recipients necessary divided by a comma.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr class="wprb-activate-user-notifications-field">
			<th scope="row"><?php esc_html_e( 'User notifications', 'wp-restaurant-booking' ); ?></th>
			<td>
				<input type="checkbox" name="wprb-activate-user-notification" id="wprb-activate-user-notification" value="1">
				<p class="description"><?php esc_html_e( 'Activate email notifications for the user.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr class="wprb-user-notification-field" style="display: none;">
			<th scope="row"><?php esc_html_e( 'Object', 'wp-restaurant-booking' ); ?></th>
			<td>
				<input type="text" name="wprb-user-notification-object" class="wprb-user-notification-object regular-text" placeholder="<?php echo esc_attr( $default_user_object ); ?>" value="<?php echo esc_attr( $user_object ); ?>" disabled>
				<p class="description"><?php esc_html_e( 'Specify a different email object.', 'wp-restaurant-booking' ); ?></p>
			</td>
		</tr>
		<tr class="wprb-user-notification-field" style="display: none;">
			<th scope="row"><?php esc_html_e( 'Message', 'wp-restaurant-booking' ); ?></th>
			<td>
				<textarea type="text" name="wprb-user-notification-message" class="wprb-user-notification-message regular-text" rows="6" placeholder="<?php echo esc_attr( $default_user_message ); ?>" disabled><?php echo esc_html( $user_message ); ?></textarea>
				<p class="description">
					<?php
					echo '<span class="shortcodes">';
						echo '<code>[first-name]</code> <code>[last-name]</code> <code>[email]</code> <code>[phone]</code>';
						echo '<code>[people]</code> <code>[date]</code> <code>[time]</code> <code>[until]</code> <code>[notes]</code>';
					echo '</span>';
					esc_html_e( 'Specify a different email message.', 'wp-restaurant-booking' );
					?>
				</p>
				<?php $admin->go_premium(); ?>
			</td>
		</tr>
	</table>

	<?php wp_nonce_field( 'wprb-set-notifications', 'wprb-set-notifications-nonce' ); ?>

	<input type="hidden" name="wprb-set-notifications-sent" value="1">
	<input type="submit" class="button-primary wprb" value="<?php esc_html_e( 'Save settings', 'wp-restaurant-booking' ); ?>">

</form>
