<?php

namespace FRWP;

use Closure;
use FastRoute\RouteParser\Std;

class Converter {
	private $parser;

	private $prefix;

	private $extra_param_resolver;

	private $route_identity_resolver;

	public function __construct( string $prefix = '' ) {
		// @todo Should we allow an empty prefix?
		$this->prefix = $prefix;

		$this->parser = new Std;

		$this->identify_routes_using( function( $regex ) {
			return [ "{$this->prefix}matched_route", $regex ];
		} );

		$this->resolve_additional_params_using( function() {
			return [];
		} );
	}

	public function to_wp_rewrite( string $route ) {
		$parsed = $this->parser->parse( $route );
		$rewrites = [];

		foreach ( $parsed as $segments ) {
			$regex = '';
			$query_array = [];
			$position = 1;

			foreach ( $segments as $segment ) {
				if ( is_string( $segment ) ) {
					$regex .= $segment;
					continue;
				}

				[ $name, $pattern ] = $segment;

				$regex .= "({$pattern})";
				$query_array["{$this->prefix}{$name}"] = "\$matches[{$position}]";
				$position++;
			}

			// @todo Validation?
			[ $id_key, $id_value ] = call_user_func(
				$this->route_identity_resolver,
				$regex,
				$query_array
			);

			$query_array[ $id_key ] = $id_value;

			// Note: this allows user to override parsed params. Should we allow this?
			// Might be better to reverse order of merge and silently drop anything that already exists.
			// Or maybe throw when attempting to override parsed param?
			$query_array = array_merge(
				$query_array,
				// @todo Validation?
				call_user_func( $this->extra_param_resolver, $regex, $query_array )
			);

			$query_array = array_map( function( $key, $value ) {
				return "{$key}={$value}";
			}, array_keys( $query_array ), $query_array );

			$query_string = 'index.php?' . implode( '&', $query_array );

			$rewrites[ $regex ] = $query_string;
		}

		return $rewrites;
	}

	public function identify_routes_using( callable $callback ) {
		// @todo Better not to bind and just pass prefix as arg to callback?
		$this->route_identity_resolver = Closure::bind( $callback, $this, __CLASS__ );

		return $this;
	}

	public function resolve_additional_params_using( callable $callback ) {
		// @todo Better not to bind and just pass prefix as arg to callback?
		$this->extra_param_resolver = Closure::bind( $callback, $this, __CLASS__ );

		return $this;
	}
}
