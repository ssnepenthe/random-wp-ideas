<?php

namespace Hook_Injection;

function _event_manager() {
	static $event_manager;

	if ( null === $event_manager ) {
		$event_manager = new Event_Manager();
	}

	return $event_manager;
}

function on( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
	return _event_manager()->on( $tag, $callback, $priority, $accepted_args );
}

function off( $tag, $callback, $priority = 10 ) {
	return _event_manager()->off( $tag, $callback, $priority );
}

function has( $tag, $callback = false ) {
	return _event_manager()->has( $tag, $callback );
}
