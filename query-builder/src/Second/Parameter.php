<?php

declare(strict_types=1);

namespace QueryBuilder\Second;

use InvalidArgumentException;
use Latitude\QueryBuilder\EngineInterface;
use Latitude\QueryBuilder\StatementInterface;

final class Parameter implements StatementInterface
{
    /** @var string */
    private $sql = '';

    /** @var array */
    private $params = [];

    public function __construct($value)
    {
        if (is_bool($value) || is_null($value)) {
            $this->sql = var_export($value, true);
        } else {
			if (is_string($value)) {
				$this->sql = '%s';
			} elseif (is_int($value)) {
				$this->sql = '%d';
			} elseif (is_float($value)) {
				$this->sql = '%f';
			} else {
				throw new InvalidArgumentException('@todo');
			}

            $this->params[] = $value;
        }
    }

    public function sql(EngineInterface $engine): string
    {
        return $this->sql;
    }

    public function params(EngineInterface $engine): array
    {
        return $this->params;
    }
}
