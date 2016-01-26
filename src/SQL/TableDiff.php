<?php

namespace SQL;

use SQLParser;
use SQLParser\Stmt\ExprList;
use SQLParser\Stmt\Column;
use RuntimeException;

class TableDiff
{
    protected $parser;

    public function __construct()
    {
        $this->parser = new SQLParser;
    }

    public function getIndexChanges(Table $old, Table $current)
    {
        $changes = array();
        $old = $old->getIndexes();
        $new = $current->getIndexes();

        foreach ($old as $name => $index) {
            if (empty($new[$name]) || $new[$name]['cols'] != $index['cols']) {
                $changes[] = new AlterTable\DropIndex($name);
                unset($old[$name]);
            }
        }

        foreach($new as $name => $index) {
            if (empty($old[$name])) {
                $changes[] = new AlterTable\AddIndex($index['unique'] ? 'UNIQUE' : '', $name, ExprList::fromArray($index['cols']));
            }
        }

        return $changes;
    }

    protected function compareTypes(Column $a, Column $b)
    {
        $checks = array('getType', 'getTypeSize', 'defaultValue', 'collate');
        foreach ($checks as $check) {
            if ($a->$check() !== $b->$check()) {
                return false;
            }
        }
        return true;
    }

    public function getColumnChanges(Table $old, Table $current)
    {
        $changes = array();
        $oldColumns = $old->getColumns();
        $newColumns = $current->getColumns();
        foreach ($newColumns as $position => $column) {
            if (empty($oldColumns[$position])) {
                $changes[] = new AlterTable\AddColumn($column, NULL);
                continue;
            }
            $oldColumn = $oldColumns[$position];
            if ($column->getName() !== $oldColumn->getName() || $this->compareTypes($column, $oldColumn)) {
                $changes[] = new AlterTable\ChangeColumn($oldColumn->getName(), $column, NULL);
            }
        }

        return $changes;
    }

    public function diff($oldSQL, $newSQL)
    {
        $old     = $this->parser->parse($oldSQL);
        $current = $this->parser->parse($newSQL);
        $changes = array();

        if (count($old) !== 1 || count($old) !== count($current)) {
            throw new RuntimeException("We expect a single SQL");
        }

        if ($old[0]->getName() !== $current[0]->getName()) {
            $changes[] = new AlterTable\RenameTable($current[0]->getName()); 
        }
        
        $changes = array_merge(
            $changes,
            $this->getColumnChanges($old[0], $current[0]),
            $this->getIndexChanges($old[0], $current[0])
        );

        foreach ($changes as $change) {
            if (!$change->getTableName()) {
                $change->setTableName($current[0]->getName());
            }
        }

        return $changes;
    }
}
