<?php

namespace Afca\Plugins\ProHitTracker\Tracker;

use Afca\Plugins\ProHitTracker\Support\Helpers;

class SpamGuard {

	public function is_bot() {
		return Helpers::is_bot();
	}

	public function is_rate_limited( $request, int $post_id ) {
		$key   = 'pht_rate_' . md5(
			Helpers::client_ip() . ':' . $request->get_route() . ':' . $post_id
		);
		$count = (int) get_transient( $key );

		if ( $count >= 60 ) {
			return true;
		}

		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
		return false;
	}

	public function is_valid_origin( \WP_REST_Request $request ) {
		$host = parse_url( home_url(), PHP_URL_HOST );

		$origin  = $request->get_header( 'origin' );
		$referer = $request->get_header( 'referer' );

		$origin_host  = $origin ? parse_url( $origin, PHP_URL_HOST ) : null;
		$referer_host = $referer ? parse_url( $referer, PHP_URL_HOST ) : null;

		return $origin_host === $host || $referer_host === $host;
	}
}
