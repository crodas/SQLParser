<?php

namespace SQL\AlterTable;

class DropColumn extends AlterTable
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getColumnName()
    {
        return $this->name;
    }
}
