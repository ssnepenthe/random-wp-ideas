<?php

// @todo Name... Proxies calls to EITHER the query or the db.

namespace Query_Builder;

use BadMethodCallException;
use Latitude\QueryBuilder\EngineInterface;
use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\Query;
use Latitude\QueryBuilder\QueryInterface;
use Latitude\QueryBuilder\StatementInterface;

class Query_Proxy implements QueryInterface {
	protected $db;
	protected $query;

	public function __construct( QueryInterface $query, Db $db = null ) {
		$this->query = $query;
		$this->db = $db ?: db();
	}

	public function asExpression() : ExpressionInterface {
		return $this->query->asExpression();
	}

	public function compile() : Query {
		return $this->query->compile();
	}

	public function sql( EngineInterface $engine ) : string {
		return $this->query->sql();
	}

	public function params( EngineInterface $engine ) : array {
		return $this->query->params();
	}

	public function get_var( $x = 0, $y = 0 ) {
		return $this->db->get_var( $this, $x, $y );
	}

	public function get_row( $output = OBJECT, $y = 0 ) {
		return $this->db->get_row( $this, $output, $y );
	}

	public function get_results( $output = OBJECT ) {
		return $this->db->get_results( $this, $output );
	}

	public function query() {
		return $this->db->query( $this );
	}

	public function __call( $method, $args ) {
		if ( method_exists( $this->query, $method ) ) {
			call_user_func_array( [ $this->query, $method ], $args );

			return $this;
		}

		$camel_cased = str_camel( $method );

		if ( method_exists( $this->query, $camel_cased ) ) {
			call_user_func_array( [ $this->query, $camel_cased ], $args );

			return $this;
		}

		throw new BadMethodCallException( '@todo' );
	}

	// All public query methods return $this except union methods which return a union query object.
	public function union( StatementInterface $right ) {
		if ( ! method_exists( $this->query, 'union' ) ) {
			throw new BadMethodCallException( '@todo' );
		}

		// @todo Should we modify and return this instance instead?
		// $this->query = $this->query->union( $right );
		// return $this;
		return new Query_Proxy( $this->query->union( $right ) );
	}

	public function unionAll( StatementInterface $right ) {
		if ( ! method_exists( $this->query, 'unionAll' ) ) {
			throw new BadMethodCallException( '@todo' );
		}

		// @todo Should we modify and return this instance instead?
		// $this->query = $this->query->unionAll( $right );
		// return $this;
		return new Query_Proxy( $this->query->unionAll( $right ) );
	}

	public function union_all( StatementInterface $right ) {
		return $this->unionAll( $right );
	}
}
