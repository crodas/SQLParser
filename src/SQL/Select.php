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

use SQLParser\Stmt\Expr;
use SQLParser\Stmt\Join;

/**
 * Class Select.
 */
class Select extends Statement
{
    /**
     * @var array
     */
    protected $tables = [];

    /**
     * @var array
     */
    protected $columns;

    public function __construct(array $expr = null)
    {
        foreach ($expr as $i => $e) {
            if ('VALUE' === $e[0]->getType()) {
                $parts = $e[0]->getMembers();
                if (2 === \count($parts) && 2 === $parts[1]) {
                    $expr[$i][0] = new Expr('column', $parts[0]);
                }
            }
        }

        $this->columns = $expr;
    }

    /**
     * Adds tables for the current SELECT.
     *
     * @return $this
     */
    public function setTables(array $tables)
    {
        $this->tables = $tables;

        return $this;
    }

    /**
     * Returns whether the current statement has any table.
     *
     * @return bool
     */
    public function hasTable()
    {
        return !empty($this->tables);
    }

    /**
     * Returns the list of tables associated with this statement.
     *
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Returns all the tables in the statement.
     *
     * This function will return all tables, including JOINs and
     * sub-queries.
     *
     * @return array
     */
    public function getAllTables()
    {
        $tables = $this->tables;

        $this->iterate(function ($stmt) use (&$tables) {
            if ($stmt instanceof self) {
                $tables = array_merge($tables, $stmt->getAllTables());
            } elseif ($stmt instanceof Join) {
                $tables[] = $stmt->getTable();
            }
        });

        foreach ($tables as $id => $table) {
            if ($table instanceof self) {
                unset($tables[$id]);
                $tables = array_merge($tables, $table->getAllTables());
            }
        }

        return array_unique(array_values($tables));
    }

    /**
     * Adds a table to the current statement.
     *
     * @param string      $table
     * @param null|string $alias
     *
     * @return $this
     */
    public function from($table, $alias = null)
    {
        if (null === $alias) {
            $alias = \count($this->tables);
        }
        $this->tables[$alias] = $table;

        return $this;
    }

    /**
     * Returns the list of columns in this statement.
     *
     * @return null|array
     */
    public function getColumns()
    {
        return $this->columns;
    }
}
