<?php

namespace Hook_Injection;

class Util {
	public static function gather_wp_globals() {
		return [
			// @todo More globals.
			'wp' => static::get_global_variable( 'wp' ),
			\WP::class => static::get_global_variable( 'wp' ),

			'wpdb' => static::get_global_variable( 'wpdb' ),
			\wpdb::class => static::get_global_variable( 'wpdb' ),

			'wp_query' => static::get_global_variable( 'wp_query' ),
			\WP_Query::class => static::get_global_variable( 'wp_query' ),

			'wp_rewrite' => static::get_global_variable( 'wp_rewrite' ),
			\WP_Rewrite::class => static::get_global_variable( 'wp_rewrite' ),

			'wp_filesystem' => static::get_filesystem_global(),
			\WP_Filesystem_Base::class => static::get_filesystem_global(),

			'wp_object_cache' => static::get_cache_global(),
			\WP_Object_Cache::class => static::get_cache_global(),
		];
	}

	protected static function get_global_variable( $key ) {
		return \array_key_exists( $key, $GLOBALS ) ? $GLOBALS[ $key ] : null;
	}

	protected static function get_filesystem_global() {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}

		if ( ! isset( $GLOBALS['wp_filesystem'] ) ) {
			\WP_Filesystem();
		}

		// Still technically possible to be null at this point.
		return static::get_global_variable( 'wp_filesystem' );
	}

	protected static function get_cache_global() {
		if ( ! isset( $GLOBALS['wp_object_cache'] ) && function_exists( 'wp_cache_init' ) ) {
			\wp_cache_init();
		}

		// Still technically possible to be null at this point.
		return static::get_global_variable( 'wp_object_cache' );
	}
}
