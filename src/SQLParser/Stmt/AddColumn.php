<?php

namespace SQLParser\Stmt;

class AddColumn extends AlterTable
{
    public function __construct(Column $column, $position)
    {
        $this->column  = $column;
        $this->positon = $position;
    }
}
