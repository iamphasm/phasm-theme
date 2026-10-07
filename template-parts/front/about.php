<?php
/**
 * Front page module: About.
 * About text, plus the content of the static front page if one is set.
 *
 * @package phasm
 */

?>
<section id="about" class="section section--panel">
	<div class="container grid-2">
		<div style="display:flex;flex-direction:column;gap:var(--space-2)">
			<div class="label label--accent"><?php esc_html_e( 'About', 'phasm' ); ?></div>
			<h2 class="section-title"><?php echo esc_html( phasm_mod( 'about_title' ) ); ?></h2>
		</div>
		<div class="about__body">
			<?php echo wpautop( esc_html( phasm_mod( 'about_text' ) ) ); // phpcs:ignore ?>
			<?php
			// Content of the static front page (if any) is shown here too.
			if ( 'page' === get_option( 'show_on_front' ) ) {
				while ( have_posts() ) {
					the_post();
					the_content();
				}
			}
			?>
		</div>
	</div>
</section>
