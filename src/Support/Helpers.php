<?php

namespace Afca\Plugins\ProHitTracker\Support;

class Helpers {

	public static function client_ip(): string {
		return trim( explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' )[0] );
	}

	public static function is_bot(): bool {
		$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
		return empty( $ua ) || (bool) preg_match( '/bot|crawl|spider|slurp|curl|wget|python|go-http/i', $ua );
	}
}
