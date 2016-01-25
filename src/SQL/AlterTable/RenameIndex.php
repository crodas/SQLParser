<?php

namespace SQL\AlterTable;

use SQLParser\Stmt\Column;

class RenameIndex extends AlterTable
{
    protected $oldName;
    protected $newName;

    public function getOldName()
    {
        return $this->oldName;
    }

    public function getNewName()
    {
        return $this->newName;
    }

    public function __construct($oldName, $newName)
    {
        $this->oldName = $oldName;
        $this->newName = $newName;
    }
}
