<?php
/**
 * Post card used on the front page, blog, archives and search.
 *
 * @package phasm
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
	<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'phasm-card', array( 'alt' => '' ) );
		}
		?>
	</a>
	<div class="post-card__body">
		<?php phasm_post_meta(); ?>
		<h3 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<div class="post-card__excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
