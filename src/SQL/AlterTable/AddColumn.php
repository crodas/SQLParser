<?php

namespace SQL\AlterTable;

use SQLParser\Stmt\Column;

class AddColumn extends AlterTable
{
    public function __construct(Column $column, $position)
    {
        $this->column  = $column;
        $this->positon = $position;
    }
}
