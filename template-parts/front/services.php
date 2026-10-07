<?php
/**
 * Front page module: What we do (services).
 * Three service boxes with selectable icons.
 *
 * @package phasm
 */

?>
<section id="services" class="section">
	<div class="container">
		<div class="section__head">
			<div>
				<div class="label label--accent"><?php esc_html_e( 'Services', 'phasm' ); ?></div>
				<h2 class="section-title"><?php echo esc_html( phasm_mod( 'services_title' ) ); ?></h2>
			</div>
			<p><?php echo esc_html( phasm_mod( 'services_intro' ) ); ?></p>
		</div>
		<div class="grid-3">
			<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<?php $phasm_url = phasm_mod( "service_{$i}_url" ); ?>
				<article class="service-card service-card--<?php echo (int) $i; ?>">
					<div class="service-card__top">
						<span class="service-card__icon"><?php echo phasm_icon( phasm_mod( "service_{$i}_icon" ) ); // phpcs:ignore ?></span>
						<span class="label"><?php echo esc_html( sprintf( 'Node %02d', $i ) ); ?></span>
					</div>
					<h3 class="heading-3"><?php echo esc_html( phasm_mod( "service_{$i}_title" ) ); ?></h3>
					<p><?php echo esc_html( phasm_mod( "service_{$i}_text" ) ); ?></p>
					<?php if ( $phasm_url ) : ?>
						<a class="link-arrow" href="<?php echo esc_url( $phasm_url ); ?>"><?php esc_html_e( 'Read more', 'phasm' ); ?> <?php echo phasm_icon( 'arrow-right' ); // phpcs:ignore ?></a>
					<?php endif; ?>
				</article>
			<?php endfor; ?>
		</div>
	</div>
</section>
