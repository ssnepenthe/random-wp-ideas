<?php

declare(strict_types=1);

namespace QueryBuilder\Second;

use Latitude\QueryBuilder\Builder\LikeBuilder as LatitudeLikeBuilder;
use Latitude\QueryBuilder\CriteriaInterface;

class LikeBuilder extends LatitudeLikeBuilder
{
    public function begins(string $value): CriteriaInterface
    {
        return $this->like(new LikeBegins($value));
    }

    public function notBegins(string $value): CriteriaInterface
    {
        return $this->notLike(new LikeBegins($value));
    }

    public function contains(string $value): CriteriaInterface
    {
        return $this->like(new LikeContains($value));
    }

    public function notContains(string $value): CriteriaInterface
    {
        return $this->notLike(new LikeContains($value));
    }

    public function ends(string $value): CriteriaInterface
    {
        return $this->like(new LikeEnds($value));
    }

    public function notEnds(string $value): CriteriaInterface
    {
        return $this->notLike(new LikeEnds($value));
    }
}
