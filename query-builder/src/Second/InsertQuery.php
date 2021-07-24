<?php

declare(strict_types=1);

namespace QueryBuilder\Second;

use Latitude\QueryBuilder\Query\InsertQuery as LatitudeInsertQuery;

use function Latitude\QueryBuilder\listing;
use function Latitude\QueryBuilder\express;

class InsertQuery extends LatitudeInsertQuery
{
	public function values(...$params): self
	{
		$this->values[] = express('(%s)', listing(paramAll($params)));

        return $this;
	}
}