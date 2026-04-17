<?php

namespace Afca\Plugins\ProHitTracker\Support;

class Helpers {

	public static function client_ip(): string {
		$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		}

		return trim( $ip );
	}

	public static function is_bot() {
		$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
		return empty( $ua ) || (bool) preg_match( '/bot|crawl|spider|slurp|curl|wget|python|go-http/i', $ua );
	}
}
