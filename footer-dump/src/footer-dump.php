<?php

use Footer_Dump\Factory;

if ( ! function_exists( 'fd' ) ) {
	function fd( $var, ...$more_vars ) {
		// @todo Cached cloner?
		$cloner = Factory::cloner();

		$to_dump = [ $cloner->cloneVar( $var ) ];

		foreach ( $more_vars as $var ) {
			$to_dump[] = $cloner->cloneVar( $var );
		}

		add_action( 'wp_footer', function() use ( $to_dump ) {
			dump( ...$to_dump );
		} );
	}
}
