<?php

namespace Afca\Plugins\ProHitTracker\Support;

class PostTypes {

	public static function get(): array {
		$saved = get_option( 'pht_post_types', '' );

		if ( empty( trim( $saved ) ) ) {
			return [ 'post', 'page' ];
		}

		return array_values(
			array_filter(
				array_map( 'sanitize_key', array_map( 'trim', explode( ',', $saved ) ) )
			)
		);
	}
}
