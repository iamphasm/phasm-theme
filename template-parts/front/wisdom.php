<?php
/**
 * Front page module: Daily wisdom.
 * Shows one quote per day, rotating through the quotes added in
 * Customize › PHASM front page › Daily wisdom.
 *
 * @package phasm
 */

$phasm_quote = phasm_daily_quote();
if ( ! $phasm_quote ) {
	return;
}
?>
<section id="daily-wisdom" class="section wisdom">
	<div class="container">
		<figure class="wisdom__card">
			<svg class="wisdom__trace" viewBox="0 0 240 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><circle cx="6" cy="8" r="4" fill="currentColor"/><path d="M10 8h120l8-6h96"/></svg>
			<div class="label label--accent"><?php echo esc_html( phasm_mod( 'wisdom_label' ) ); ?></div>
			<blockquote class="wisdom__quote">
				<p><?php echo esc_html( $phasm_quote['quote'] ); ?></p>
			</blockquote>
			<?php if ( '' !== $phasm_quote['author'] ) : ?>
				<figcaption class="wisdom__author">— <?php echo esc_html( $phasm_quote['author'] ); ?></figcaption>
			<?php endif; ?>
		</figure>
	</div>
</section>
