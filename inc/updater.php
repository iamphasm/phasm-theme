<?php
/**
 * Auto-updates from GitHub releases.
 *
 * How it works:
 * - WordPress checks for theme updates (about twice a day).
 * - This file asks GitHub for the latest release of PHASM_GITHUB_REPO.
 * - If the release tag (e.g. v1.2.0) is higher than the installed version,
 *   WordPress offers the update, and installs it automatically when
 *   auto-updates are enabled for the theme.
 * - The release must have a zip asset named phasm.zip whose top folder is "phasm/".
 *   The included GitHub Action (.github/workflows/release.yml) builds it for you.
 *
 * @package phasm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// GitHub repository "owner/name". Change this if the repo is renamed.
if ( ! defined( 'PHASM_GITHUB_REPO' ) ) {
	define( 'PHASM_GITHUB_REPO', 'iamphasm/phasm-theme' );
}

// Name of the zip asset attached to each release.
if ( ! defined( 'PHASM_RELEASE_ASSET' ) ) {
	define( 'PHASM_RELEASE_ASSET', 'phasm.zip' );
}

/**
 * Fetch the latest release from GitHub, cached for 6 hours.
 *
 * @return array|null { version, package, url } or null.
 */
function phasm_github_latest_release( $force = false ) {
	$cache_key = 'phasm_github_release';
	if ( ! $force ) {
		$cached = get_site_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached ? $cached : null;
		}
	}

	$headers = array(
		'Accept'     => 'application/vnd.github+json',
		'User-Agent' => 'PHASM-theme-updater',
	);
	// Optional: for a private repo, define PHASM_GITHUB_TOKEN in wp-config.php.
	if ( defined( 'PHASM_GITHUB_TOKEN' ) && PHASM_GITHUB_TOKEN ) {
		$headers['Authorization'] = 'Bearer ' . PHASM_GITHUB_TOKEN;
	}

	$response = wp_remote_get(
		'https://api.github.com/repos/' . PHASM_GITHUB_REPO . '/releases/latest',
		array( 'timeout' => 10, 'headers' => $headers )
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		set_site_transient( $cache_key, 0, HOUR_IN_SECONDS ); // Retry in an hour.
		return null;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $data['tag_name'] ) ) {
		set_site_transient( $cache_key, 0, HOUR_IN_SECONDS );
		return null;
	}

	$package = '';
	if ( ! empty( $data['assets'] ) ) {
		foreach ( $data['assets'] as $asset ) {
			if ( isset( $asset['name'] ) && PHASM_RELEASE_ASSET === $asset['name'] ) {
				// Private repos need the API asset URL; public repos can use the browser URL.
				$package = defined( 'PHASM_GITHUB_TOKEN' ) && PHASM_GITHUB_TOKEN ? $asset['url'] : $asset['browser_download_url'];
				break;
			}
		}
	}

	$release = array(
		'version' => ltrim( $data['tag_name'], 'vV' ),
		'package' => $package,
		'url'     => isset( $data['html_url'] ) ? $data['html_url'] : 'https://github.com/' . PHASM_GITHUB_REPO,
	);
	set_site_transient( $cache_key, $release, 6 * HOUR_IN_SECONDS );
	return $release;
}

/**
 * Tell WordPress about a newer version.
 */
function phasm_check_for_update( $transient ) {
	if ( ! is_object( $transient ) ) {
		return $transient;
	}

	$theme   = wp_get_theme( 'phasm' );
	$current = $theme->exists() ? $theme->get( 'Version' ) : PHASM_VERSION;
	$release = phasm_github_latest_release();

	$item = array(
		'theme'        => 'phasm',
		'new_version'  => $release ? $release['version'] : $current,
		'url'          => $release ? $release['url'] : '',
		'package'      => $release ? $release['package'] : '',
		'requires'     => '6.0',
		'requires_php' => '7.4',
	);

	if ( $release && $release['package'] && version_compare( $release['version'], $current, '>' ) ) {
		$transient->response['phasm'] = $item;
	} else {
		// Listing it under no_update keeps the "Enable auto-updates" link visible.
		$transient->no_update['phasm'] = $item;
		unset( $transient->response['phasm'] );
	}
	return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'phasm_check_for_update' );

/**
 * Private repos: GitHub needs the token and an octet-stream Accept header to download the asset.
 */
function phasm_github_download_auth( $args, $url ) {
	if ( defined( 'PHASM_GITHUB_TOKEN' ) && PHASM_GITHUB_TOKEN && false !== strpos( $url, 'api.github.com/repos/' . PHASM_GITHUB_REPO . '/releases/assets/' ) ) {
		$args['headers']['Authorization'] = 'Bearer ' . PHASM_GITHUB_TOKEN;
		$args['headers']['Accept']        = 'application/octet-stream';
	}
	return $args;
}
add_filter( 'http_request_args', 'phasm_github_download_auth', 10, 2 );

/**
 * Safety net: make sure the unpacked folder is named "phasm",
 * so the update replaces the theme instead of installing a copy.
 */
function phasm_fix_source_folder( $source, $remote_source, $upgrader, $hook_extra = array() ) {
	global $wp_filesystem;
	if ( empty( $hook_extra['theme'] ) || 'phasm' !== $hook_extra['theme'] ) {
		return $source;
	}
	$wanted = trailingslashit( $remote_source ) . 'phasm/';
	if ( untrailingslashit( $source ) === untrailingslashit( $wanted ) ) {
		return $source;
	}
	if ( $wp_filesystem && $wp_filesystem->move( $source, $wanted, true ) ) {
		return $wanted;
	}
	return $source;
}
add_filter( 'upgrader_source_selection', 'phasm_fix_source_folder', 10, 4 );

/**
 * Clear the cache after an update, and add a "Check for updates" link on the Themes screen.
 */
add_action(
	'upgrader_process_complete',
	function () {
		delete_site_transient( 'phasm_github_release' );
	}
);

add_action(
	'admin_init',
	function () {
		if ( isset( $_GET['phasm_check_update'] ) && current_user_can( 'update_themes' ) && check_admin_referer( 'phasm_check_update' ) ) {
			delete_site_transient( 'phasm_github_release' );
			delete_site_transient( 'update_themes' );
			wp_update_themes();
			wp_safe_redirect( admin_url( 'themes.php?phasm_checked=1' ) );
			exit;
		}
	}
);

add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'themes' !== $screen->id || ! current_user_can( 'update_themes' ) ) {
			return;
		}
		$url = wp_nonce_url( admin_url( 'themes.php?phasm_check_update=1' ), 'phasm_check_update' );
		$msg = isset( $_GET['phasm_checked'] ) ? esc_html__( 'Checked GitHub for PHASM updates.', 'phasm' ) . ' ' : '';
		printf(
			'<div class="notice notice-info is-dismissible"><p>%1$s%2$s <a href="%3$s">%4$s</a></p></div>',
			esc_html( $msg ),
			/* translators: %s: version */
			esc_html( sprintf( __( 'PHASM %s updates from GitHub.', 'phasm' ), PHASM_VERSION ) ),
			esc_url( $url ),
			esc_html__( 'Check for updates now', 'phasm' )
		);
	}
);
