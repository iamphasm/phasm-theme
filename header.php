<?php
/**
 * Site header.
 *
 * @package phasm
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> id="top">
<?php wp_body_open(); ?>
<a class="screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'phasm' ); ?></a>

<header class="site-header">
	<div class="container site-header__inner">
		<a class="site-branding" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php phasm_logo( 40 ); ?>
			<?php if ( phasm_show_site_title() ) : ?>
				<span class="site-title"><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>
		</a>

		<button class="menu-toggle" aria-controls="primary-nav" aria-expanded="false">
			<?php echo phasm_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'phasm' ); ?></span>
		</button>

		<nav id="primary-nav" class="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'phasm' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_id'        => 'primary-menu',
					'depth'          => 2,
					'fallback_cb'    => 'phasm_menu_fallback',
				)
			);
			?>
		</nav>
	</div>
</header>

<div id="content" class="site-content">
