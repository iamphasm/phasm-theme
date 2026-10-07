<?php
/**
 * Front page: hero, then the modules in the order and on/off state
 * chosen in Customize › PHASM front page › Modules.
 * Each module lives in template-parts/front/<id>.php.
 *
 * @package phasm
 */

get_header();

$phasm_hero_img = (int) phasm_mod( 'hero_image' );
?>

<section class="band-dark">
	<div class="container hero">
		<div class="hero__text">
			<div class="label"><?php echo esc_html( phasm_mod( 'hero_overline' ) ); ?></div>
			<h1 class="display-xl"><?php echo esc_html( phasm_mod( 'hero_title' ) ); ?></h1>
			<p class="lead"><?php echo esc_html( phasm_mod( 'hero_text' ) ); ?></p>
			<div class="hero__actions">
				<a class="btn btn--primary" href="<?php echo esc_url( phasm_mod( 'hero_btn1_url' ) ); ?>"><?php echo esc_html( phasm_mod( 'hero_btn1_label' ) ); ?> <?php echo phasm_icon( 'arrow-right' ); // phpcs:ignore ?></a>
				<a class="btn btn--outline" href="<?php echo esc_url( phasm_mod( 'hero_btn2_url' ) ); ?>"><?php echo esc_html( phasm_mod( 'hero_btn2_label' ) ); ?></a>
			</div>
		</div>
		<div class="hero__media">
			<?php if ( $phasm_hero_img ) : ?>
				<?php echo wp_get_attachment_image( $phasm_hero_img, 'large', false, array( 'alt' => '' ) ); ?>
			<?php else : ?>
				<svg viewBox="0 0 480 360" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
					<path d="M40 80h120l40 40h80"/><circle cx="40" cy="80" r="5" fill="currentColor"/><circle cx="280" cy="120" r="5" fill="currentColor"/>
					<path d="M440 280H320l-40-40h-64"/><circle cx="440" cy="280" r="5" fill="currentColor"/><circle cx="216" cy="240" r="5" fill="currentColor"/>
					<path d="M240 40v56"/><circle cx="240" cy="40" r="5" fill="currentColor"/>
					<path d="M240 320v-56"/><circle cx="240" cy="320" r="5" fill="currentColor"/>
					<path d="M400 64v48l-24 24"/><circle cx="400" cy="64" r="5" fill="currentColor"/>
					<path d="M80 296v-48l24-24"/><circle cx="80" cy="296" r="5" fill="currentColor"/>
				</svg>
			<?php endif; ?>
		</div>
	</div>
</section>

<div class="container" style="padding-top:var(--space-6)"><?php phasm_trace_divider(); ?></div>

<?php
foreach ( phasm_get_modules() as $phasm_module ) {
	if ( $phasm_module['on'] ) {
		get_template_part( 'template-parts/front/' . $phasm_module['id'] );
	}
}

get_footer();
