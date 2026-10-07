<?php
/**
 * Site inbox › Email settings: SMTP server for the contact form e-mails.
 *
 * The SMTP password is stored encrypted (AES-256, key derived from the site's
 * secret keys in wp-config.php). If those keys change, enter the password again.
 *
 * @package phasm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings with defaults.
 */
function phasm_mail_settings() {
	$defaults = array(
		'host'       => '',
		'port'       => 587,
		'encryption' => 'tls',
		'username'   => '',
		'password'   => '',
		'from_email' => '',
		'from_name'  => get_bloginfo( 'name' ),
		'notify'     => get_option( 'admin_email' ),
		'signature'  => "Best Regards,\nPhasm",
		'all_mail'   => 0,
	);
	return wp_parse_args( (array) get_option( 'phasm_mail', array() ), $defaults );
}

/**
 * Encrypt / decrypt the SMTP password.
 */
function phasm_crypt_key() {
	return hash( 'sha256', wp_salt( 'auth' ) . 'phasm-smtp', true );
}

function phasm_encrypt( $plain ) {
	if ( '' === $plain || ! function_exists( 'openssl_encrypt' ) ) {
		return '';
	}
	$iv = random_bytes( 16 );
	$ct = openssl_encrypt( $plain, 'aes-256-cbc', phasm_crypt_key(), OPENSSL_RAW_DATA, $iv );
	return base64_encode( $iv . $ct ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
}

function phasm_decrypt( $stored ) {
	if ( '' === $stored || ! function_exists( 'openssl_decrypt' ) ) {
		return '';
	}
	$raw = base64_decode( $stored, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	if ( false === $raw || strlen( $raw ) < 17 ) {
		return '';
	}
	$plain = openssl_decrypt( substr( $raw, 16 ), 'aes-256-cbc', phasm_crypt_key(), OPENSSL_RAW_DATA, substr( $raw, 0, 16 ) );
	return false === $plain ? '' : $plain;
}

/**
 * Send an e-mail through the PHASM SMTP settings.
 */
function phasm_mail( $to, $subject, $body, $headers = array() ) {
	$GLOBALS['phasm_sending'] = true;
	$sent                     = wp_mail( $to, $subject, $body, $headers );
	$GLOBALS['phasm_sending'] = false;
	return $sent;
}

/**
 * Configure PHPMailer for SMTP (theme e-mails, or all e-mails if chosen).
 */
function phasm_phpmailer_init( $phpmailer ) {
	if ( ! phasm_uses_smtp() ) {
		return;
	}
	$s = phasm_mail_settings();
	$phpmailer->isSMTP();
	$phpmailer->Host    = $s['host']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$phpmailer->Port    = (int) $s['port']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$phpmailer->Timeout = 15; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	if ( 'ssl' === $s['encryption'] ) {
		$phpmailer->SMTPSecure = 'ssl'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	} elseif ( 'tls' === $s['encryption'] ) {
		$phpmailer->SMTPSecure = 'tls'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	} else {
		$phpmailer->SMTPSecure  = ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$phpmailer->SMTPAutoTLS = false; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	}
	if ( '' !== $s['username'] ) {
		$phpmailer->SMTPAuth = true; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$phpmailer->Username = $s['username']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$phpmailer->Password = phasm_decrypt( $s['password'] ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	}
}
add_action( 'phpmailer_init', 'phasm_phpmailer_init' );

/**
 * Sender address and name (applied before WordPress builds the e-mail).
 */
function phasm_uses_smtp() {
	$s = phasm_mail_settings();
	return ! empty( $s['host'] ) && ( ! empty( $GLOBALS['phasm_sending'] ) || ! empty( $s['all_mail'] ) );
}

add_filter(
	'wp_mail_from',
	function ( $from ) {
		if ( ! phasm_uses_smtp() ) {
			return $from;
		}
		$s = phasm_mail_settings();
		if ( is_email( $s['from_email'] ) ) {
			return $s['from_email'];
		}
		return is_email( $s['username'] ) ? $s['username'] : $from;
	},
	20
);

add_filter(
	'wp_mail_from_name',
	function ( $name ) {
		$s = phasm_mail_settings();
		return phasm_uses_smtp() && '' !== $s['from_name'] ? $s['from_name'] : $name;
	},
	20
);

/**
 * Keep the last mail error so the settings page can show it.
 */
add_action(
	'wp_mail_failed',
	function ( $error ) {
		set_transient( 'phasm_last_mail_error', $error->get_error_message(), DAY_IN_SECONDS );
	}
);

/**
 * Admin page.
 */
function phasm_settings_menu() {
	add_submenu_page(
		'edit.php?post_type=phasm_message',
		__( 'Email settings', 'phasm' ),
		__( 'Email settings', 'phasm' ),
		'manage_options',
		'phasm-mail',
		'phasm_settings_page'
	);
}
add_action( 'admin_menu', 'phasm_settings_menu', 20 );

/**
 * Save settings / send a test e-mail.
 */
function phasm_settings_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'phasm' ) );
	}
	check_admin_referer( 'phasm_mail_settings' );

	$old = phasm_mail_settings();
	$in  = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	$enc  = isset( $in['encryption'] ) && in_array( $in['encryption'], array( 'tls', 'ssl', 'none' ), true ) ? $in['encryption'] : 'tls';
	$port = isset( $in['port'] ) ? absint( $in['port'] ) : 0;
	if ( ! $port || $port > 65535 ) {
		$port = 'ssl' === $enc ? 465 : ( 'none' === $enc ? 25 : 587 );
	}

	$new = array(
		'host'       => isset( $in['host'] ) ? sanitize_text_field( $in['host'] ) : '',
		'port'       => $port,
		'encryption' => $enc,
		'username'   => isset( $in['username'] ) ? sanitize_text_field( $in['username'] ) : '',
		'password'   => $old['password'],
		'from_email' => isset( $in['from_email'] ) ? sanitize_email( $in['from_email'] ) : '',
		'from_name'  => isset( $in['from_name'] ) ? sanitize_text_field( $in['from_name'] ) : '',
		'notify'     => isset( $in['notify'] ) ? sanitize_email( $in['notify'] ) : '',
		'all_mail'   => empty( $in['all_mail'] ) ? 0 : 1,
		'signature'  => isset( $in['signature'] ) && '' !== trim( $in['signature'] ) ? sanitize_textarea_field( $in['signature'] ) : "Best Regards,\nPhasm",
	);
	if ( ! empty( $in['clear_password'] ) ) {
		$new['password'] = '';
	} elseif ( isset( $in['password'] ) && '' !== $in['password'] ) {
		$new['password'] = phasm_encrypt( (string) $in['password'] );
	}
	update_option( 'phasm_mail', $new, false );

	$notice = 'saved';
	if ( ! empty( $in['send_test'] ) ) {
		delete_transient( 'phasm_last_mail_error' );
		$to   = is_email( $in['test_to'] ?? '' ) ? $in['test_to'] : wp_get_current_user()->user_email;
		$ok   = phasm_mail(
			$to,
			/* translators: %s: site name */
			sprintf( __( 'Test e-mail from %s', 'phasm' ), get_bloginfo( 'name' ) ),
			__( 'This is a test e-mail from the PHASM theme. If you can read this, your SMTP settings work.', 'phasm' )
		);
		$notice = $ok ? 'test_ok' : 'test_failed';
	}
	wp_safe_redirect( admin_url( 'edit.php?post_type=phasm_message&page=phasm-mail&phasm_notice=' . $notice ) );
	exit;
}
add_action( 'admin_post_phasm_mail_settings', 'phasm_settings_save' );

function phasm_settings_page() {
	$s      = phasm_mail_settings();
	$notice = isset( $_GET['phasm_notice'] ) ? sanitize_key( $_GET['phasm_notice'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Email settings', 'phasm' ); ?></h1>
		<p><?php esc_html_e( 'The contact form sends the customer a dated copy of their message, and sends you a notification. Enter the SMTP server of the e-mail account that should send these e-mails.', 'phasm' ); ?></p>

		<?php if ( 'saved' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'phasm' ); ?></p></div>
		<?php elseif ( 'test_ok' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved, and the test e-mail was sent. Check the inbox (and spam folder).', 'phasm' ); ?></p></div>
		<?php elseif ( 'test_failed' === $notice ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Settings saved, but the test e-mail could not be sent:', 'phasm' ); ?> <code><?php echo esc_html( (string) get_transient( 'phasm_last_mail_error' ) ); ?></code></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="phasm_mail_settings">
			<?php wp_nonce_field( 'phasm_mail_settings' ); ?>

			<h2 class="title"><?php esc_html_e( 'SMTP server', 'phasm' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="phasm-host"><?php esc_html_e( 'SMTP server', 'phasm' ); ?></label></th>
					<td><input name="host" id="phasm-host" type="text" class="regular-text" value="<?php echo esc_attr( $s['host'] ); ?>" placeholder="smtp.example.com">
						<p class="description"><?php esc_html_e( 'Leave empty to use the server\'s normal mail function.', 'phasm' ); ?></p></td>
				</tr>
				<tr>
					<th scope="row"><label for="phasm-enc"><?php esc_html_e( 'Encryption', 'phasm' ); ?></label></th>
					<td>
						<select name="encryption" id="phasm-enc">
							<option value="tls" <?php selected( $s['encryption'], 'tls' ); ?>><?php esc_html_e( 'STARTTLS (usually port 587)', 'phasm' ); ?></option>
							<option value="ssl" <?php selected( $s['encryption'], 'ssl' ); ?>><?php esc_html_e( 'SSL/TLS (usually port 465)', 'phasm' ); ?></option>
							<option value="none" <?php selected( $s['encryption'], 'none' ); ?>><?php esc_html_e( 'None (not recommended)', 'phasm' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="phasm-port"><?php esc_html_e( 'Port (optional)', 'phasm' ); ?></label></th>
					<td><input name="port" id="phasm-port" type="number" min="1" max="65535" class="small-text" value="<?php echo esc_attr( $s['port'] ); ?>">
						<p class="description"><?php esc_html_e( 'Leave empty to use the usual port for the chosen encryption.', 'phasm' ); ?></p></td>
				</tr>
				<tr>
					<th scope="row"><label for="phasm-user"><?php esc_html_e( 'Username', 'phasm' ); ?></label></th>
					<td><input name="username" id="phasm-user" type="text" class="regular-text" value="<?php echo esc_attr( $s['username'] ); ?>" autocomplete="off"></td>
				</tr>
				<tr>
					<th scope="row"><label for="phasm-pass"><?php esc_html_e( 'Password', 'phasm' ); ?></label></th>
					<td><input name="password" id="phasm-pass" type="password" class="regular-text" value="" autocomplete="new-password" placeholder="<?php echo $s['password'] ? esc_attr__( 'Saved — leave empty to keep', 'phasm' ) : ''; ?>">
						<?php if ( $s['password'] ) : ?>
							<label style="margin-left:8px"><input type="checkbox" name="clear_password" value="1"> <?php esc_html_e( 'Remove saved password', 'phasm' ); ?></label>
						<?php endif; ?>
						<p class="description"><?php esc_html_e( 'Stored encrypted. Use an app password if your e-mail provider offers one.', 'phasm' ); ?></p></td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Sender and notifications', 'phasm' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="phasm-from"><?php esc_html_e( 'From e-mail', 'phasm' ); ?></label></th>
					<td><input name="from_email" id="phasm-from" type="email" class="regular-text" value="<?php echo esc_attr( $s['from_email'] ); ?>">
						<p class="description"><?php esc_html_e( 'Usually the same address as the username. Leave empty to use the username.', 'phasm' ); ?></p></td>
				</tr>
				<tr>
					<th scope="row"><label for="phasm-fromname"><?php esc_html_e( 'From name', 'phasm' ); ?></label></th>
					<td><input name="from_name" id="phasm-fromname" type="text" class="regular-text" value="<?php echo esc_attr( $s['from_name'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="phasm-notify"><?php esc_html_e( 'Notify me at', 'phasm' ); ?></label></th>
					<td><input name="notify" id="phasm-notify" type="email" class="regular-text" value="<?php echo esc_attr( $s['notify'] ); ?>">
						<p class="description"><?php esc_html_e( 'You get a notification here for each new message. Leave empty for no notifications.', 'phasm' ); ?></p></td>
				</tr>
				<tr>
					<th scope="row"><label for="phasm-sig"><?php esc_html_e( 'Signature on answers', 'phasm' ); ?></label></th>
					<td><textarea name="signature" id="phasm-sig" rows="3" class="regular-text"><?php echo esc_textarea( $s['signature'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Added below every answer you send from Site inbox.', 'phasm' ); ?></p></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'All WordPress e-mails', 'phasm' ); ?></th>
					<td><label><input type="checkbox" name="all_mail" value="1" <?php checked( $s['all_mail'] ); ?>> <?php esc_html_e( 'Also send WordPress\'s own e-mails (password resets, notifications) through this server', 'phasm' ); ?></label></td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Test', 'phasm' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="phasm-testto"><?php esc_html_e( 'Send a test e-mail to', 'phasm' ); ?></label></th>
					<td><input name="test_to" id="phasm-testto" type="email" class="regular-text" value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>"></td>
				</tr>
			</table>

			<p class="submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Save settings', 'phasm' ); ?></button>
				<button type="submit" class="button" name="send_test" value="1"><?php esc_html_e( 'Save and send test e-mail', 'phasm' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}
