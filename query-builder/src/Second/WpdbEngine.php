<?php

declare(strict_types=1);

namespace QueryBuilder\Second;

use Latitude\QueryBuilder\Engine\BasicEngine;
use Latitude\QueryBuilder\Query\InsertQuery as LatitudeInsertQuery;
use Latitude\QueryBuilder\Query\UpdateQuery as LatitudeUpdateQuery;

class WpdbEngine extends BasicEngine
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
