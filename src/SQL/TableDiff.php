<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2015-2021 César Rodas
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * -
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * -
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace SQL;

use InvalidArgumentException;
use SQLParser;
use SQLParser\Stmt\Column;
use SQLParser\Stmt\ExprList;

/**
 * Class TableDiff.
 *
 * This class allows to do a "table diff" between two CREATE TABLE statements, returning an
 * array of changes for table A to become table B.
 */
class TableDiff
{
    /**
     * @var SQLParser
     */
    protected $parser;

    /**
     * TableDiff constructor.
     */
    public function __construct()
    {
        $this->parser = new SQLParser();
    }

    /**
     * Returns an array with DROP INDEX and CREATE INDEX statements,
     * the list of statements are the differences between two tables.
     *
     * @return array
     */
    public function getIndexChanges(Table $old, Table $current)
    {
        $changes = [];
        $old     = $old->getIndexes();
        $new     = $current->getIndexes();

        foreach ($old as $name => $index) {
            if (empty($new[$name]) || $new[$name]['cols'] !== $index['cols']) {
                $changes[] = new AlterTable\DropIndex($name);
                unset($old[$name]);
            }
        }

        foreach ($new as $name => $index) {
            if (empty($old[$name])) {
                $changes[] = new AlterTable\AddIndex(
                    $index['unique'] ? 'UNIQUE' : '',
                    $name,
                    ExprList::fromArray($index['cols'])
                );
            }
        }

        return $changes;
    }

    /**
     * Returns an arrays with ADD COLUMN and CHANGE COLUMN that are needed
     * for table $old to become table $new.
     *
     * @return array
     */
    public function getColumnChanges(Table $old, Table $current)
    {
        $changes  = [];
        $oldNames = $this->getColumns($old);
        $newNames = $this->getColumns($current);

        foreach ($newNames as $name => $column) {
            $after = empty($after) ? null : $after;
            if (empty($oldNames[$name])) {
                $changes[] = new AlterTable\AddColumn($column, $after);
            } elseif (!$this->compareColumns($column, $oldNames[$name])) {
                $changes[] = new AlterTable\ChangeColumn($name, $column, $after);
            }

            $after = $column->getName();
        }

        foreach ($oldNames as $name => $column) {
            if (empty($newNames[$name])) {
                $changes[] = new AlterTable\DropColumn($name);
            }
        }

        return $changes;
    }

    /**
     * Returns a table object from a given $sql statement. Any aditional
     * CREATE INDEX that may exists will be added to the current $table.
     *
     * @param string $sql
     *
     * @return Table
     */
    public function getTable($sql)
    {
        $stmts = $this->parser->parse($sql);
        if (!($stmts[0] instanceof Table)) {
            throw new InvalidArgumentException('Expecting a CREATE TABLE Statement, got ' . \get_class($stmts[0]) . ' class');
        }

        $table = array_shift($stmts);
        foreach (array_filter($stmts) as $stmt) {
            if ($stmt instanceof AlterTable\AddIndex && $stmt->getTableName() === $table->getName()) {
                $table->addIndex($stmt);
            }
        }

        return $table;
    }

    /**
     * Returns an array of SQL statements needed for table defined in $oldSQL to look
     * like table defined in $newSQL.
     *
     * @param string $oldSQL
     * @param string $newSQL
     *
     * @return array
     */
    public function diff($oldSQL, $newSQL)
    {
        $old     = $this->getTable($oldSQL);
        $current = $this->getTable($newSQL);
        $changes = [];

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

    /**
     * Compares two columns objects and return TRUE if they are the same.
     *
     * @return bool
     */
    protected function compareColumns(Column $a, Column $b)
    {
        $checks = ['getType', 'getTypeSize', 'defaultValue', 'collate'];
        foreach ($checks as $check) {
            if ($a->{$check}() !== $b->{$check}()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns a list of columns.
     *
     * @return array
     */
    protected function getColumns(Table $table)
    {
        $columns = [];

        foreach ($table->getColumns() as $id => $column) {
            $columns[$column->getName()] = $column;
        }

        return $columns;
    }
}
