<?php
/**
 * Front page module: Start a conversation (call to action).
 * Dark band with a contact button.
 *
 * @package phasm
 */

$phasm_email = phasm_mod( 'contact_email' );
?>
<section id="contact" class="section">
	<div class="container cta-band">
		<div class="cta-band__text">
			<h2 class="section-title"><?php echo esc_html( phasm_mod( 'cta_title' ) ); ?></h2>
			<p><?php echo esc_html( phasm_mod( 'cta_text' ) ); ?></p>
		</div>
		<a class="btn btn--primary" href="<?php echo esc_url( $phasm_email ? 'mailto:' . antispambot( $phasm_email ) : home_url( '/contact/' ) ); ?>"><?php echo esc_html( phasm_mod( 'cta_label' ) ); ?> <?php echo phasm_icon( 'arrow-right' ); // phpcs:ignore ?></a>
	</div>
</section>
