<?php

namespace Afca\Plugins\ProHitTracker\Api;

use Afca\Plugins\ProHitTracker\Support\PostTypes;
use Afca\Plugins\ProHitTracker\Tracker\SpamGuard;
use Afca\Plugins\ProHitTracker\Tracker\HitRecorder;

class TrackEndpoint {

	private SpamGuard $guard;
	private HitRecorder $recorder;

	public function __construct() {
		$this->guard    = new SpamGuard();
		$this->recorder = new HitRecorder();
	}

	public function register() {
		add_action( 'rest_api_init', [ $this, 'register_route' ] );
	}

	public function register_route() {
		register_rest_route(
			'pht/v1',
			'/track',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'handle' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'post_id' => [
						'required'          => true,
						'validate_callback' => fn( $v ) => is_numeric( $v ) && (int) $v > 0,
						'sanitize_callback' => 'absint',
					],
					'hp'      => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);
	}

	public function handle( \WP_REST_Request $request ): \WP_REST_Response {
		do_action( 'litespeed_control_set_nocache', 'pht tracker endpoint' );

		if ( ! empty( $request->get_param( 'hp' ) ) ) {
			return new \WP_REST_Response( [ 'status' => 'ok' ], 200 );
		}

		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new \WP_REST_Response( [ 'error' => 'invalid nonce' ], 403 );
		}

		if ( ! $this->guard->is_valid_origin( $request ) ) {
			return new \WP_REST_Response( [ 'error' => 'forbidden' ], 403 );
		}

		if ( $this->guard->is_bot() ) {
			return new \WP_REST_Response( [ 'status' => 'bot ignored' ], 200 );
		}

		if ( $this->guard->is_rate_limited() ) {
			return new \WP_REST_Response( [ 'error' => 'rate limit exceeded' ], 429 );
		}

		$post_id    = $request->get_param( 'post_id' );
		$post       = get_post( $post_id );
		$post_types = PostTypes::get();

		if ( ! $post || ! in_array( $post->post_type, $post_types, true ) || $post->post_status !== 'publish' ) {
			return new \WP_REST_Response( [ 'error' => 'invalid post' ], 404 );
		}

		if ( $this->recorder->already_counted( $post_id ) ) {
			return new \WP_REST_Response( [ 'status' => 'already counted' ], 200 );
		}

		$this->recorder->record( $post_id );

		return new \WP_REST_Response( [ 'status' => 'counted' ], 200 );
	}
}
