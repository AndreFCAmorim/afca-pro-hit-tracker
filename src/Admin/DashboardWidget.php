<?php

namespace Afca\Plugins\ProHitTracker\Admin;

use Afca\Plugins\ProHitTracker\Support\PostTypes;

class DashboardWidget {

	public function register(): void {
		add_action( 'wp_dashboard_setup', [ $this, 'add_widget' ] );
	}

	public function add_widget(): void {
		wp_add_dashboard_widget( 'top_posts_hits', 'Top Posts by Hits', [ $this, 'render' ] );
	}

	public function render(): void {
		$posts = get_posts(
			[
				'post_type'      => PostTypes::get(),
				'posts_per_page' => 5,
				'meta_key'       => 'post_hits',
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC',
				'post_status'    => 'publish',
			]
		);

		if ( ! $posts ) {
			echo '<p>No data yet.</p>';
		} else {
			echo '<ul>';
			foreach ( $posts as $p ) {
				$hits  = number_format( (int) get_post_meta( $p->ID, 'post_hits', true ) );
				$link  = esc_url( get_edit_post_link( $p->ID ) );
				$title = esc_html( $p->post_title );
				echo "<li><a href=\"{$link}\">{$title}</a> &mdash; {$hits} hits</li>";
			}
			echo '</ul>';
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<hr style="margin: 12px 0;">';
		echo '<p style="margin:0"><a href="' . esc_url( admin_url( 'options-general.php?page=pht-settings' ) ) . '" class="button button-small">⚙️ PHT Settings & Reset</a></p>';
	}
}
