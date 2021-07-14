<?php
/**
 * Adds a REST endpoint that makes requests to the Fedora API and returns the result.
 *
 * @package MESHResearch\CommonsEmbed
 */

namespace MESHResearch\CommonsConnect;

/**
 * Actions
 */
add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest' );

/**
 * Registers REST routes.
 *
 * Routes:
 *  - /find - From search terms generated by POST request, query remote host,
 *    parse results, and return as JSON object.
 */
function register_rest() {
	register_rest_route(
		'commons-connect/v1',
		'/find',
		[
			[
				'methods'             => 'POST',
				'permission_callback' => __NAMESPACE__ . '\rest_public_permissions_check',
				'callback'            => __NAMESPACE__ . '\rest_find_callback',
			],
		]
	);

	register_rest_route(
		'commons-connect/v1',
		'/item',
		[
			[
				'methods'             => 'POST',
				'permission_callback' => __NAMESPACE__ . '\rest_public_permissions_check',
				'callback'            => __NAMESPACE__ . '\rest_item_callback',
			],
		]
	);
}

/**
 * Returns whether user has permission to make the REST request.
 *
 * We are making a call to a public API and not exposing or altering any site
 * data based on the request. This request can be initiated by any visitor to
 * the site, so must always return true.
 *
 * @return boolean True if user has permission to make the request.
 */
function rest_public_permissions_check() {
	return true;
}

/**
 * Callback for /find endpoint.
 *
 * @param \WP_REST_Request $request The REST request object.
 *
 * @return \WP_REST_Response JSON encoded data from query to remote Fedora repository.
 */
function rest_find_callback( $request ) {
	$CC_options = get_option( CC_PREFIX . 'options' );
	if ( $CC_options ) {
		$base_url = $CC_options['base_url'];
	}
	if ( ! $base_url ) {
		$base_url = $request->get_param( 'baseURL' );
	}
	$base_url = esc_url_raw( $base_url );
	if ( ! $base_url ) {
		return new \WP_Error( 'no-remote-url', 'No base URL set for Fedora repository.' );
	}

	$parameter_string = $request->get_param( 'parameterString' );
	$fetch_address    = "{$base_url}objects/?{$parameter_string}";
	$response_xml     = \simplexml_load_file( $fetch_address );

	return rest_ensure_response( $response_xml );
}

/**
 * Callback for /item endpoint.
 *
 * @param \WP_REST_Request $request The REST request object.
 *
 * @return \WP_REST_Response JSON encoded data from query to remote Fedora repository.
 */
function rest_item_callback( $request ) {
	$CC_options = get_option( CC_PREFIX . 'options' );
	if ( $CC_options ) {
		$base_url = $CC_options['base_url'];
	}
	if ( ! $base_url ) {
		$base_url = $request->get_param( 'baseURL' );
	}
	$base_url = esc_url_raw( $base_url );
	if ( ! $base_url ) {
		return new \WP_Error( 'no-remote-url', 'No base URL set for Fedora repository.' );
	}

	$pid           = $request->get_param( 'pid' );
	$fetch_address = "{$base_url}objects/{$pid}/datastreams/descMetadata/content";
	$response_xml  = \simplexml_load_file( $fetch_address );

	return rest_ensure_response( $response_xml );
}
