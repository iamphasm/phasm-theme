<?php
/**
 * PHASM theme functions.
 *
 * @package phasm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHASM_VERSION', '1.5.0' ); // Keep in sync with "Version" in style.css.

/**
 * Theme setup.
 */
function phasm_setup() {
	load_theme_textdomain( 'phasm', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support(
		'editor-color-palette',
		array(
			array( 'name' => __( 'Ink', 'phasm' ), 'slug' => 'ink', 'color' => '#000000' ),
			array( 'name' => __( 'White', 'phasm' ), 'slug' => 'white', 'color' => '#ffffff' ),
			array( 'name' => __( 'Night', 'phasm' ), 'slug' => 'night', 'color' => '#0B0F14' ),
			array( 'name' => __( 'Signal', 'phasm' ), 'slug' => 'signal', 'color' => '#00E08A' ),
			array( 'name' => __( 'Signal deep', 'phasm' ), 'slug' => 'signal-deep', 'color' => '#007A4D' ),
			array( 'name' => __( 'Muted', 'phasm' ), 'slug' => 'muted', 'color' => '#505050' ),
			array( 'name' => __( 'Panel', 'phasm' ), 'slug' => 'panel', 'color' => '#f4f4f4' ),
		)
	);

	add_image_size( 'phasm-card', 768, 432, true );
	add_image_size( 'phasm-wide', 1200, 514, true );

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'phasm' ),
			'footer'  => __( 'Footer menu', 'phasm' ),
		)
	);

	add_editor_style( array( phasm_fonts_url(), 'style.css' ) );
}
add_action( 'after_setup_theme', 'phasm_setup' );

/**
 * Google Fonts URL (Michroma for display, Inter for text).
 */
function phasm_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Michroma&display=swap';
}

/**
 * Enqueue styles and scripts.
 */
function phasm_assets() {
	wp_enqueue_style( 'phasm-fonts', phasm_fonts_url(), array(), null );
	wp_enqueue_style( 'phasm-style', get_stylesheet_uri(), array( 'phasm-fonts' ), PHASM_VERSION );
	wp_enqueue_script( 'phasm-nav', get_template_directory_uri() . '/assets/js/navigation.js', array(), PHASM_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'phasm_assets' );

/**
 * Preconnect to Google Fonts.
 */
function phasm_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'phasm_resource_hints', 10, 2 );

/**
 * Excerpt length and "more" string.
 */
add_filter( 'excerpt_length', function () { return 25; } );
add_filter( 'excerpt_more', function () { return '…'; } );

/**
 * Menu item class "menu-cta" turns a link into the header button.
 * Add it under Appearance > Menus > Screen Options > CSS Classes.
 */

/**
 * Fallback primary menu when none is assigned.
 */
function phasm_menu_fallback( $args = array() ) {
	echo ! empty( $args['menu_id'] ) ? '<ul id="' . esc_attr( $args['menu_id'] ) . '">' : '<ul>';
	echo '<li class="' . ( is_front_page() ? 'current-menu-item' : '' ) . '"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'phasm' ) . '</a></li>';
	$posts_page = (int) get_option( 'page_for_posts' );
	if ( $posts_page ) {
		echo '<li class="' . ( is_home() ? 'current-menu-item' : '' ) . '"><a href="' . esc_url( get_permalink( $posts_page ) ) . '">' . esc_html( get_the_title( $posts_page ) ) . '</a></li>';
	}
	wp_list_pages( array( 'title_li' => '', 'depth' => 1, 'exclude' => $posts_page ) );
	echo '</ul>';
}

/**
 * Site logo: custom logo if set, otherwise the bundled PHASM wordmark.
 */
function phasm_logo( $height = 40 ) {
	if ( has_custom_logo() ) {
		$logo_id = get_theme_mod( 'custom_logo' );
		echo wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'custom-logo', 'alt' => get_bloginfo( 'name' ), 'style' => 'height:' . (int) $height . 'px;width:auto' ) );
		return;
	}
	printf(
		'<img src="%1$s" alt="%2$s" width="765" height="190" style="height:%3$dpx;width:auto">',
		esc_url( get_template_directory_uri() . '/assets/images/phasm-logo.png' ),
		esc_attr( get_bloginfo( 'name' ) ),
		(int) $height
	);
}

/**
 * Whether the site title is shown next to the logo (Customize > Site Identity).
 * Off by default, because the PHASM logo already shows the name.
 */
function phasm_show_site_title() {
	return (bool) get_theme_mod( 'phasm_show_site_title', false );
}

/**
 * Inline SVG icons (2px stroke, square caps).
 */
function phasm_icon( $name ) {
	$paths = array(
		'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'arrow-left'  => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
		'search'      => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>',
		'menu'        => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		// Service icons (selectable in the Customizer).
		'shield'      => '<path d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7z"/>',
		'shield-check' => '<path d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7z"/><path d="M9 12l2 2 4-4"/>',
		'lock'        => '<rect x="5" y="11" width="14" height="10"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
		'key'         => '<circle cx="8" cy="15" r="4"/><path d="M11 12l9-9M17 6l3 3M15 8l2 2"/>',
		'fingerprint' => '<path d="M12 11v4c0 2-1 4-2 5"/><path d="M8 9a4 4 0 0 1 8 0v4c0 3-1 6-3 8"/><path d="M5 12V9a7 7 0 0 1 14 0v3"/><path d="M5 16c0 1-.5 2-1 3"/>',
		'eye'         => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
		'bug'         => '<rect x="7" y="8" width="10" height="12" rx="5"/><path d="M12 8v12M9 5l1.5 3M15 5l-1.5 3M3 13h4M17 13h4M4 8l3 2M20 8l-3 2M4 19l3-2M20 19l-3-2"/>',
		'alert'       => '<path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18v.01"/>',
		'terminal'    => '<rect x="3" y="4" width="18" height="16"/><path d="M7 9l3 3-3 3M13 15h4"/>',
		'code'        => '<path d="M8 7l-5 5 5 5M16 7l5 5-5 5M14 4l-4 16"/>',
		'monitor'     => '<rect x="3" y="4" width="18" height="12"/><path d="M8 20h8M12 16v4"/>',
		'server'      => '<rect x="3" y="4" width="18" height="7"/><rect x="3" y="13" width="18" height="7"/><path d="M7 7.5h.01M7 16.5h.01"/>',
		'database'    => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
		'cloud'       => '<path d="M7 18h10a4 4 0 0 0 .5-8 6 6 0 0 0-11.5 1.5A3.5 3.5 0 0 0 7 18z"/>',
		'network'     => '<rect x="9" y="2" width="6" height="5"/><rect x="2" y="17" width="6" height="5"/><rect x="16" y="17" width="6" height="5"/><path d="M12 7v5M5 17v-5h14v5"/>',
		'wifi'        => '<path d="M2 9a15 15 0 0 1 20 0M5 13a10 10 0 0 1 14 0M8.5 16.5a5 5 0 0 1 7 0M12 20h.01"/>',
		'cpu'         => '<rect x="6" y="6" width="12" height="12"/><rect x="9" y="9" width="6" height="6"/><path d="M9 2v4M15 2v4M9 18v4M15 18v4M2 9h4M2 15h4M18 9h4M18 15h4"/>',
		'circuit'     => '<circle cx="6" cy="6" r="2"/><circle cx="18" cy="18" r="2"/><path d="M8 6h6l4 4v6"/><path d="M6 8v8h8"/>',
		'book'        => '<path d="M4 4h6a2 2 0 0 1 2 2v14a2 2 0 0 0-2-2H4z"/><path d="M20 4h-6a2 2 0 0 0-2 2v14a2 2 0 0 1 2-2h6z"/>',
		'graduation'  => '<path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c3 2 9 2 12 0v-5M22 9v6"/>',
		'flag'        => '<path d="M5 21V4M5 4h12l-2 4 2 4H5"/>',
		'target'      => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
		'users'       => '<circle cx="9" cy="8" r="3"/><path d="M3 20v-1a5 5 0 0 1 10 0v1"/><circle cx="17" cy="9" r="2.5"/><path d="M15 14.5a4 4 0 0 1 6 3.5v2"/>',
		'chat'        => '<path d="M4 4h16v12H9l-5 4z"/>',
		'tools'       => '<path d="M14 6a4 4 0 0 0 5 5l-9 9-3-3 9-9a4 4 0 0 0-2-2z"/><path d="M5 3l3 3-2 2-3-3"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/**
 * Icons offered for the three service boxes (slug => label).
 * To add one: add its SVG path to phasm_icon() and a label here.
 */
function phasm_service_icons() {
	return array(
		'shield'       => __( 'Shield', 'phasm' ),
		'shield-check' => __( 'Shield with check', 'phasm' ),
		'lock'         => __( 'Lock', 'phasm' ),
		'key'          => __( 'Key', 'phasm' ),
		'fingerprint'  => __( 'Fingerprint', 'phasm' ),
		'eye'          => __( 'Eye / monitoring', 'phasm' ),
		'bug'          => __( 'Bug', 'phasm' ),
		'alert'        => __( 'Alert', 'phasm' ),
		'terminal'     => __( 'Terminal', 'phasm' ),
		'code'         => __( 'Code', 'phasm' ),
		'monitor'      => __( 'Monitor', 'phasm' ),
		'server'       => __( 'Server', 'phasm' ),
		'database'     => __( 'Database', 'phasm' ),
		'cloud'        => __( 'Cloud', 'phasm' ),
		'network'      => __( 'Network', 'phasm' ),
		'wifi'         => __( 'Wi-Fi', 'phasm' ),
		'cpu'          => __( 'CPU / chip', 'phasm' ),
		'circuit'      => __( 'Circuit', 'phasm' ),
		'book'         => __( 'Book', 'phasm' ),
		'graduation'   => __( 'Graduation cap', 'phasm' ),
		'flag'         => __( 'Flag / CTF', 'phasm' ),
		'target'       => __( 'Target', 'phasm' ),
		'users'        => __( 'Users / team', 'phasm' ),
		'chat'         => __( 'Chat / support', 'phasm' ),
		'tools'        => __( 'Tools', 'phasm' ),
	);
}

/**
 * Trace divider (the wordmark's circuit motif).
 */
function phasm_trace_divider() {
	echo '<svg class="trace-divider" viewBox="0 0 1072 16" preserveAspectRatio="none" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M6 8h400l8-6h244l8 6h400"/><circle cx="6" cy="8" r="4" fill="currentColor"/><circle cx="1066" cy="8" r="4" fill="currentColor"/></svg>';
}

/**
 * Post meta line: category · date.
 */
function phasm_post_meta( $prefix = '' ) {
	$cats = get_the_category();
	echo '<div class="meta label">';
	if ( $prefix ) {
		echo '<span>' . esc_html( $prefix ) . '</span><span aria-hidden="true">·</span>';
	}
	if ( ! empty( $cats ) ) {
		echo '<a href="' . esc_url( get_category_link( $cats[0]->term_id ) ) . '">' . esc_html( $cats[0]->name ) . '</a><span aria-hidden="true">·</span>';
	}
	echo '<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>';
	echo '</div>';
}

/**
 * Estimated reading time in minutes.
 */
function phasm_reading_time( $post_id = null ) {
	$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );
	return max( 1, (int) ceil( $words / 220 ) );
}

/**
 * Category chips for the blog/archive header.
 */
function phasm_category_chips() {
	$cats = get_categories( array( 'hide_empty' => true, 'number' => 8 ) );
	if ( empty( $cats ) ) {
		return;
	}
	$blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
	echo '<nav class="chips" aria-label="' . esc_attr__( 'Categories', 'phasm' ) . '">';
	printf(
		'<a class="chip%s" href="%s">%s</a>',
		is_home() ? ' is-active' : '',
		esc_url( $blog_url ),
		esc_html__( 'All', 'phasm' )
	);
	foreach ( $cats as $cat ) {
		printf(
			'<a class="chip%s" href="%s">%s</a>',
			is_category( $cat->term_id ) ? ' is-active' : '',
			esc_url( get_category_link( $cat->term_id ) ),
			esc_html( $cat->name )
		);
	}
	echo '</nav>';
}

/**
 * Pagination arrows.
 */
function phasm_pagination() {
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => phasm_icon( 'arrow-left' ) . '<span class="screen-reader-text">' . __( 'Previous page', 'phasm' ) . '</span>',
			'next_text'          => phasm_icon( 'arrow-right' ) . '<span class="screen-reader-text">' . __( 'Next page', 'phasm' ) . '</span>',
			'screen_reader_text' => __( 'Posts navigation', 'phasm' ),
		)
	);
}

/**
 * Footer widget area.
 */
function phasm_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Footer', 'phasm' ),
			'id'            => 'footer-1',
			'description'   => __( 'Extra column in the footer.', 'phasm' ),
			'before_widget' => '<div id="%1$s" class="site-footer__col widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<div class="label">',
			'after_title'   => '</div>',
		)
	);
}
add_action( 'widgets_init', 'phasm_widgets_init' );

require get_template_directory() . '/inc/modules.php';
require get_template_directory() . '/inc/settings.php';
require get_template_directory() . '/inc/inbox.php';
require get_template_directory() . '/inc/contact.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/updater.php';
