<?php

namespace SQL;

use SQLParser\Stmt\Expr;
use SQLParser\Stmt;

class Writer
{
    protected static $instance;

    /**
     *  Set Write instance
     *
     *  Change the Writer instance to generate SQL-compatible
     *  with another engine.
     */
    final public static function setInstance(self $instance)
    {
        self::$instance = $instance;
    }

    final public static function getInstance()
    {
        self::$instance = self::$instance ?: new self;
        return self::$instance;
    }

    final public static function create($object)
    {
        if (empty(self::$instance)) {
            self::getInstance();
        }

        if ($object instanceof Select) {
            return self::$instance->select($object);
        }

        throw new RuntimeException("Don't know how to create " . get_class($object));
    }

    protected function value($value)
    {
        $map = [
            'SQLParser\Select' => 'select',
            'SQLParser\Stmt\Expr' => 'expr',
            'SQLParser\Stmt\ExprList' => 'exprList',
            'SQLParser\Stmt\VariablePlaceholder' => 'variable',
        ];

        foreach ($map as $class => $callback) {
            if ($value instanceof $class) {
                return $this->$callback($value);
            }
        }

        if ($value === null) {
            return 'NULL';
        }

        if (!is_scalar($value)) {
            throw new \InvalidArgumentException;
        }

        return var_export($value, true);
    }

    public function expr(Stmt\Expr $expr)
    {
        $method = 'expr' . $expr->getType();
        if (is_callable([$this, $method])) {
            return $this->$method($expr);
        }

        $member = array();
        foreach ($expr->GetMembers() as $part) {
            $member[] = $this->value($part);
        }

        $type = $expr->getType();
        switch ($type) {
        case 'COLUMN':
            return implode(".", array_map([$this, 'escape'], $member));

        case 'ALPHA':
            return $member[0];

        case 'CASE':
            $else = NULL;
            if ($expr->getMember(-1) instanceof Stmt\Expr) {
                $else = array_pop($member);
            }
            $stmt = "CASE " . implode(" ", $member);
            if ($else !== NULL) {
                $stmt .= " ELSE " . $else;
            }
            $stmt .= " END";
            return $stmt;
        case 'WHEN':
            return "WHEN {$member[0]} THEN {$member[1]}";

        case 'ALIAS':
            return "{$member[0]} AS {$member[1]}";
        case 'ALL':
            return "*";
        case 'CALL':
            return $expr->getMember(0) . "({$member[1]})";
        case 'ASC':
        case 'DESC':
            return "{$member[0]} {$type}";
        case 'IN':
            return "{$member[0]} IN ({$member[1]})";
        case 'EXPR':
            return "({$member[0]})";
        case 'VALUE': 
            return $member[0];
        case 'NOT':
            return "NOT {$member[0]}";
        case 'TIMEINTERVAL':
            return "INTERVAL {$member[0]} " . $expr->getMember(1);
        }

        return "{$member[0]} {$type} {$member[1]}";
    }

    protected function exprList(Array $list)
    {
        $columns = array();
        foreach ($list as $column) {
            if (is_array($column[0])) {
                $value = $this->expr($column[0][0]) . "." . $this->expr($column[0][1]);
            } else {
                var_dump($column[0]);
                $value = $this->expr($column[0]);
            }
            if (count($column) == 2) {
                $columns[] = $value . ' AS '. $this->escape($column[1]);
            } else {
                $columns[] = $value;
            }
        }

        return implode(",", $columns); 
    }

    protected function tableList(Array $tables)
    {
        $list = array();
        foreach ($tables as $key => $table) {
            if (is_numeric($key)) {
                $list[] = $this->escape($table);
            } else if (is_array($table)) {
                $list[] = $this->escape($table[0]) . '.' . $this->escape($table[1]) . ' AS ' . $this->escape($key);
            } else if ($table instanceof Expr) {
                var_dump($table);exit;
            } else {
                $list[] = $this->escape($table) . ' AS ' . $this->escape($key);
            }
        }
        return implode(",", $list);
    }

    public function Select(Select $select)
    {
        $stmt  = 'SELECT ' . $this->exprList($select->getColumns());
        if ($select->hasTable()) {
            $stmt .= " FROM " . $this->tableList($select->getTables());
        }
        if ($select->hasWhere()) {
            $stmt .= ' WHERE ' . $this->expr($select->getWhere());
        }
        return $stmt;
    }
}
