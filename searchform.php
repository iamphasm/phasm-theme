<?php
/**
 * Search form.
 *
 * @package phasm
 */

$phasm_sid = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $phasm_sid ); ?>"><?php esc_html_e( 'Search posts', 'phasm' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $phasm_sid ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search posts', 'phasm' ); ?>">
	<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'phasm' ); ?>"><?php echo phasm_icon( 'search' ); // phpcs:ignore ?></button>
</form>
