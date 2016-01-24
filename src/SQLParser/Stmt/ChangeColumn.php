<?php

namespace SQLParser\Stmt;

class ChangeColumn extends AlterTable
{
    protected $oldName;

    public function getOldName()
    {
        return $this->oldName;
    }

    public function __construct($oldName, Column $column, $position)
    {
        $this->oldName  = $oldName;
        $this->column   = $column;
        $this->position = $position; 
    }
}
