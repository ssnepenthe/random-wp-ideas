<?php

namespace Flush_Free_Rewrites;

class Registry {
	protected $bottom = [];
	protected $top = [];

	public function add( $regex, $query, $after = 'bottom' ) {
		if ( 'top' === $after ) {
			$this->top[ $regex ] = $query;
		} else {
			$this->bottom[ $regex ] = $query;
		}
	}

	public function get_bottom() {
		return $this->bottom;
	}

	public function get_rewrites() {
		return array_merge( $this->bottom, $this->top );
	}

	public function get_top() {
		return $this->top;
	}
}
