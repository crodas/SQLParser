<?php

namespace SQLParser\Stmt;

use SQL\Statement;

abstract class AlterTable extends Statement
{
    protected $table;
    protected $column;
    protected $position;

    public function getTableName()
    {
        return $this->table;
    }

    public function isFirst()
    {
        return $this->position === TRUE;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function setTableName($table)
    {
        $this->table = $table;
        return $this;
    }
}
