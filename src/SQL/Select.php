<?php

namespace SQL;

use SQLParser\Stmt\Expr;

class Select 
{
    protected $tables = array();
    protected $columns;
    protected $where;
    protected $orderBy;
    protected $limit;
    protected $offset;
    protected $variables = array();

    public function limit($limit, $offset = NULL)
    {
        foreach (['limit', 'offset'] as $var) {
            if ($$var instanceof Expr) {
                $$var = $$var->getValue();
            }
            $this->$var = $$var;
        }

        return $this;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    public function where($expr)
    {
        if (is_string($expr)) {
            die($expr);
        }

        $this->where = $expr;

        return $this;
    }

    public function setTables(Array $tables)
    {
        $this->tables = $tables;
        return $this;
    }

    public function hasTable()
    {
        return !empty($this->tables);
    }

    public function hasLimit()
    {
        return !empty($this->limit);
    }

    public function hasOrderBy()
    {
        return !empty($this->orderBy);
    }

    public function from($table, $alias = '')
    {
        $alias = $alias ? $alias : $table;
        $this->tables[$alias] = $table;
        return $this;
    }

    public function getTables()
    {
        return $this->tables;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function __construct($expr = NULL)
    {
        if (is_string($expr)) {
            die($expr);
        }

        $this->columns = $expr;
    }

    public function hasWhere()
    {
        return !empty($this->where);
    }


    public function getWhere()
    {
        return $this->where;
    }

}
