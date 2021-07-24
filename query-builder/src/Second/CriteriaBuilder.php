<?php

declare(strict_types=1);

namespace QueryBuilder\Second;

use Latitude\QueryBuilder\Builder\CriteriaBuilder as LatitudeCriteriaBuilder;
use Latitude\QueryBuilder\CriteriaInterface;

class CriteriaBuilder extends LatitudeCriteriaBuilder
{
	public function between($start, $end): CriteriaInterface
    {
    	return parent::between(param($start), param($end));
    }

    public function notBetween($start, $end): CriteriaInterface
    {
    	return parent::notBetween(param($start), param($end));
    }

    public function in(...$values): CriteriaInterface
    {
    	return parent::in(...paramAll($values));
    }

    public function notIn(...$values): CriteriaInterface
    {
    	return parent::notIn(...paramAll($values));
    }

    public function eq($value): CriteriaInterface
    {
    	return parent::eq(param($value));
    }

    public function notEq($value): CriteriaInterface
    {
    	return parent::notEq(param($value));
    }

    public function gt($value): CriteriaInterface
    {
    	return parent::gt(param($value));
    }

    public function gte($value): CriteriaInterface
    {
    	return parent::gte(param($value));
    }

    public function lt($value): CriteriaInterface
    {
    	return parent::lt(param($value));
    }

    public function lte($value): CriteriaInterface
    {
    	return parent::lte(param($value));
    }
}
