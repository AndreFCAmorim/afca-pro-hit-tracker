<?php

namespace Afca\Plugins\ProHitTracker\Api;

class NonceEndpoint {

	public function register(): void {
		add_action( 'rest_api_init', [ $this, 'register_route' ] );
	}

	public function register_route(): void {
		register_rest_route(
			'pht/v1',
			'/nonce',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'handle' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	public function handle(): \WP_REST_Response {
		do_action( 'litespeed_control_set_nocache', 'pht nonce endpoint' );
		return new \WP_REST_Response( [ 'nonce' => wp_create_nonce( 'wp_rest' ) ], 200 );
	}
}
