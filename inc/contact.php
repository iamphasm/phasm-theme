<?php
/**
 * Contact form: modal markup, submission handler and emails.
 *
 * - Any element with data-phasm-contact, or a link to "#contact-form", opens the form.
 * - Submissions are saved to Site inbox › Messages (see inc/inbox.php).
 * - The customer gets a dated copy of their message; the site owner gets a notification.
 * - Mail goes through the SMTP server set in Site inbox › Email settings (see inc/settings.php).
 *
 * @package phasm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHASM_MESSAGE_MAX', 400 );

/**
 * Enqueue the form script on the front end.
 */
function phasm_contact_assets() {
	wp_enqueue_script( 'phasm-contact', get_template_directory_uri() . '/assets/js/contact.js', array(), PHASM_VERSION, true );
	wp_localize_script(
		'phasm-contact',
		'phasmContact',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'max'     => PHASM_MESSAGE_MAX,
			'sending' => __( 'Sending…', 'phasm' ),
			'error'   => __( 'Something went wrong. Please try again, or contact us by email.', 'phasm' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'phasm_contact_assets' );

/**
 * Modal markup, printed once at the end of every page.
 */
function phasm_contact_modal() {
	$logo = get_template_directory_uri() . '/assets/images/phasm-logo.png';
	?>
	<dialog id="phasm-contact" class="contact-modal" aria-labelledby="phasm-contact-title">
		<div class="contact-modal__panel">
			<button type="button" class="contact-modal__close" data-phasm-close aria-label="<?php esc_attr_e( 'Close and go back to the website', 'phasm' ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg>
			</button>

			<div class="contact-modal__form-view">
				<div class="label label--accent"><?php esc_html_e( 'Contact', 'phasm' ); ?></div>
				<h2 id="phasm-contact-title" class="heading-2"><?php echo esc_html( phasm_mod( 'cta_title' ) ); ?></h2>

				<form class="contact-form" novalidate>
					<input type="hidden" name="action" value="phasm_contact">
					<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'phasm_contact' ) ); ?>">
					<div class="contact-form__hp" aria-hidden="true">
						<label for="phasm-website">Website</label>
						<input type="text" id="phasm-website" name="website" tabindex="-1" autocomplete="off">
					</div>

					<p class="contact-form__field">
						<label for="phasm-name"><?php esc_html_e( 'Name:', 'phasm' ); ?></label>
						<input type="text" id="phasm-name" name="name" required maxlength="100" autocomplete="name">
					</p>
					<div class="contact-form__row">
						<p class="contact-form__field">
							<label for="phasm-email"><?php esc_html_e( 'E-mail', 'phasm' ); ?></label>
							<input type="email" id="phasm-email" name="email" required maxlength="190" autocomplete="email">
						</p>
						<p class="contact-form__field">
							<label for="phasm-phone"><?php esc_html_e( 'Phone', 'phasm' ); ?></label>
							<input type="tel" id="phasm-phone" name="phone" maxlength="40" autocomplete="tel">
						</p>
					</div>
					<p class="contact-form__field">
						<label for="phasm-message"><?php esc_html_e( 'Message', 'phasm' ); ?></label>
						<textarea id="phasm-message" name="message" rows="6" required maxlength="<?php echo (int) PHASM_MESSAGE_MAX; ?>" aria-describedby="phasm-count"></textarea>
						<span id="phasm-count" class="contact-form__count" aria-live="polite">0 / <?php echo (int) PHASM_MESSAGE_MAX; ?></span>
					</p>

					<p class="contact-form__status" role="alert"></p>

					<div class="contact-form__actions">
						<button type="submit" class="btn btn--primary"><?php esc_html_e( 'Send', 'phasm' ); ?> <?php echo phasm_icon( 'arrow-right' ); // phpcs:ignore ?></button>
						<button type="reset" class="btn btn--outline"><?php esc_html_e( 'Reset', 'phasm' ); ?></button>
					</div>
				</form>
			</div>

			<div class="contact-modal__thanks" hidden tabindex="-1">
				<p class="contact-modal__thanks-text"><?php esc_html_e( 'Thanks for your enquiry, we will be in contact with you shortly.', 'phasm' ); ?></p>
				<img src="<?php echo esc_url( $logo ); ?>" alt="PHASM" width="765" height="190" class="contact-modal__logo">
			</div>
		</div>
	</dialog>
	<?php
}
add_action( 'wp_footer', 'phasm_contact_modal', 5 );

/**
 * Handle a submission (logged-in and logged-out visitors).
 */
function phasm_contact_submit() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'phasm_contact' ) ) {
		wp_send_json_error( array( 'message' => __( 'The form expired. Please reload the page and try again.', 'phasm' ) ), 400 );
	}

	// Honeypot: real visitors never fill this in. Pretend success to bots.
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success();
	}

	// Rate limit: 5 messages per 10 minutes per visitor (IP is hashed, never stored).
	$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$rate_key = 'phasm_rl_' . md5( wp_salt( 'nonce' ) . $ip );
	$count    = (int) get_transient( $rate_key );
	if ( $count >= 5 ) {
		wp_send_json_error( array( 'message' => __( 'Too many messages. Please wait a few minutes and try again.', 'phasm' ) ), 429 );
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	$errors = array();
	if ( '' === $name ) {
		$errors['name'] = __( 'Please enter your name.', 'phasm' );
	}
	if ( ! is_email( $email ) ) {
		$errors['email'] = __( 'Please enter a valid e-mail address.', 'phasm' );
	}
	if ( '' === trim( $message ) ) {
		$errors['message'] = __( 'Please write a message.', 'phasm' );
	} elseif ( mb_strlen( $message ) > PHASM_MESSAGE_MAX ) {
		/* translators: %d: max characters */
		$errors['message'] = sprintf( __( 'The message can be at most %d characters.', 'phasm' ), PHASM_MESSAGE_MAX );
	}
	if ( $errors ) {
		wp_send_json_error(
			array(
				'message' => __( 'Please check the highlighted fields.', 'phasm' ),
				'fields'  => $errors,
			),
			422
		);
	}

	set_transient( $rate_key, $count + 1, 10 * MINUTE_IN_SECONDS );

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'phasm_message',
			'post_status'  => 'publish',
			'post_title'   => $name,
			'post_content' => $message,
			'meta_input'   => array(
				'_phasm_email'  => $email,
				'_phasm_phone'  => $phone,
				'_phasm_closed' => 0,
			),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Your message could not be saved. Please try again.', 'phasm' ) ), 500 );
	}

	phasm_send_contact_emails( $post_id );

	wp_send_json_success();
}
add_action( 'wp_ajax_phasm_contact', 'phasm_contact_submit' );
add_action( 'wp_ajax_nopriv_phasm_contact', 'phasm_contact_submit' );

/**
 * Send the customer a dated copy, and notify the site owner.
 * Failures are logged on the message, never shown to the visitor.
 */
function phasm_send_contact_emails( $post_id ) {
	$post     = get_post( $post_id );
	$name     = $post->post_title;
	$email    = get_post_meta( $post_id, '_phasm_email', true );
	$phone    = get_post_meta( $post_id, '_phasm_phone', true );
	$date     = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), get_post_time( 'U', true, $post ) );
	$site     = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$settings = phasm_mail_settings();

	// 1. Copy to the customer.
	/* translators: %s: site name */
	$subject = sprintf( __( 'We received your enquiry – %s', 'phasm' ), $site );
	$body    = sprintf(
		/* translators: 1: name, 2: date, 3: message, 4: site name */
		__( "Hi %1\$s,\n\nThanks for your enquiry, we will be in contact with you shortly.\n\nThis is a copy of the message you sent on %2\$s:\n\n%3\$s\n\n— %4\$s", 'phasm' ),
		$name,
		$date,
		$post->post_content,
		$site
	);
	$sent_customer = phasm_mail( $email, $subject, $body );

	// 2. Notification to the site owner.
	$sent_owner = true;
	if ( $settings['notify'] ) {
		/* translators: %s: customer name */
		$subject = sprintf( __( 'New enquiry from %s', 'phasm' ), $name );
		$body    = sprintf(
			/* translators: 1: date, 2: name, 3: email, 4: phone, 5: message, 6: admin link */
			__( "New enquiry received %1\$s\n\nName: %2\$s\nE-mail: %3\$s\nPhone: %4\$s\n\nMessage:\n%5\$s\n\nOpen in Site inbox: %6\$s", 'phasm' ),
			$date,
			$name,
			$email,
			$phone ? $phone : '–',
			$post->post_content,
			admin_url( 'post.php?post=' . $post_id . '&action=edit' )
		);
		$sent_owner = phasm_mail( $settings['notify'], $subject, $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
	}

	update_post_meta( $post_id, '_phasm_mail_status', ( $sent_customer ? 'customer:ok' : 'customer:failed' ) . ' ' . ( $sent_owner ? 'owner:ok' : 'owner:failed' ) );
}
