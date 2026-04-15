<?php

namespace Afca\Plugins\ProHitTracker\Tracker;

use Afca\Plugins\ProHitTracker\Support\Helpers;

class HitRecorder {

	public function already_counted( int $post_id ) {
		$key = 'hit_' . md5( $post_id . Helpers::client_ip() . date( 'Y-m-d' ) );
		return get_transient( $key );
	}

	public function record( int $post_id ) {
		$hit_key = 'hit_' . md5( $post_id . Helpers::client_ip() . date( 'Y-m-d' ) );
		set_transient( $hit_key, 1, DAY_IN_SECONDS );

		$total = get_post_meta( $post_id, 'post_hits', true );
		update_post_meta( $post_id, 'post_hits', $total + 1 );

		$daily_key = 'post_hits_daily_' . date( 'Y-m-d' );
		$daily     = get_post_meta( $post_id, $daily_key, true );
		update_post_meta( $post_id, $daily_key, $daily + 1 );
	}
}
