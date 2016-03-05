<?php
/*
   The MIT License (MIT)

   Copyright (c) 2015 César Rodas

   Permission is hereby granted, free of charge, to any person obtaining a copy
   of this software and associated documentation files (the "Software"), to deal
   in the Software without restriction, including without limitation the rights
   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   copies of the Software, and to permit persons to whom the Software is
   furnished to do so, subject to the following conditions:

   The above copyright notice and this permission notice shall be included in
   all copies or substantial portions of the Software.

   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   AUTHORS OR COPYRIGHT HnewERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   THE SOFTWARE.
*/
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
            if ($a->$check() != $b->$check()) {
                return true;
            }
        }
        return false;
    }

    protected function getColumns(Table $table)
    {
        $columns = $table->getColumns();
        $names   = array();

        foreach ($columns as $id => $column) {
            $column->position = $id;
            $names[$column->getName()] = $column;
        }

        return array($columns, $names);
    }

    public function getColumnChanges(Table $old, Table $current)
    {
        $changes = array();
        list($oldColumns, $oldNames) = $this->getColumns($old);
        list($newColumns, $newNames) = $this->getColumns($current);

        foreach ($newNames as $name => $column) {
            $after = $column->position === 0 ? TRUE : $newColumns[$column->position-1]->getName();
            unset($column->position);
            if (empty($oldNames[$name])) {
                $changes[] = new AlterTable\AddColumn($column, $after);
                continue;
            }
            if ($this->compareTypes($column, $oldNames[$name])) {
                $changes[] = new AlterTable\ChangeColumn($name, $column, $after);
            }
        }

        foreach ($oldNames as $name => $column) {
            if (empty($newNames[$name])) {
                $changes[] = new AlterTable\DropColumn($name);
            }
        }
        

        return $changes;
    }

    public function getTable($sql)
    {
        $stmts = $this->parser->parse($sql);
        if (!($stmts[0] instanceof Table)) {
            throw new RuntimeException("Expecting a CREATE TABLE Statement, got " . get_class($stmts[0]) . ' class');
        }

        $table = array_shift($stmts);
        foreach ($stmts as $stmt) {
            if ($stmt instanceof AlterTable\AddIndex) {
                $table->addIndex($stmt);
            }
        }

        return $table;
    }

    public function diff($oldSQL, $newSQL)
    {
        $old     = $this->getTable($oldSQL);
        $current = $this->getTable($newSQL);
        $changes = array();

        if ($old->getName() !== $current->getName()) {
            $rename = new AlterTable\RenameTable($current->getName()); 
            $rename->setTableName($old->getName());
            $changes[] = $rename;
        }
        
        $changes = array_merge(
            $changes,
            $this->getColumnChanges($old, $current),
            $this->getIndexChanges($old, $current)
        );

        foreach ($changes as $change) {
            if (!$change->getTableName()) {
                $change->setTableName($current->getName());
            }
        }

        return $changes;
    }
}
