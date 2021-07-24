<?php

declare(strict_types=1);

namespace QueryBuilder\Second;

use Latitude\QueryBuilder\Engine\CommonEngine;
use Latitude\QueryBuilder\Query\InsertQuery as LatitudeInsertQuery;
use Latitude\QueryBuilder\Query\UpdateQuery as LatitudeUpdateQuery;

class WpdbEngine extends CommonEngine
{
    public function makeUpdate(): LatitudeUpdateQuery
    {
        return new UpdateQuery($this);
    }

    public function makeInsert(): LatitudeInsertQuery
    {
        return new InsertQuery($this);
    }
}
