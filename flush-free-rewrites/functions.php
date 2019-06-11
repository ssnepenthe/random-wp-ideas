<?php

// @todo What about rewrite endpoints?

namespace Flush_Free_Rewrites;

function add_rewrite_rule( $regex, $query, $after = 'bottom' ) {
	return _registry()->add( $regex, $query, $after );
}

function _registry() {
	static $instance;

	if ( null === $instance ) {
		$instance = new Registry();
	}

	return $instance;
}
