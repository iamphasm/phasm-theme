<?php
/**
 * Static page.
 *
 * @package phasm
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<section class="page-head band-dark">
		<div class="container page-head__inner">
			<nav class="breadcrumb label" aria-label="<?php esc_attr_e( 'Breadcrumb', 'phasm' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'phasm' ); ?></a>
				<span aria-hidden="true">/</span>
				<span><?php the_title(); ?></span>
			</nav>
			<h1 class="heading-1"><?php the_title(); ?></h1>
		</div>
	</section>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="container" style="padding-top:var(--space-5)">
			<figure class="entry-figure"><?php the_post_thumbnail( 'phasm-wide' ); ?></figure>
		</div>
	<?php endif; ?>

	<div class="narrow">
		<div class="entry-content">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>
		<?php
		if ( comments_open() || get_comments_number() ) {
			echo '<div class="entry-footer">';
			comments_template();
			echo '</div>';
		}
		?>
	</div>
</article>
	<?php
endwhile;

get_footer();
