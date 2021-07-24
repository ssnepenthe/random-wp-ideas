<?php

namespace QueryBuilder\First;

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
		// @todo is BasicEngine, CommonEngine or MySqlEngine most appropriate?
		$factory = new QueryFactory( new MySqlEngine() );
	}

	return $factory;
}

function table( string $table ) : string {
	return db()->table( $table );
}

function select( ...$columns ) {
	return new QueryProxy( factory()->select( ...$columns ) );
}

function selectDistinct( ...$columns ) {
	return new QueryProxy( factory()->selectDistinct( ...$columns ) );
}

function insert( $table, array $map = [] ) {
	return new QueryProxy( factory()->insert( $table, $map ) );
}

function delete( $table ) {
	return new QueryProxy( factory()->delete( $table ) );
}

function update( $table, array $map = [] ) {
	return new QueryProxy( factory()->update( $table, $map ) );
}
