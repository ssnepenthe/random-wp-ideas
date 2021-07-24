<?php

namespace QueryBuilder\Second;

use InvalidArgumentException;
use Latitude\QueryBuilder\QueryInterface;
use wpdb;

class Db
{
    protected $db;

    public function __construct(?wpdb $db = null)
    {
        if (null !== $db) {
            $this->db = $db;
        } elseif (array_key_exists('wpdb', $GLOBALS) && $GLOBALS['wpdb'] instanceof wpdb) {
            $this->db = $GLOBALS['wpdb'];
        } else {
            throw new InvalidArgumentException('@todo');
        }
    }

    public function getVar(QueryInterface $query, $x = 0, $y = 0)
    {
        return $this->db->get_var($this->prepare($query), $x, $y);
    }

    public function getRow(QueryInterface $query, $output = OBJECT, $y = 0)
    {
        return $this->db->get_row($this->prepare($query), $output, $y);
    }

    public function getCol(QueryInterface $query, $x = 0)
    {
        return $this->db->get_col($this->prepare($query), $x);
    }

    public function getResults(QueryInterface $query, $output = OBJECT)
    {
        return $this->db->get_results($this->prepare($query), $output);
    }

    public function query(QueryInterface $query)
    {
        return $this->db->query($this->prepare($query));
    }

    public function table(string $table): string
    {
        if (array_key_exists($table, $tables = $this->db->tables())) {
            return $tables[$table];
        }

        // @todo I am fairly certain that blog prefix is more appropriate than base prefix (global
        // tables use base, everything else uses blog) but it may be worth revisiting...
        return $this->db->get_blog_prefix() . $table;
    }

    public function prepare(QueryInterface $query): string
    {
        $compiled = $query->compile();

        if (empty($params = $compiled->params())) {
            return $compiled->sql();
        }

        return $this->db->prepare($compiled->sql(), $params);
    }
}
