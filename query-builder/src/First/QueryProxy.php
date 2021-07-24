<?php

// @todo Name... Proxies calls to EITHER the query or the db.

namespace QueryBuilder\First;

use BadMethodCallException;
use Latitude\QueryBuilder\EngineInterface;
use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\Query;
use Latitude\QueryBuilder\QueryInterface;
use Latitude\QueryBuilder\StatementInterface;

class QueryProxy implements QueryInterface {
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

	public function getVar( $x = 0, $y = 0 ) {
		return $this->db->get_var( $this, $x, $y );
	}

	public function getRow( $output = OBJECT, $y = 0 ) {
		return $this->db->get_row( $this, $output, $y );
	}

	public function getCol( $x = 0 ) {
		return $this->db->get_col( $this, $x );
	}

	public function getResults( $output = OBJECT ) {
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
		return new QueryProxy( $this->query->union( $right ) );
	}

	public function unionAll( StatementInterface $right ) {
		if ( ! method_exists( $this->query, 'unionAll' ) ) {
			throw new BadMethodCallException( '@todo' );
		}

		// @todo Should we modify and return this instance instead?
		// $this->query = $this->query->unionAll( $right );
		// return $this;
		return new QueryProxy( $this->query->unionAll( $right ) );
	}
}
