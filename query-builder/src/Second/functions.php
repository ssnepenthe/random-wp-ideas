<?php

declare(strict_types=1);

namespace QueryBuilder\Second;

use Latitude\QueryBuilder\QueryFactory;
use Latitude\QueryBuilder\QueryInterface;
use Latitude\QueryBuilder\StatementInterface;

use function Latitude\QueryBuilder\identify;
use function Latitude\QueryBuilder\isStatement;

function db(): Db
{
	static $db;

	if (null === $db) {
		$db = new Db();
	}

	return $db;
}

function queryFactory(): QueryFactory
{
	static $factory;

	if (null === $factory) {
		$factory = new QueryFactory(engine());
	}

	return $factory;
}

function engine(): EngineInterface
{
	static $engine;

	if (null === $engine) {
		$engine = new WpdbEngine();
	}

	return $engine;
}

function table(string $tableWithoutPrefix): string
{
	return db()->table($tableWithoutPrefix);
}

function select(...$columns): DbAwareQueryProxy
{
	return new DbAwareQueryProxy(queryFactory()->select(...$columns), db());
}

function selectDistinct(...$columns): DbAwareQueryProxy
{
	return new DbAwareQueryProxy(queryFactory()->selectDistinct(...$columns), db());
}

function insert($table, array $map = []): DbAwareQueryProxy
{
	return new DbAwareQueryProxy(queryFactory()->insert($table, $map), db());
}

function delete($table): DbAwareQueryProxy
{
	return new DbAwareQueryProxy(queryFactory()->delete($table), db());
}

function update($table, array $map = []): DbAwareQueryProxy
{
	return new DbAwareQueryProxy(queryFactory()->update($table, $map), db());
}

function field($name): CriteriaBuilder
{
	return new CriteriaBuilder(identify($name));
}

function search($name): LikeBuilder
{
	return new LikeBuilder(identify($name));
}

function param($value): StatementInterface
{
    return isStatement($value) ? $value : new Parameter($value);
}

/**
 * @return StatementInterface[]
 */
function paramAll(array $values): array
{
    return array_map(__NAMESPACE__ . '\\param', $values);
}
