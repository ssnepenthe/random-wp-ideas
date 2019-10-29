<?php

namespace Query_Builder;

use Latitude\QueryBuilder\QueryFactory;
use Latitude\QueryBuilder\Engine\MySqlEngine;

function db() {
	static $db;

	if ( null === $db ) {
		$db = new Db();
	}

	return $db;
}

function factory() {
	static $factory;

	if ( null === $factory ) {
		$factory = new QueryFactory( new MySqlEngine() );
	}

	return $factory;
}

function table( string $table ) : string {
	return db()->table( $table );
}

function select( ...$columns ) {
	return new Query_Proxy( factory()->select( ...$columns ) );
}

function select_distinct( ...$columns ) {
	return new Query_Proxy( factory()->selectDistinct( ...$columns ) );
}

function insert( $table, array $map = [] ) {
	return new Query_Proxy( factory()->insert( $table, $map ) );
}

function delete( $table ) {
	return new Query_Proxy( factory()->delete( $table ) );
}

function update( $table, array $map = [] ) {
	return new Query_Proxy( factory()->update( $table, $map ) );
}

/*
 * String helpers adapted from illuminate/support.
 */

function str_camel( $value ) {
	static $cache = [];

	if ( isset( $cache[ $value ] ) ) {
		return $cache[ $value ];
	}

	return $cache[ $value ] = lcfirst( str_studly( $value ) );
}

function str_studly( $value ) {
	static $cache = [];

	$key = $value;

	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$value = ucwords( str_replace( [ '-', '_' ], ' ', $value ) );

	return $cache[ $key ] = str_replace( ' ', '', $value );
}
