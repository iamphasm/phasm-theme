<?php
/**
 * Front page modules and Daily wisdom quotes.
 *
 * Both are stored as JSON strings in theme mods:
 * - phasm_modules:       [{"id":"services","on":true,"scheme":"light","divider":true}, …]  (order = display order)
 * - phasm_wisdom_quotes: [{"quote":"…","author":"…"}, …]
 *
 * @package phasm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available modules (id => label). The id is the template file in template-parts/front/.
 * To add a module: add it here and create template-parts/front/<id>.php.
 */
function phasm_module_list() {
	return array(
		'services' => __( 'What we do', 'phasm' ),
		'about'    => __( 'About', 'phasm' ),
		'wisdom'   => __( 'Daily wisdom', 'phasm' ),
		'insights' => __( 'Insights', 'phasm' ),
		'cta'      => __( 'Start a conversation', 'phasm' ),
	);
}

/**
 * Colour schemes a module can use (id => label).
 */
function phasm_module_schemes() {
	return array(
		'light' => __( 'Light', 'phasm' ),
		'grey'  => __( 'Grey', 'phasm' ),
		'dark'  => __( 'Dark (inverted)', 'phasm' ),
	);
}

/**
 * Default colour scheme per module (matches the original design).
 */
function phasm_module_default_scheme( $id ) {
	$map = array(
		'about' => 'grey',
		'cta'   => 'dark',
	);
	return isset( $map[ $id ] ) ? $map[ $id ] : 'light';
}

/**
 * Default module order and state, as JSON.
 */
function phasm_modules_default() {
	$out = array();
	foreach ( array_keys( phasm_module_list() ) as $id ) {
		$out[] = array(
			'id'     => $id,
			'on'     => true,
			'scheme'  => phasm_module_default_scheme( $id ),
			'divider' => true,
		);
	}
	return wp_json_encode( $out );
}

/**
 * Sanitize the modules JSON: known ids only, no duplicates,
 * and any module missing from the saved value is appended (switched on).
 */
function phasm_sanitize_modules( $value ) {
	$known   = phasm_module_list();
	$schemes = phasm_module_schemes();
	$data    = json_decode( (string) $value, true );
	$out   = array();
	$seen  = array();

	if ( is_array( $data ) ) {
		foreach ( $data as $row ) {
			if ( ! is_array( $row ) || empty( $row['id'] ) ) {
				continue;
			}
			$id = sanitize_key( $row['id'] );
			if ( ! isset( $known[ $id ] ) || isset( $seen[ $id ] ) ) {
				continue;
			}
			$seen[ $id ] = true;
			$scheme      = isset( $row['scheme'] ) ? sanitize_key( $row['scheme'] ) : '';
			$out[]       = array(
				'id'     => $id,
				'on'     => ! empty( $row['on'] ),
				'scheme'  => isset( $schemes[ $scheme ] ) ? $scheme : phasm_module_default_scheme( $id ),
				// Before 1.5.0 the divider was one global setting; use it when a module has no value yet.
				'divider' => isset( $row['divider'] ) ? ! empty( $row['divider'] ) : (bool) get_theme_mod( 'modules_divider', true ),
			);
		}
	}
	foreach ( array_keys( $known ) as $id ) {
		if ( ! isset( $seen[ $id ] ) ) {
			$out[] = array(
				'id'     => $id,
				'on'     => true,
				'scheme'  => phasm_module_default_scheme( $id ),
				'divider' => true,
			);
		}
	}
	return wp_json_encode( $out );
}

/**
 * Modules in display order: array of [ 'id' => string, 'on' => bool ].
 */
function phasm_get_modules() {
	$json = get_theme_mod( 'phasm_modules', phasm_modules_default() );
	return json_decode( phasm_sanitize_modules( $json ), true );
}

/**
 * Default quotes, as JSON.
 */
function phasm_wisdom_default() {
	return wp_json_encode(
		array(
			array(
				'quote'  => 'Security is a process, not a product.',
				'author' => 'Bruce Schneier',
			),
			array(
				'quote'  => 'Amateurs hack systems, professionals hack people.',
				'author' => 'Bruce Schneier',
			),
		)
	);
}

/**
 * Sanitize the quotes JSON: plain text only, empty rows dropped.
 */
function phasm_sanitize_wisdom( $value ) {
	$data = json_decode( (string) $value, true );
	$out  = array();
	if ( is_array( $data ) ) {
		foreach ( $data as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$quote  = isset( $row['quote'] ) ? sanitize_textarea_field( $row['quote'] ) : '';
			$author = isset( $row['author'] ) ? sanitize_text_field( $row['author'] ) : '';
			if ( '' === trim( $quote ) && '' === trim( $author ) ) {
				continue;
			}
			$out[] = array(
				'quote'  => $quote,
				'author' => $author,
			);
		}
	}
	return wp_json_encode( $out );
}

/**
 * All quotes that have text.
 */
function phasm_get_quotes() {
	$json   = get_theme_mod( 'phasm_wisdom_quotes', phasm_wisdom_default() );
	$quotes = json_decode( phasm_sanitize_wisdom( $json ), true );
	return array_values(
		array_filter(
			(array) $quotes,
			function ( $q ) {
				return '' !== trim( $q['quote'] );
			}
		)
	);
}

/**
 * Today's quote: the list rotates one step per day (site time zone).
 *
 * @return array|null [ 'quote' => string, 'author' => string ]
 */
function phasm_daily_quote() {
	$quotes = phasm_get_quotes();
	$count  = count( $quotes );
	if ( ! $count ) {
		return null;
	}
	$day = (int) floor( ( time() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) / DAY_IN_SECONDS );
	return $quotes[ $day % $count ];
}
