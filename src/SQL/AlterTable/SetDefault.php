<?php

namespace SQL\AlterTable;

use SQLParser\Stmt\Column;

class SetDefault extends AlterTable
{
    protected $value;

    public function __construct($columnName, $value)
    {
        $this->column = $columnName;
        $this->value  = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
