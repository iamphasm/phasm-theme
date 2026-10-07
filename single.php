<?php
/**
 * Single post.
 *
 * @package phasm
 */

get_header();

while ( have_posts() ) :
	the_post();
	$phasm_cats     = get_the_category();
	$phasm_blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
	?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="narrow entry-header">
		<nav class="breadcrumb label label--accent" aria-label="<?php esc_attr_e( 'Breadcrumb', 'phasm' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'phasm' ); ?></a>
			<span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( $phasm_blog_url ); ?>"><?php esc_html_e( 'Insights', 'phasm' ); ?></a>
			<?php if ( ! empty( $phasm_cats ) ) : ?>
				<span aria-hidden="true">/</span>
				<a href="<?php echo esc_url( get_category_link( $phasm_cats[0]->term_id ) ); ?>"><?php echo esc_html( $phasm_cats[0]->name ); ?></a>
			<?php endif; ?>
		</nav>
		<h1 class="heading-1"><?php the_title(); ?></h1>
		<?php if ( has_excerpt() ) : ?>
			<p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
		<div class="byline">
			<?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
			<div>
				<span class="byline__name"><?php the_author_posts_link(); ?></span>
				<span class="label">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					· <?php /* translators: %d: minutes */ echo esc_html( sprintf( __( '%d min read', 'phasm' ), phasm_reading_time() ) ); ?>
				</span>
			</div>
		</div>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="container">
			<figure class="entry-figure">
				<?php the_post_thumbnail( 'phasm-wide' ); ?>
				<?php if ( get_the_post_thumbnail_caption() ) : ?>
					<figcaption><?php the_post_thumbnail_caption(); ?></figcaption>
				<?php endif; ?>
			</figure>
		</div>
	<?php endif; ?>

	<div class="narrow">
		<div class="entry-content">
			<?php
			the_content();
			wp_link_pages( array( 'before' => '<nav class="page-links">' . __( 'Pages:', 'phasm' ), 'after' => '</nav>' ) );
			?>
		</div>

		<footer class="entry-footer">
			<?php $phasm_tags = get_the_tags(); ?>
			<?php if ( $phasm_tags ) : ?>
				<div class="tags">
					<span class="label"><?php esc_html_e( 'Tags', 'phasm' ); ?></span>
					<?php foreach ( $phasm_tags as $phasm_tag ) : ?>
						<a class="chip" href="<?php echo esc_url( get_tag_link( $phasm_tag->term_id ) ); ?>"><?php echo esc_html( $phasm_tag->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<aside class="author-box">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 56 ); ?>
				<div class="author-box__text">
					<span class="label"><?php esc_html_e( 'Written by', 'phasm' ); ?></span>
					<span class="author-box__name"><?php the_author(); ?></span>
					<?php if ( get_the_author_meta( 'description' ) ) : ?>
						<span class="muted"><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></span>
					<?php endif; ?>
				</div>
			</aside>

			<?php
			the_post_navigation(
				array(
					'prev_text' => '<span class="label">← ' . __( 'Previous', 'phasm' ) . '</span><span class="nav-title">%title</span>',
					'next_text' => '<span class="label">' . __( 'Next', 'phasm' ) . ' →</span><span class="nav-title">%title</span>',
				)
			);

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</footer>
	</div>
</article>
	<?php
endwhile;

get_footer();
