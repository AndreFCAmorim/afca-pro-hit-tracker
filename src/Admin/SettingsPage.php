<?php

namespace Afca\Plugins\ProHitTracker\Admin;

use Afca\Plugins\ProHitTracker\Tracker\Cleaner;

class SettingsPage {

	public function register() {
		add_action( 'admin_menu', [ $this, 'add_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_post_pht_reset', [ $this, 'handle_reset' ] );
	}

	public function add_page() {
		add_options_page(
			'Pro Hit Tracker Settings',
			'Pro Hit Tracker',
			'manage_options',
			'pht-settings',
			[ $this, 'render' ]
		);
	}

	public function register_settings() {
		register_setting(
			'pht_settings_group',
			'pht_post_types',
			[
				'sanitize_callback' => function ( string $value ): string {
					$types = array_filter(
						array_map( 'sanitize_key', array_map( 'trim', explode( ',', $value ) ) )
					);
					return implode( ', ', $types );
				},
				'default'           => 'post, page',
			]
		);
	}

	public function handle_reset() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized', 403 );
		}

		check_admin_referer( 'pht_reset_action', 'pht_reset_nonce' );

		$result = ( new Cleaner() )->run();

		wp_redirect(
			add_query_arg(
				[
					'page'      => 'pht-settings',
					'pht_reset' => 'done',
					'pht_meta'  => $result['meta_rows_deleted'],
					'pht_trans' => $result['transient_rows_deleted'],
				],
				admin_url( 'options-general.php' )
			)
		);

		exit;
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['pht_reset'], $_GET['pht_meta'], $_GET['pht_trans'] ) && $_GET['pht_reset'] === 'done' ) {
			$meta  = (int) $_GET['pht_meta'];
			$trans = (int) $_GET['pht_trans'];
			echo '<div class="notice notice-success is-dismissible"><p>';
			echo "<strong>Pro Hit Tracker:</strong> Reset complete. Removed {$meta} hit meta rows and {$trans} transient rows.";
			echo '</p></div>';
		}

		$current_types = get_option( 'pht_post_types', 'post, page' );
		$action_url    = esc_url( admin_url( 'admin-post.php' ) );
		$reset_nonce   = wp_nonce_field( 'pht_reset_action', 'pht_reset_nonce', true, false );

		$registered = get_post_types( [ 'public' => true ], 'objects' );
		$hints      = [];
		foreach ( $registered as $pt ) {
			$hints[] = '<code>' . esc_html( $pt->name ) . '</code> (' . esc_html( $pt->label ) . ')';
		}
		?>
		<div class="wrap">
			<h1>Pro Hit Tracker Settings</h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'pht_settings_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="pht_post_types">Tracked Post Types</label>
						</th>
						<td>
							<input
								type="text"
								id="pht_post_types"
								name="pht_post_types"
								value="<?php echo esc_attr( $current_types ); ?>"
								class="regular-text"
								placeholder="post, page"
							>
							<p class="description">
								Comma-separated list of post type slugs to track.<br>
								Registered public post types on this site:
								<?php echo implode( ', ', $hints ); ?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button( 'Save Settings' ); ?>
			</form>

			<hr>

			<h2>Danger Zone</h2>
			<p>This will permanently delete all recorded hits and rate-limit transients.</p>
			<form method="post" action="<?php echo $action_url; ?>">
				<input type="hidden" name="action" value="pht_reset">
				<?php echo $reset_nonce; ?>
				<button
					type="submit"
					class="button button-link-delete"
					onclick="return confirm('Reset ALL hit counts and transients? This cannot be undone.')">
					&#x267B; Reset All Hits
				</button>
			</form>
		</div>
		<?php
	}
}