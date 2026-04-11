<?php

namespace Afca\Plugins\ProHitTracker\Admin;

use Afca\Plugins\ProHitTracker\Support\PostTypes;

class AdminColumns {

	public function register(): void {
		add_action( 'init', [ $this, 'register_column_hooks' ], 20 );
		add_action( 'pre_get_posts', [ $this, 'sort_by_hits' ] );
	}

	public function register_column_hooks(): void {
		foreach ( PostTypes::get() as $pt ) {
			$col_filter  = $pt === 'page' ? 'manage_pages_columns' : "manage_{$pt}_posts_columns";
			$col_action  = $pt === 'page' ? 'manage_pages_custom_column' : "manage_{$pt}_posts_custom_column";
			$sort_filter = "manage_edit-{$pt}_sortable_columns";

			add_filter( $col_filter, [ $this, 'add_column' ] );
			add_action( $col_action, [ $this, 'render_column' ], 10, 2 );
			add_filter( $sort_filter, [ $this, 'sortable_column' ] );
		}
	}

	public function add_column( array $cols ): array {
		$cols['post_hits'] = 'Hits';
		return $cols;
	}

	public function render_column( string $col, int $id ): void {
		if ( $col === 'post_hits' ) {
			echo number_format( (int) get_post_meta( $id, 'post_hits', true ) );
		}
	}

	public function sortable_column( array $cols ): array {
		$cols['post_hits'] = 'post_hits';
		return $cols;
	}

	public function sort_by_hits( \WP_Query $q ): void {
		if ( ! is_admin() || ! $q->is_main_query() ) {
			return;
		}
		if ( $q->get( 'orderby' ) === 'post_hits' ) {
			$q->set( 'meta_key', 'post_hits' );
			$q->set( 'orderby', 'meta_value_num' );
		}
	}
}
