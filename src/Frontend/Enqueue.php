<?php

namespace Afca\Plugins\ProHitTracker\Frontend;

use Afca\Plugins\ProHitTracker\Support\PostTypes;

class Enqueue {

	public function register() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	public function enqueue() {
		if ( ! is_singular() ) {
			return;
		}

		$post       = get_queried_object();
		$post_types = PostTypes::get();

		if ( ! $post || ! in_array( $post->post_type, $post_types, true ) ) {
			return;
		}

		wp_enqueue_script(
			'pht-tracker',
			PHT_URL . 'assets/js/tracker.js',
			[],
			PHT_VERSION,
			true
		);

		wp_localize_script(
			'pht-tracker',
			'phtData',
			[
				'restUrl'  => rest_url( 'pht/v1/track' ),
				'nonceUrl' => rest_url( 'pht/v1/nonce' ),
				'postId'   => get_queried_object_id(),
			]
		);
	}
}
