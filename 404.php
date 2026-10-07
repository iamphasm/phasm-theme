<?php
/**
 * 404.
 *
 * @package phasm
 */

get_header();
?>
<main class="container empty-state">
	<svg viewBox="0 0 240 48" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
		<circle cx="6" cy="24" r="4" fill="currentColor"/><path d="M10 24h80l12-12h40"/><circle cx="146" cy="12" r="4" fill="currentColor"/>
		<path d="M150 12h20" stroke-dasharray="4 6"/><path d="M190 36h44"/><circle cx="234" cy="36" r="4" fill="currentColor"/>
	</svg>
	<div class="label label--accent"><?php esc_html_e( 'Error 404', 'phasm' ); ?></div>
	<h1 class="heading-1"><?php esc_html_e( 'Trace lost', 'phasm' ); ?></h1>
	<p class="muted"><?php esc_html_e( 'This page does not exist or has moved. Search, or go back to the front page.', 'phasm' ); ?></p>
	<?php get_search_form(); ?>
	<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Front page', 'phasm' ); ?> <?php echo phasm_icon( 'arrow-right' ); // phpcs:ignore ?></a>
</main>
<?php
get_footer();
