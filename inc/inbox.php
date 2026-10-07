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
 * Save the "Closed" checkbox.
 */
function phasm_message_save( $post_id ) {
	if ( ! isset( $_POST['phasm_message_status_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['phasm_message_status_nonce'] ) ), 'phasm_message_status' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	update_post_meta( $post_id, '_phasm_closed', empty( $_POST['phasm_closed'] ) ? 0 : 1 );
}
add_action( 'save_post_phasm_message', 'phasm_message_save' );

/**
 * After saving, go back to the list the message now belongs to.
 */
add_filter(
	'redirect_post_location',
	function ( $location, $post_id ) {
		if ( 'phasm_message' === get_post_type( $post_id ) ) {
			$closed = (bool) get_post_meta( $post_id, '_phasm_closed', true );
			return admin_url( 'edit.php?post_type=phasm_message' . ( $closed ? '&phasm_view=closed' : '' ) );
		}
		return $location;
	},
	10,
	2
);
