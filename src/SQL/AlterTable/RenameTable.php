<?php

namespace SQL\AlterTable;

class RenameTable extends AlterTable
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getTableName()
    {
        return $this->name;
    }
}

