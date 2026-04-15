<?php

namespace Afca\Plugins\ProHitTracker\Tracker;

use Afca\Plugins\ProHitTracker\Support\Helpers;

class SpamGuard {

	public function is_bot() {
		return Helpers::is_bot();
	}

	public function is_rate_limited() {
		$key   = 'pht_rate_' . md5( Helpers::client_ip() );
		$count = (int) get_transient( $key );

		if ( $count >= 60 ) {
			return true;
		}

		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
		return false;
	}

	public function is_valid_origin( \WP_REST_Request $request ) {
		$home    = home_url();
		$origin  = $request->get_header( 'origin' );
		$referer = $request->get_header( 'referer' );

		return ( $origin && str_starts_with( $origin, $home ) )
			|| ( $referer && str_starts_with( $referer, $home ) );
	}
}
