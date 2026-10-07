<?php
/**
 * Site footer.
 *
 * @package phasm
 */

$phasm_email   = phasm_mod( 'contact_email' );
$phasm_phone   = phasm_mod( 'contact_phone' );
$phasm_address = phasm_mod( 'contact_address' );
?>
</div><!-- #content -->

<footer class="site-footer">
	<div class="container site-footer__grid">
		<div class="site-footer__col site-footer__brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Home', 'phasm' ); ?>"><?php phasm_logo( 32 ); ?></a>
			<p class="muted"><?php echo esc_html( phasm_mod( 'footer_text' ) ); ?></p>
		</div>

		<nav class="site-footer__col" aria-label="<?php esc_attr_e( 'Footer', 'phasm' ); ?>">
			<div class="label"><?php esc_html_e( 'Pages', 'phasm' ); ?></div>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => 'phasm_menu_fallback',
				)
			);
			?>
		</nav>

		<?php if ( $phasm_email || $phasm_phone || $phasm_address ) : ?>
			<div class="site-footer__col">
				<div class="label"><?php esc_html_e( 'Contact', 'phasm' ); ?></div>
				<?php if ( $phasm_email ) : ?>
					<a href="mailto:<?php echo esc_attr( antispambot( $phasm_email ) ); ?>"><?php echo esc_html( antispambot( $phasm_email ) ); ?></a>
				<?php endif; ?>
				<?php if ( $phasm_phone ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phasm_phone ) ); ?>"><?php echo esc_html( $phasm_phone ); ?></a>
				<?php endif; ?>
				<?php if ( $phasm_address ) : ?>
					<span class="muted"><?php echo nl2br( esc_html( $phasm_address ) ); // phpcs:ignore ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
			<?php dynamic_sidebar( 'footer-1' ); ?>
		<?php endif; ?>
	</div>

	<div class="container site-footer__bottom">
		<span>&copy; <?php echo esc_html( gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?></span>
		<a class="back-to-top" href="#top"><?php esc_html_e( 'Back to top', 'phasm' ); ?> &uarr;</a>
		<?php if ( function_exists( 'get_privacy_policy_url' ) && get_privacy_policy_url() ) : ?>
			<a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'Privacy', 'phasm' ); ?></a>
		<?php endif; ?>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
