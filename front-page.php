<?php
/**
 * Front page: hero, services, about, latest posts, CTA.
 * Set Settings > Reading > "Your homepage displays" to a static page to use it,
 * or leave it on "latest posts" — this template is used either way.
 *
 * @package phasm
 */

get_header();

$phasm_hero_img = (int) phasm_mod( 'hero_image' );
$phasm_email    = phasm_mod( 'contact_email' );
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

<?php
$phasm_latest = new WP_Query(
	array(
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
if ( $phasm_latest->have_posts() ) :
	$phasm_blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
	?>
<section class="section">
	<div class="container">
		<div class="section__head">
			<div>
				<div class="label label--accent"><?php esc_html_e( 'Latest', 'phasm' ); ?></div>
				<h2 class="section-title"><?php esc_html_e( 'Insights', 'phasm' ); ?></h2>
			</div>
			<a class="link-arrow" href="<?php echo esc_url( $phasm_blog_url ); ?>"><?php esc_html_e( 'All posts', 'phasm' ); ?> <?php echo phasm_icon( 'arrow-right' ); // phpcs:ignore ?></a>
		</div>
		<div class="grid-3">
			<?php
			while ( $phasm_latest->have_posts() ) {
				$phasm_latest->the_post();
				get_template_part( 'template-parts/content', 'card' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<section id="contact" class="section section--inverse band-dark">
	<div class="container cta-band">
		<div class="cta-band__text">
			<h2 class="section-title"><?php echo esc_html( phasm_mod( 'cta_title' ) ); ?></h2>
			<p><?php echo esc_html( phasm_mod( 'cta_text' ) ); ?></p>
		</div>
		<a class="btn btn--primary" href="<?php echo esc_url( $phasm_email ? 'mailto:' . antispambot( $phasm_email ) : home_url( '/contact/' ) ); ?>"><?php echo esc_html( phasm_mod( 'cta_label' ) ); ?> <?php echo phasm_icon( 'arrow-right' ); // phpcs:ignore ?></a>
	</div>
</section>

<?php
get_footer();
