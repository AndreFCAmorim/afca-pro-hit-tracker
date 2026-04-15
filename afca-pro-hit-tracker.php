<?php
/**
 * Plugin Name:       AFCA Pro Hit Tracker
 * Plugin URI:        https://andreamorim.site/plugin-documentation/afca-pro-hit-tracker/
 * Description:       Tracks unique daily hits per post/page via REST API, with spam protection.
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Version:           1.0
 * Author:            André Amorim
 * Author URI:        https://andreamorim.site
 * Text Domain:       afca-pro-hit-tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

define( 'PHT_VERSION', '1.0' );
define( 'PHT_DIR', plugin_dir_path( __FILE__ ) );
define( 'PHT_URL', plugin_dir_url( __FILE__ ) );

use Afca\Plugins\ProHitTracker\Admin\AdminColumns;
use Afca\Plugins\ProHitTracker\Admin\DashboardWidget;
use Afca\Plugins\ProHitTracker\Admin\SettingsPage;
use Afca\Plugins\ProHitTracker\Admin\Updates;
use Afca\Plugins\ProHitTracker\Api\NonceEndpoint;
use Afca\Plugins\ProHitTracker\Api\TrackEndpoint;
use Afca\Plugins\ProHitTracker\Frontend\Enqueue;

add_action(
	'plugins_loaded',
	function () {
		( new TrackEndpoint() )->register();
		( new NonceEndpoint() )->register();
		( new SettingsPage() )->register();
		( new AdminColumns() )->register();
		( new DashboardWidget() )->register();
		( new Enqueue() )->register();

		$update_class = new Updates( 'https://andreamorim.site/', plugin_basename( __FILE__ ), PHT_VERSION );

		add_action( 'afca_pro_hit_tracker_updates', [ $update_class, 'check_for_updates_on_hub' ] );

		if ( ! wp_next_scheduled( 'afca_pro_hit_tracker_updates' ) ) {
			wp_schedule_event( time(), 'daily', 'afca_pro_hit_tracker_updates' );
		}
	}
);
