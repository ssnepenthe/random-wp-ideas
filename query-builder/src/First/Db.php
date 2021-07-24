<?php

namespace QueryBuilder\First;

use wpdb;
use InvalidArgumentException;
use Latitude\QueryBuilder\QueryInterface;

/**
 * Covers the primary query functions of wpdb.
 *
 * Does not include the insert, delete or update methods which can be handled by passing an
 * instance of their corresponding query classes to the query method.
 *
 * Does not include the replace method which is not supported by latitude AFAIK.
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

	public function getVar( QueryInterface $query, $x = 0, $y = 0 ) {
		return $this->db->get_var( $this->prepare( $query ), $x, $y );
	}

	public function getRow( QueryInterface $query, $output = OBJECT, $y = 0 ) {
		return $this->db->get_row( $this->prepare( $query ), $output, $y );
	}

	public function getCol( QueryInterface $query, $x = 0 ) {
		return $this->db->get_col( $this->prepare( $query ), $x );
	}

	public function getResults( QueryInterface $query, $output = OBJECT ) {
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
			$this->replacePlaceholders( $compiled->sql(), $params ),
			$params
		);
	}

	protected function replacePlaceholders( $sql, $params ) : string {
		$position = 0;

		return preg_replace_callback( '/\?/', function ( $matches ) use ( $params, &$position ) {
			// Guard against errors if for some reason there is a ? in $sql that was actually
			// intended as part of the query and not as parameter placeholder.
			if ( ! isset( $params[ $position ] ) ) {
				// @todo Is InvalidArgumentException more appropriate?
				throw new \RuntimeException( '@todo' );
			}

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
