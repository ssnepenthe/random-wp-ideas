<?php

namespace Query_Builder;

use wpdb;
use InvalidArgumentException;
use Latitude\QueryBuilder\QueryInterface;

/**
 * Covers the primary query functions of wpdb.
 *
 * Does not include the insert, replace or delete methods which can be handled by passing an
 * instance of their corresponding query classes to the query method.
 *
 * Does not include the update method which is not supported by latitude AFAIK.
 */
class Db {
	protected $db;

	public function __construct( wpdb $db = null ) {
		if ( null !== $db ) {
			$this->db = $db;
		} elseif ( array_key_exists( 'wpdb', $GLOBALS ) && $GLOBALS['wpdb'] instanceof wpdb ) {
			$this->db = $GLOBALS['wpdb'];
		} else {
			throw new InvalidArgumentException( '@todo' );
		}
	}

	public function get_var( QueryInterface $query, $x = 0, $y = 0	 ) {
		return $this->db->get_var( $this->prepare( $query ), $x, $y );
	}

	public function get_row( QueryInterface $query, $output = OBJECT, $y = 0 ) {
		return $this->db->get_row( $this->prepare( $query ), $output, $y );
	}

	public function get_col( QueryInterface $query, $x = 0 ) {
		return $this->db->get_col( $this->prepare( $query ), $x );
	}

	public function get_results( QueryInterface $query, $output = OBJECT ) {
		return $this->db->get_results( $this->prepare( $query ), $output );
	}

	public function query( QueryInterface $query ) {
		return $this->db->query( $this->prepare( $query ) );
	}

	public function table( string $table ) : string {
		// Should keep us safe on multisite but requires custom tables to be registered...
		$tables = $this->db->tables();

		if ( ! array_key_exists( $table, $tables ) ) {
			// @todo Should we really throw for unknown tables? Maybe fall back to sensible default?
			throw new InvalidArgumentException( '@todo' );
		}

		return $tables[ $table ];
	}

	// @todo Public?
	protected function prepare( QueryInterface $query ) : string {
		$compiled = $query->compile();

		if ( empty( $params = $compiled->params() ) ) {
			return $compiled->sql();
		}

		return $this->db->prepare(
			$this->replace_placeholders( $compiled->sql(), $params ),
			$params
		);
	}

	protected function replace_placeholders( $sql, $params ) : string {
		$position = 0;

		return preg_replace_callback( '/\?/', function ( $matches ) use ( $params, &$position ) {
			$param = $params[ $position++ ];

			if ( is_string( $param ) ) {
				return '%s';
			} elseif ( is_int( $param ) ) {
				return '%d';
			} elseif ( is_float( $param ) ) {
				return '%f';
			} else {
				throw new InvalidArgumentException( '@todo' );
			}
		}, $sql );
	}
}