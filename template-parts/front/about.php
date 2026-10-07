<?php
/**
 * Front page module: About.
 * About text, plus the content of the static front page if one is set.
 *
 * @package phasm
 */

?>
<section id="about" class="section">
	<div class="container grid-2">
		<div style="display:flex;flex-direction:column;gap:var(--space-2)">
			<div class="label label--accent"><?php esc_html_e( 'About', 'phasm' ); ?></div>
			<h2 class="section-title"><?php echo esc_html( phasm_mod( 'about_title' ) ); ?></h2>
		</div>
		<div class="about__body">
			<?php echo wpautop( esc_html( phasm_mod( 'about_text' ) ) ); // phpcs:ignore ?>
			<?php
			// Optional: content of the page set as homepage (Settings › Reading).
			if ( phasm_mod( 'about_show_page_content' ) && 'page' === get_option( 'show_on_front' ) ) {
				while ( have_posts() ) {
					the_post();
					the_content();
				}
			}
			?>
		</div>
	</div>
</section>
