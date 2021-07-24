<?php

declare(strict_types=1);

namespace QueryBuilder\Second;

use Latitude\QueryBuilder\Query\UpdateQuery as LatitudeUpdateQuery;
use Latitude\QueryBuilder\StatementInterface;

use function Latitude\QueryBuilder\express;
use function Latitude\QueryBuilder\identify;
use function Latitude\QueryBuilder\listing;

class UpdateQuery extends LatitudeUpdateQuery
{
	public function set(array $map): self
	{
		$this->set = listing(array_map(
            function ($key, $value): StatementInterface {
                return express('%s = %s', identify($key), param($value));
            },
            array_keys($map),
            $map
        ));
        return $this;
	}
}
