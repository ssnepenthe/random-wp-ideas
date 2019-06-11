<?php

use function Flush_Free_Rewrites\_registry;

( function() {
	require_once __DIR__ . '/class-registry.php';
	require_once __DIR__ . '/functions.php';

	$guard = function( $value ) {
		return \count( _registry()->get_rewrites() ) > 0
			&& \is_array( $value )
			&& \count( $value ) > 0;
	};

	$merger = function( $value ) use ( $guard ) {
		if ( ! $guard( $value ) ) {
			return $value;
		}

		// @todo Verify order matches native rewrite API.
		return \array_merge( _registry()->get_top(), $value, _registry()->get_bottom() );
	};

	$differ = function( $value ) use ( $guard ) {
		if ( ! $guard( $value ) ) {
			return $value;
		}

		return \array_diff_key( $value, _registry()->get_rewrites() );
	};

	// @todo Set latest possible priority?
	\add_filter( 'option_rewrite_rules', $merger );
	\add_filter( 'rewrite_rules_array', $merger );
	\add_filter( 'pre_update_option_rewrite_rules', $differ );
} )();
