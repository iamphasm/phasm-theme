<?php
/**
 * Front page module: Insights.
 * The three latest posts. Hidden when there are no posts.
 *
 * @package phasm
 */

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
<?php
endif;
