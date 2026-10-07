<?php
/**
 * Comments.
 *
 * @package phasm
 */

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="heading-2">
			<?php
			/* translators: %d: number of comments */
			echo esc_html( sprintf( _n( '%d comment', '%d comments', get_comments_number(), 'phasm' ), get_comments_number() ) );
			?>
		</h2>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 32,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'        => __( 'Leave a comment', 'phasm' ),
			'title_reply_before' => '<h2 id="reply-title" class="heading-2">',
			'title_reply_after'  => '</h2>',
			'label_submit'       => __( 'Post comment', 'phasm' ),
			'class_submit'       => 'submit',
		)
	);
	?>
</section>
