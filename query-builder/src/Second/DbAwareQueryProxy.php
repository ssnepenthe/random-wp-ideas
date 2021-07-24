<?php

namespace QueryBuilder\Second;

use BadMethodCallException;
use Latitude\QueryBuilder\EngineInterface;
use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\Query;
use Latitude\QueryBuilder\QueryInterface;
use Latitude\QueryBuilder\StatementInterface;

class DbAwareQueryProxy implements QueryInterface
{
	protected $db;
	protected $query;

	public function __construct(QueryInterface $query, ?Db $db = null)
	{
		$this->query = $query;
		$this->db = $db ?: new Db();
	}

	public function asExpression(): ExpressionInterface
	{
		return $this->query->asExpression();
	}

	public function compile(): Query
	{
		return $this->query->compile();
	}

	public function sql(EngineInterface $engine): string
	{
		// @todo Should be $this->query->sql($engine); ???
		return $this->query->sql();
	}

	public function params(EngineInterface $engine): array
	{
		// @todo Should be $this->query->params($engine); ???
		return $this->query->params();
	}

	public function getVar($x = 0, $y = 0)
	{
		return $this->db->getVar($this, $x, $y);

		// return $this->db->getVar($this->sql(), $this->params(), $x, $y);
	}

	public function getRow($output = OBJECT, $y = 0)
	{
		return $this->db->getRow($this, $output, $y);

		// return $this->db->getRow($this->sql(), $this->params(), $output, $y);
	}

	public function getCol($x = 0)
	{
		return $this->db->getCol($this, $x);
	}

	public function getResults($output = OBJECT)
	{
		return $this->db->getResults($this, $output);
	}

	public function query()
	{
		return $this->db->query($this);
	}

	public function __call($method, $args)
	{
		if (! method_exists($this->query, $method)) {
			throw new BadMethodCallException('@todo');
		}

		$result = call_user_func_array([$this->query, $method], $args);

		// All public methods return $this except union* methods which return union query objects.
		// @todo Should we modify and return this instance instead?
		// i.e. $this->query = $this->query->union( $right );
		return 'union' === substr($method, 0, 5) ? new DbAwareQueryProxy($result) : $this;
	}
}
