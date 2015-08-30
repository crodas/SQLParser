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
   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   THE SOFTWARE.
*/
namespace SQLParser\Writer;

use SQLParser\Stmt\Expr;
use SQLParser\Stmt\Alpha;
use SQLParser\Select;
use SQLParser\Table as CreateTable;
use SQLParser\Stmt;

class MySQL extends SQL
{
    public function selectOptions(Select $select)
    {
        $options = $select->getOptions();
        if (!empty($options)) {
            return implode(" ", $options) . " ";
        }
    }

    public function createTable(CreateTable $table)
    {
        $columns = [];
        foreach ($table->getColumns() as $column) {
            $columns[] = $this->columnDefinition($column);
        }

        $keys = array();
        foreach ($table->getIndexes() as $name => $definition) {
            $keys[] = ($definition['unique']  ? "UNIQUE KEY " : "KEY ") . $this->escape($name) . "(" . $this->escape($definition['cols']) . ")";
        }

        $sql = "CREATE TABLE " . $this->value($table->getName()) . "(" 
            . implode(",", array_merge($columns, $keys))
            . ")";
        foreach ($table->getOptions() as $key => $value) {
            $sql .= " $key = $value";
        }
        return $sql;
    }

    public function columnDefinition(Stmt\Column $column)
    {
        $sql = $this->value($column->GetName()) 
            . " "
            . $this->dataType($column->getType(), $column->getTypeSize());


        if ($column->isNotNull()) {
            $sql .= " NOT NULL";
        }

        if ($column->isAutoIncrement()) {
            $sql .= " AUTO_INCREMENT";
        }

        if ($column->defaultValue()) {
            $sql .= " DEFAULT " . $this->value($column->defaultValue());
        }

        if ($column->collate()) {
            $sql .= " COLLATE" . $this->value($column->collate());
        } 

        if ($column->isPrimaryKey()) {
            $sql .= " PRIMARY KEY";
        }

        return $sql;
    }

    public function escape($name)
    {
        if (is_array($name)) {
            foreach ($name as $id => $val) {
                $name[$id] = "`$val`";
            }
            return implode(",", $name);
        }
        return "`$name`";
    }

    public function exprAlpha(Alpha $stmt)
    {
        return "`$stmt`";
    }
}

