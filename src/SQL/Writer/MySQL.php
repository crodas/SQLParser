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

namespace SQL\Writer;

use SQL\BeginTransaction;
use SQL\CommitTransaction;
use SQL\Insert;
use SQL\RollbackTransaction;
use SQL\Select;
use SQL\Table;
use SQL\Writer;
use SQLParser\Stmt;

/**
 * MySQL Query writer.
 *
 * This class extends the standard SQL writer to add MySQL flavoured
 * SQL statements.
 *
 * Class MySQL
 */
class MySQL extends Writer
{
    /**
     * Returns "escaped" value.
     *
     * @param $value
     *
     * @return string
     */
    public function escape($value)
    {
        if (!\is_string($value)) {
            return $this->value($value);
        }

        return "`{$value}`";
    }

    /**
     * Returns any special "option" defined in the query. Options are
     * unique to MySQL.
     *
     * @return string
     */
    public function selectOptions(Select $select)
    {
        $options = $select->getOptions();
        if (!empty($options)) {
            return implode(' ', $options) . ' ';
        }
    }

    /**
     * Returns the SQL statement for a commit or a save point (for nested transactions).
     *
     * @return string
     */
    public function commit(CommitTransaction $transaction)
    {
        if ($transaction->getName()) {
            return 'RELEASE SAVEPOINT ' . $this->escape($transaction->getName());
        }

        return 'COMMIT WORK';
    }

    /**
     * Returns the SQL statement for rollback a transaction or a save point (for nested transactions).
     *
     * @return string
     */
    public function rollback(RollbackTransaction $transaction)
    {
        if ($transaction->getName()) {
            return 'ROLLBACK TO ' . $this->escape($transaction->getName());
        }

        return 'ROLLBACK WORK';
    }

    /**
     * Returns the SQL statement for start a transaction or any save point (for nested transactions).
     *
     * @return string
     */
    public function begin(BeginTransaction $transaction)
    {
        if ($transaction->getName()) {
            return 'SAVEPOINT ' . $this->escape($transaction->getName());
        }

        return 'BEGIN WORK';
    }

    /**
     * Returns the SQL statement for creating a table.
     *
     * Create statements in MySQL are special because they may include indexes
     * definitions within the same CREATE TABLE statement.
     *
     * @return string
     */
    public function createTable(Table $table)
    {
        $columns = [];
        foreach ($table->getColumns() as $column) {
            $columns[] = $this->columnDefinition($column);
        }

        $primaryKey = [];
        foreach ($table->getPrimaryKey() as $column) {
            $primaryKey[] = $this->escape($column->getName());
        }

        $keys = [];
        if (!empty($primaryKey)) {
            $keys[] = 'PRIMARY KEY(' . implode(', ', $primaryKey) . ')';
        }

        foreach ($table->getIndexes() as $name => $definition) {
            $keys[] = ($definition['unique'] ? 'UNIQUE KEY ' : 'KEY ')
                . $this->escape($name) . '(' . $this->exprList($definition['cols']) . ')';
        }

        $sql = 'CREATE TABLE ' . $this->escape($table->getName()) . '('
            . implode(',', array_merge($columns, $keys))
            . ')';
        foreach ($table->getOptions() as $key => $value) {
            $sql .= " {$key} = {$value}";
        }

        return $sql;
    }

    /**
     * Returns the SQL statement for an INSERT.
     *
     * This method adds MySQL support ON DUPLICATE KEY UPDATE.
     *
     * @return string
     */
    public function insert(Insert $insert)
    {
        $stmt = parent::insert($insert);
        if ($insert->getOnDuplicate()) {
            $stmt .= ' ON DUPLICATE KEY UPDATE ' . $this->exprList($insert->getOnDuplicate());
        }

        return $stmt;
    }

    /**
     * Returns SQL statements for definition of columns.
     *
     * @return string
     */
    public function columnDefinition(Stmt\Column $column)
    {
        $sql = $this->escape($column->GetName())
            . ' '
            . $this->dataType($column->getType(), $column->getTypeSize())
            . $column->getModifier();

        if ($column->isNotNull()) {
            $sql .= ' NOT NULL';
        }

        if ($column->isAutoIncrement()) {
            $sql .= ' AUTO_INCREMENT';
        }

        if ($column->defaultValue()) {
            $sql .= ' DEFAULT ' . $this->value($column->defaultValue());
        }

        if ($column->collate()) {
            $sql .= ' COLLATE' . $this->value($column->collate());
        }

        return $sql;
    }

    /**
     * Write MySQL GLOB equivalent statement using LIKE BINARY.
     *
     * @return string
     */
    public function exprGlob(Stmt\Expr $expr)
    {
        $members = [];
        foreach ($expr->getMembers() as $part) {
            $members[] = $this->value($part);
        }

        return "{$members[0]} LIKE BINARY {$members[1]}";
    }

    /**
     * Write MySQL NOT GLOB equivalent statement using NOT LIKE BINARY.
     *
     * @return string
     */
    public function exprNotGlob(Stmt\Expr $expr)
    {
        $members = [];
        foreach ($expr->getMembers() as $part) {
            $members[] = $this->value($part);
        }

        return "{$members[0]} NOT LIKE BINARY {$members[1]}";
    }
}
