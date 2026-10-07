<?php
/**
 * Site inbox: contact form messages in the WordPress admin.
 *
 * Menu: Site inbox › Messages (open) / Closed messages / Email settings.
 * A message is closed with the "Closed" checkbox on the message itself,
 * or with the Close / Reopen links in the lists. Only administrators can see messages.
 *
 * @package phasm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the message post type (private, admin only).
 */
function phasm_register_message_type() {
	$cap = 'manage_options';
	register_post_type(
		'phasm_message',
		array(
			'labels'              => array(
				'name'               => __( 'Messages', 'phasm' ),
				'singular_name'      => __( 'Message', 'phasm' ),
				'menu_name'          => __( 'Site inbox', 'phasm' ),
				'all_items'          => __( 'Messages', 'phasm' ),
				'edit_item'          => __( 'Message', 'phasm' ),
				'search_items'       => __( 'Search messages', 'phasm' ),
				'not_found'          => __( 'No messages.', 'phasm' ),
				'not_found_in_trash' => __( 'No messages in the bin.', 'phasm' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_position'       => 25,
			'menu_icon'           => 'dashicons-email-alt',
			'supports'            => array( '' ),
			'map_meta_cap'        => false,
			'capabilities'        => array(
				'edit_post'              => $cap,
				'read_post'              => $cap,
				'delete_post'            => $cap,
				'edit_posts'             => $cap,
				'edit_others_posts'      => $cap,
				'edit_published_posts'   => $cap,
				'publish_posts'          => $cap,
				'delete_posts'           => $cap,
				'delete_others_posts'    => $cap,
				'delete_published_posts' => $cap,
				'read_private_posts'     => $cap,
				'create_posts'           => 'do_not_allow',
			),
		)
	);
}
add_action( 'init', 'phasm_register_message_type' );

/**
 * Number of open messages.
 */
function phasm_open_message_count() {
	$q = new WP_Query(
		array(
			'post_type'      => 'phasm_message',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array( phasm_open_meta_query() ), // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	return (int) $q->found_posts;
}

function phasm_open_meta_query() {
	return array(
		'relation' => 'OR',
		array(
			'key'     => '_phasm_closed',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'   => '_phasm_closed',
			'value' => '1',
			'compare' => '!=',
		),
	);
}

/**
 * Submenu: Closed messages, plus a count bubble on the menu.
 */
function phasm_inbox_menu() {
	add_submenu_page(
		'edit.php?post_type=phasm_message',
		__( 'Closed messages', 'phasm' ),
		__( 'Closed messages', 'phasm' ),
		'manage_options',
		'edit.php?post_type=phasm_message&phasm_view=closed'
	);

	global $menu;
	$open = phasm_open_message_count();
	if ( $open && is_array( $menu ) ) {
		foreach ( $menu as $i => $item ) {
			if ( isset( $item[2] ) && 'edit.php?post_type=phasm_message' === $item[2] ) {
				$menu[ $i ][0] .= ' <span class="awaiting-mod count-' . $open . '"><span class="pending-count">' . number_format_i18n( $open ) . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			}
		}
	}
}
add_action( 'admin_menu', 'phasm_inbox_menu' );

/**
 * Highlight the right submenu item.
 */
function phasm_inbox_submenu_file( $submenu_file ) {
	$screen = get_current_screen();
	if ( $screen && 'phasm_message' === $screen->post_type ) {
		$closed = ( isset( $_GET['phasm_view'] ) && 'closed' === $_GET['phasm_view'] ) // phpcs:ignore WordPress.Security.NonceVerification
			|| ( 'post' === $screen->base && isset( $_GET['post'] ) && get_post_meta( (int) $_GET['post'], '_phasm_closed', true ) ); // phpcs:ignore WordPress.Security.NonceVerification
		return $closed ? 'edit.php?post_type=phasm_message&phasm_view=closed' : 'edit.php?post_type=phasm_message';
	}
	return $submenu_file;
}
add_filter( 'submenu_file', 'phasm_inbox_submenu_file' );

/**
 * Lists: Messages shows open ones, Closed messages shows closed ones. Newest first.
 */
function phasm_inbox_filter_list( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'phasm_message' !== $query->get( 'post_type' ) ) {
		return;
	}
	global $pagenow;
	if ( 'edit.php' !== $pagenow ) {
		return;
	}
	$closed = isset( $_GET['phasm_view'] ) && 'closed' === $_GET['phasm_view']; // phpcs:ignore WordPress.Security.NonceVerification
	if ( 'trash' !== $query->get( 'post_status' ) ) {
		$query->set(
			'meta_query',
			$closed ? array(
				array(
					'key'   => '_phasm_closed',
					'value' => '1',
				),
			) : array( phasm_open_meta_query() )
		);
	}
	if ( ! isset( $_GET['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}
}
add_action( 'pre_get_posts', 'phasm_inbox_filter_list' );

/**
 * Page titles and views for the two lists.
 */
add_filter(
	'views_edit-phasm_message',
	function ( $views ) {
		return isset( $views['trash'] ) ? array( 'trash' => $views['trash'] ) : array();
	}
);

add_action(
	'admin_head-edit.php',
	function () {
		$screen = get_current_screen();
		if ( $screen && 'phasm_message' === $screen->post_type && isset( $_GET['phasm_view'] ) && 'closed' === $_GET['phasm_view'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			global $post_type_object;
			$post_type_object->labels->name = __( 'Closed messages', 'phasm' );
		}
	}
);

/**
 * List columns.
 */
add_filter(
	'manage_phasm_message_posts_columns',
	function () {
		return array(
			'cb'            => '<input type="checkbox" />',
			'title'         => __( 'From', 'phasm' ),
			'phasm_email'   => __( 'E-mail', 'phasm' ),
			'phasm_phone'   => __( 'Phone', 'phasm' ),
			'phasm_message' => __( 'Message', 'phasm' ),
			'phasm_status'  => __( 'Answered', 'phasm' ),
			'date'          => __( 'Received', 'phasm' ),
		);
	}
);

add_action(
	'manage_phasm_message_posts_custom_column',
	function ( $column, $post_id ) {
		switch ( $column ) {
			case 'phasm_email':
				$email = get_post_meta( $post_id, '_phasm_email', true );
				echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
				break;
			case 'phasm_phone':
				echo esc_html( get_post_meta( $post_id, '_phasm_phone', true ) );
				break;
			case 'phasm_message':
				echo esc_html( wp_trim_words( get_post_field( 'post_content', $post_id ), 18 ) );
				break;
			case 'phasm_status':
				$replies = array_filter(
					(array) get_post_meta( $post_id, '_phasm_replies', true ),
					function ( $r ) {
						return ! empty( $r['ok'] );
					}
				);
				$notes   = array_filter( (array) get_post_meta( $post_id, '_phasm_notes', true ) );
				echo $replies ? esc_html( sprintf( /* translators: %d: count */ _n( 'Yes (%d)', 'Yes (%d)', count( $replies ), 'phasm' ), count( $replies ) ) ) : '–';
				if ( $notes ) {
					echo '<br><span style="color:#50575e">' . esc_html( sprintf( /* translators: %d: count */ _n( '%d note', '%d notes', count( $notes ), 'phasm' ), count( $notes ) ) ) . '</span>';
				}
				break;
		}
	},
	10,
	2
);

/**
 * Row actions: Close / Reopen. Remove Quick Edit.
 */
add_filter(
	'post_row_actions',
	function ( $actions, $post ) {
		if ( 'phasm_message' !== $post->post_type ) {
			return $actions;
		}
		unset( $actions['inline hide-if-no-js'] );
		if ( isset( $actions['edit'] ) ) {
			$actions['edit'] = '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . esc_html__( 'Open', 'phasm' ) . '</a>';
		}
		if ( 'trash' !== $post->post_status ) {
			$closed = (bool) get_post_meta( $post->ID, '_phasm_closed', true );
			$url    = wp_nonce_url( admin_url( 'admin-post.php?action=phasm_toggle_message&post=' . $post->ID ), 'phasm_toggle_' . $post->ID );
			$actions['phasm_toggle'] = '<a href="' . esc_url( $url ) . '">' . ( $closed ? esc_html__( 'Reopen', 'phasm' ) : esc_html__( 'Close', 'phasm' ) ) . '</a>';
		}
		return $actions;
	},
	10,
	2
);

add_action(
	'admin_post_phasm_toggle_message',
	function () {
		$id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
		if ( ! $id || ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'phasm_toggle_' . $id ) || 'phasm_message' !== get_post_type( $id ) ) {
			wp_die( esc_html__( 'Not allowed.', 'phasm' ) );
		}
		$closed = (bool) get_post_meta( $id, '_phasm_closed', true );
		update_post_meta( $id, '_phasm_closed', $closed ? 0 : 1 );
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=phasm_message' ) );
		exit;
	}
);

/**
 * Message screen: read-only details and a "Closed" checkbox.
 */
function phasm_message_meta_boxes() {
	remove_meta_box( 'submitdiv', 'phasm_message', 'side' );
	remove_meta_box( 'slugdiv', 'phasm_message', 'normal' );
	add_meta_box( 'phasm_message_body', __( 'Message', 'phasm' ), 'phasm_message_body_box', 'phasm_message', 'normal', 'high' );
	add_meta_box( 'phasm_message_status', __( 'Status', 'phasm' ), 'phasm_message_status_box', 'phasm_message', 'side', 'high' );
	add_meta_box( 'phasm_message_reply', __( 'Answer', 'phasm' ), 'phasm_message_reply_box', 'phasm_message', 'normal', 'default' );
	add_meta_box( 'phasm_message_notes', __( 'Notes', 'phasm' ), 'phasm_message_notes_box', 'phasm_message', 'normal', 'low' );
}
add_action( 'add_meta_boxes_phasm_message', 'phasm_message_meta_boxes' );

function phasm_message_body_box( $post ) {
	$email = get_post_meta( $post->ID, '_phasm_email', true );
	$phone = get_post_meta( $post->ID, '_phasm_phone', true );
	$mail  = get_post_meta( $post->ID, '_phasm_mail_status', true );
	$date  = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), get_post_time( 'U', true, $post ) );
	?>
	<table class="form-table" role="presentation">
		<tr><th scope="row"><?php esc_html_e( 'Name', 'phasm' ); ?></th><td><strong><?php echo esc_html( $post->post_title ); ?></strong></td></tr>
		<tr><th scope="row"><?php esc_html_e( 'E-mail', 'phasm' ); ?></th><td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td></tr>
		<tr><th scope="row"><?php esc_html_e( 'Phone', 'phasm' ); ?></th><td><?php echo $phone ? '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a>' : '–'; ?></td></tr>
		<tr><th scope="row"><?php esc_html_e( 'Received', 'phasm' ); ?></th><td><?php echo esc_html( $date ); ?></td></tr>
		<tr><th scope="row"><?php esc_html_e( 'Message', 'phasm' ); ?></th><td><div style="white-space:pre-wrap;max-width:60em;font-size:14px;line-height:1.6"><?php echo esc_html( $post->post_content ); ?></div></td></tr>
		<?php if ( $mail ) : ?>
			<tr><th scope="row"><?php esc_html_e( 'E-mail delivery', 'phasm' ); ?></th><td>
				<?php
				$ok_c = false !== strpos( $mail, 'customer:ok' );
				$ok_o = false !== strpos( $mail, 'owner:ok' );
				echo esc_html( $ok_c ? __( 'Copy to customer: sent', 'phasm' ) : __( 'Copy to customer: FAILED (check Email settings)', 'phasm' ) );
				echo '<br>';
				echo esc_html( $ok_o ? __( 'Notification to you: sent', 'phasm' ) : __( 'Notification to you: FAILED (check Email settings)', 'phasm' ) );
				?>
			</td></tr>
		<?php endif; ?>
	</table>
	<?php
}

function phasm_message_status_box( $post ) {
	$closed = (bool) get_post_meta( $post->ID, '_phasm_closed', true );
	wp_nonce_field( 'phasm_message_status', 'phasm_message_status_nonce' );
	?>
	<input type="hidden" name="post_status" value="publish">
	<p>
		<label>
			<input type="checkbox" name="phasm_closed" value="1" <?php checked( $closed ); ?>>
			<?php esc_html_e( 'Closed', 'phasm' ); ?>
		</label>
	</p>
	<p class="description"><?php esc_html_e( 'Closed messages move to Site inbox › Closed messages.', 'phasm' ); ?></p>
	<p>
		<button type="submit" class="button button-primary" name="save" value="save"><?php esc_html_e( 'Save', 'phasm' ); ?></button>
		<?php if ( current_user_can( 'delete_post', $post->ID ) ) : ?>
			<a class="submitdelete" style="margin-left:8px;color:#b32d2e" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"><?php esc_html_e( 'Move to bin', 'phasm' ); ?></a>
		<?php endif; ?>
	</p>
	<?php
}

/**
 * Answer box: write and send an answer to the customer, with the history of earlier answers.
 */
function phasm_message_reply_box( $post ) {
	$replies = (array) get_post_meta( $post->ID, '_phasm_replies', true );
	$replies = array_filter( $replies );
	$email   = get_post_meta( $post->ID, '_phasm_email', true );
	$site    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$s       = phasm_mail_settings();
	/* translators: %s: site name */
	$subject = sprintf( __( 'Re: Your enquiry – %s', 'phasm' ), $site );
	$from    = is_email( $s['from_email'] ) ? $s['from_email'] : ( is_email( $s['username'] ) ? $s['username'] : get_option( 'admin_email' ) );

	if ( $replies ) :
		?>
		<div class="phasm-thread">
			<?php foreach ( $replies as $r ) : ?>
				<div class="phasm-thread__item">
					<div class="phasm-thread__meta">
						<strong><?php echo esc_html( $r['subject'] ); ?></strong>
						<span><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $r['time'] ) ); ?> · <?php echo esc_html( $r['user'] ); ?></span>
						<?php if ( empty( $r['ok'] ) ) : ?>
							<span class="phasm-thread__failed"><?php esc_html_e( 'NOT SENT – check Email settings', 'phasm' ); ?></span>
						<?php endif; ?>
					</div>
					<div class="phasm-thread__text"><?php echo esc_html( $r['text'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<p class="phasm-reply__to">
		<?php
		/* translators: 1: customer e-mail, 2: sender e-mail */
		echo esc_html( sprintf( __( 'To: %1$s · From: %2$s', 'phasm' ), $email, $from ) );
		?>
	</p>
	<p>
		<label for="phasm-reply-subject"><strong><?php esc_html_e( 'Subject', 'phasm' ); ?></strong></label><br>
		<input type="text" id="phasm-reply-subject" name="phasm_reply_subject" class="large-text" value="<?php echo esc_attr( $subject ); ?>">
	</p>
	<p>
		<label for="phasm-reply-text"><strong><?php esc_html_e( 'Your answer', 'phasm' ); ?></strong></label><br>
		<textarea id="phasm-reply-text" name="phasm_reply_text" class="large-text" rows="8"></textarea>
	</p>
	<p class="description">
		<?php esc_html_e( 'Sent with this signature, followed by a copy of the customer\'s message. Change it in Site inbox › Email settings.', 'phasm' ); ?>
	</p>
	<div style="white-space:pre-line;border-left:3px solid #dcdcde;padding:4px 12px;margin:0 0 12px;color:#50575e"><?php echo esc_html( $s['signature'] ); ?></div>
	<p>
		<label><input type="checkbox" name="phasm_reply_close" value="1"> <?php esc_html_e( 'Close the enquiry after sending', 'phasm' ); ?></label>
	</p>
	<p>
		<button type="submit" class="button button-primary" name="phasm_send_reply" value="1"><?php esc_html_e( 'Send answer', 'phasm' ); ?></button>
	</p>
	<?php
}

/**
 * Notes box: internal notes, never sent to the customer.
 */
function phasm_message_notes_box( $post ) {
	$notes = array_filter( (array) get_post_meta( $post->ID, '_phasm_notes', true ) );
	if ( $notes ) :
		?>
		<ul class="phasm-notes">
			<?php foreach ( $notes as $i => $n ) : ?>
				<li>
					<div class="phasm-notes__meta">
						<?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $n['time'] ) ); ?> · <?php echo esc_html( $n['user'] ); ?>
						<label class="phasm-notes__delete"><input type="checkbox" name="phasm_delete_notes[]" value="<?php echo (int) $i; ?>"> <?php esc_html_e( 'Delete', 'phasm' ); ?></label>
					</div>
					<div class="phasm-notes__text"><?php echo esc_html( $n['text'] ); ?></div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<p>
		<label for="phasm-note"><strong><?php esc_html_e( 'Add a note', 'phasm' ); ?></strong></label><br>
		<textarea id="phasm-note" name="phasm_note" class="large-text" rows="3" placeholder="<?php esc_attr_e( 'Only visible here, never sent to the customer.', 'phasm' ); ?>"></textarea>
	</p>
	<p><button type="submit" class="button" name="phasm_add_note" value="1"><?php esc_html_e( 'Add note', 'phasm' ); ?></button></p>
	<?php
}

/**
 * Styles for the message screen.
 */
add_action(
	'admin_head-post.php',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'phasm_message' !== $screen->post_type ) {
			return;
		}
		echo '<style>
		.phasm-thread{margin:0 0 16px;display:flex;flex-direction:column;gap:10px}
		.phasm-thread__item{border:1px solid #dcdcde;border-left:3px solid #007A4D;background:#f6fbf8;padding:10px 12px}
		.phasm-thread__meta{display:flex;flex-wrap:wrap;gap:4px 12px;font-size:12px;color:#50575e;margin-bottom:6px}
		.phasm-thread__meta strong{color:#1d2327}
		.phasm-thread__failed{color:#b32d2e;font-weight:600}
		.phasm-thread__text,.phasm-notes__text{white-space:pre-wrap;font-size:13px;line-height:1.6}
		.phasm-reply__to{color:#50575e}
		.phasm-notes{margin:0 0 12px}
		.phasm-notes li{border:1px solid #dcdcde;background:#fcf9e8;padding:8px 12px;margin:0 0 8px}
		.phasm-notes__meta{display:flex;justify-content:space-between;font-size:12px;color:#50575e;margin-bottom:4px}
		.phasm-notes__delete{font-size:12px}
		</style>';
	}
);

/**
 * Save: Closed checkbox, notes, and sending an answer.
 */
function phasm_message_save( $post_id ) {
	static $done = array();
	if ( isset( $done[ $post_id ] ) ) {
		return;
	}
	if ( ! isset( $_POST['phasm_message_status_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['phasm_message_status_nonce'] ) ), 'phasm_message_status' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$done[ $post_id ] = true;
	$user             = wp_get_current_user()->display_name;
	$result           = array();

	// Notes: delete ticked ones, then add a new one.
	$notes = array_values( array_filter( (array) get_post_meta( $post_id, '_phasm_notes', true ) ) );
	if ( ! empty( $_POST['phasm_delete_notes'] ) ) {
		$del   = array_map( 'intval', (array) wp_unslash( $_POST['phasm_delete_notes'] ) );
		$notes = array_values( array_diff_key( $notes, array_flip( $del ) ) );
	}
	$note = isset( $_POST['phasm_note'] ) ? trim( sanitize_textarea_field( wp_unslash( $_POST['phasm_note'] ) ) ) : '';
	if ( '' !== $note ) {
		$notes[]          = array(
			'time' => time(),
			'user' => $user,
			'text' => $note,
		);
		$result['note'] = 1;
	}
	update_post_meta( $post_id, '_phasm_notes', $notes );

	$closed = empty( $_POST['phasm_closed'] ) ? 0 : 1;

	// Answer.
	$text = isset( $_POST['phasm_reply_text'] ) ? trim( sanitize_textarea_field( wp_unslash( $_POST['phasm_reply_text'] ) ) ) : '';
	if ( ! empty( $_POST['phasm_send_reply'] ) ) {
		if ( '' === $text ) {
			$result['reply'] = 'empty';
		} else {
			$subject = isset( $_POST['phasm_reply_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['phasm_reply_subject'] ) ) : '';
			$ok      = phasm_send_reply( $post_id, $subject, $text );
			$replies   = array_values( array_filter( (array) get_post_meta( $post_id, '_phasm_replies', true ) ) );
			$replies[] = array(
				'time'    => time(),
				'user'    => $user,
				'subject' => $subject,
				'text'    => $text,
				'ok'      => $ok ? 1 : 0,
			);
			update_post_meta( $post_id, '_phasm_replies', $replies );
			$result['reply'] = $ok ? 'sent' : 'failed';
			if ( $ok && ! empty( $_POST['phasm_reply_close'] ) ) {
				$closed = 1;
			}
		}
	}

	update_post_meta( $post_id, '_phasm_closed', $closed );
	set_transient( 'phasm_msg_result_' . get_current_user_id(), $result, 60 );
}
add_action( 'save_post_phasm_message', 'phasm_message_save' );

/**
 * Send an answer to the customer: answer + signature + quoted original message.
 */
function phasm_send_reply( $post_id, $subject, $text ) {
	$post  = get_post( $post_id );
	$email = get_post_meta( $post_id, '_phasm_email', true );
	if ( ! is_email( $email ) ) {
		return false;
	}
	$s    = phasm_mail_settings();
	$date = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), get_post_time( 'U', true, $post ) );
	if ( '' === $subject ) {
		/* translators: %s: site name */
		$subject = sprintf( __( 'Re: Your enquiry – %s', 'phasm' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	}
	$quoted = '> ' . str_replace( "\n", "\n> ", $post->post_content );
	$body   = $text . "\n\n" . $s['signature'] . "\n\n\n"
		/* translators: 1: date, 2: customer name */
		. sprintf( __( 'On %1$s, %2$s wrote:', 'phasm' ), $date, $post->post_title ) . "\n" . $quoted;
	return phasm_mail( $email, $subject, $body );
}

/**
 * After saving: stay on the message when a note or answer was added,
 * otherwise go back to the list the message now belongs to.
 */
add_filter(
	'redirect_post_location',
	function ( $location, $post_id ) {
		if ( 'phasm_message' !== get_post_type( $post_id ) ) {
			return $location;
		}
		$result = get_transient( 'phasm_msg_result_' . get_current_user_id() );
		if ( ! empty( $result['reply'] ) || ! empty( $result['note'] ) ) {
			return admin_url( 'post.php?post=' . $post_id . '&action=edit' );
		}
		$closed = (bool) get_post_meta( $post_id, '_phasm_closed', true );
		return admin_url( 'edit.php?post_type=phasm_message' . ( $closed ? '&phasm_view=closed' : '' ) );
	},
	10,
	2
);

/**
 * Notice after sending an answer or adding a note.
 */
add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'phasm_message' !== $screen->post_type ) {
			return;
		}
		$key    = 'phasm_msg_result_' . get_current_user_id();
		$result = get_transient( $key );
		if ( ! $result ) {
			return;
		}
		delete_transient( $key );
		if ( isset( $result['reply'] ) ) {
			if ( 'sent' === $result['reply'] ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Your answer was sent to the customer.', 'phasm' ) . '</p></div>';
			} elseif ( 'failed' === $result['reply'] ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'The answer could not be sent. It is saved below; check Site inbox › Email settings and try again.', 'phasm' ) . ' <code>' . esc_html( (string) get_transient( 'phasm_last_mail_error' ) ) . '</code></p></div>';
			} else {
				echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'Write an answer before pressing Send answer.', 'phasm' ) . '</p></div>';
			}
		}
		if ( ! empty( $result['note'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Note added.', 'phasm' ) . '</p></div>';
		}
	}
);
