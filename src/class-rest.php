<?php
/**
 * REST functionality for AAA Option Optimizer.
 *
 * @package Emilia\OptionOptimizer
 */

namespace Emilia\OptionOptimizer;

use \WP_Error;
use \WP_REST_Request;
use \WP_REST_Response;

/**
 * REST functionality of AAA Option Optimizer.
 */
class REST {

	/**
	 * Registers hooks.
	 */
	public function register_hooks() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	/**
	 * Register the REST API routes.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		\register_rest_route(
			'aaa-option-optimizer/v1',
			'/update-autoload/(?P<option_name>[a-zA-Z0-9-_\.\:]+)',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'update_option_autoload' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'option_name' => [
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'autoload'    => [
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		\register_rest_route(
			'aaa-option-optimizer/v1',
			'/delete-option/(?P<option_name>[a-zA-Z0-9-_\.\:]+)',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'delete_option' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'option_name' => [
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);
	}

	/**
	 * Update autoload status of an option.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_option_autoload( $request ) {
		$option_name  = $request['option_name'];
		$autoload     = $request['autoload'] === 'yes' ? 'yes' : 'no';
		$option_value = get_option( $option_name );

		if ( false === $option_value ) {
			return new \WP_Error( 'option_not_found', 'Option does not exist', [ 'status' => 404 ] );
		}

		update_option( $option_name, $option_value, $autoload );
		return new \WP_REST_Response( [ 'success' => true ], 200 );
	}

	/**
	 * Delete an option.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_option( $request ) {
		$option_name = $request['option_name'];
		if ( delete_option( $option_name ) ) {
			return new \WP_REST_Response( [ 'success' => true ], 200 );
		} else {
			return new \WP_Error( 'option_not_found_or_deleted', 'Option does not exist or could not be deleted', [ 'status' => 404 ] );
		}
	}
}