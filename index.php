<?php
/**
 * Blog index, archives and search results.
 *
 * @package phasm
 */

get_header();

if ( is_home() ) {
	$phasm_title = get_option( 'page_for_posts' ) ? get_the_title( get_option( 'page_for_posts' ) ) : __( 'Insights', 'phasm' );
	$phasm_desc  = get_bloginfo( 'description' );
} elseif ( is_search() ) {
	/* translators: %s: search query */
	$phasm_title = sprintf( __( 'Search: %s', 'phasm' ), get_search_query() );
	$phasm_desc  = '';
} else {
	$phasm_title = wp_strip_all_tags( get_the_archive_title() );
	$phasm_desc  = get_the_archive_description();
}
$phasm_blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
?>

<section class="page-head band-dark">
	<div class="container page-head__inner">
		<nav class="breadcrumb label" aria-label="<?php esc_attr_e( 'Breadcrumb', 'phasm' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'phasm' ); ?></a>
			<span aria-hidden="true">/</span>
			<?php if ( is_home() ) : ?>
				<span><?php echo esc_html( $phasm_title ); ?></span>
			<?php else : ?>
				<a href="<?php echo esc_url( $phasm_blog_url ); ?>"><?php esc_html_e( 'Insights', 'phasm' ); ?></a>
				<span aria-hidden="true">/</span>
				<span><?php echo esc_html( $phasm_title ); ?></span>
			<?php endif; ?>
		</nav>
		<div class="page-head__row">
			<div>
				<h1 class="heading-1"><?php echo esc_html( $phasm_title ); ?></h1>
				<?php if ( $phasm_desc ) : ?>
					<div class="archive-description"><?php echo wp_kses_post( wpautop( $phasm_desc ) ); ?></div>
				<?php endif; ?>
			</div>
			<?php get_search_form(); ?>
		</div>
		<?php phasm_category_chips(); ?>
	</div>
</section>

<main class="container archive-main">
	<?php if ( have_posts() ) : ?>
		<?php
		$phasm_first = true;
		$phasm_open  = false;
		while ( have_posts() ) :
			the_post();

			// First post on page 1 of the blog is shown as a wide featured card.
			if ( $phasm_first && is_home() && ! is_paged() ) :
				$phasm_first = false;
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card post-card--featured' ); ?>>
					<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'large', array( 'alt' => '' ) );
						}
						?>
					</a>
					<div class="post-card__body">
						<?php phasm_post_meta( __( 'Featured', 'phasm' ) ); ?>
						<h2 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<div class="post-card__excerpt muted"><?php the_excerpt(); ?></div>
						<a class="btn btn--primary" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read post', 'phasm' ); ?> <?php echo phasm_icon( 'arrow-right' ); // phpcs:ignore ?></a>
					</div>
				</article>
				<?php
				continue;
			endif;
			$phasm_first = false;

			if ( ! $phasm_open ) {
				echo '<div class="grid-3">';
				$phasm_open = true;
			}
			get_template_part( 'template-parts/content', 'card' );
		endwhile;

		if ( $phasm_open ) {
			echo '</div>';
		}

		phasm_pagination();
		?>
	<?php else : ?>
		<div class="empty-state">
			<?php phasm_trace_divider(); ?>
			<h2 class="heading-2"><?php esc_html_e( 'Nothing found', 'phasm' ); ?></h2>
			<p class="muted"><?php esc_html_e( 'No posts match this view yet. Try another search or category.', 'phasm' ); ?></p>
		</div>
	<?php endif; ?>
</main>

<?php
get_footer();
