<?php

namespace SQL\AlterTable;

class DropIndex extends AlterTable
{
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getIndexName()
    {
        return $this->name;
    }
}

