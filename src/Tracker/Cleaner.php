<?php

namespace Afca\Plugins\ProHitTracker\Tracker;

class Cleaner {

	public function run() {
		global $wpdb;

		$meta_deleted = $wpdb->query(
			"DELETE FROM {$wpdb->postmeta}
             WHERE meta_key = 'post_hits'
                OR meta_key LIKE 'post_hits_daily_%'"
		);

		$transients_deleted = $wpdb->query(
			"DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_hit_%'
                OR option_name LIKE '_transient_timeout_hit_%'
                OR option_name LIKE '_transient_pht_rate_%'
                OR option_name LIKE '_transient_timeout_pht_rate_%'"
		);

		return [
			'meta_rows_deleted'      => (int) $meta_deleted,
			'transient_rows_deleted' => (int) $transients_deleted,
		];
	}
}
