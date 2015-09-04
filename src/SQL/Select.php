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
namespace SQL;

use SQLParser\Stmt\Expr;
use SQLParser\Stmt\VariablePlaceholder;
use SQLParser\Stmt\ExprList;

class Select extends Statement
{
    protected $tables = array();
    protected $columns;

    public function setTables(Array $tables)
    {
        $this->tables = $tables;
        return $this;
    }

    public function hasTable()
    {
        return !empty($this->tables);
    }

    public function from($table, $alias = '')
    {
        $alias = $alias ? $alias : $table;
        $this->tables[$alias] = $table;
        return $this;
    }

    public function getTables()
    {
        $tables = array();
        foreach ($this->tables as $id => $table) {
            if ($id === $table) {
                $tables[] = $table;
            } else {
                $tables[$id] = $table;
            }
        }
        return $tables;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function __construct($expr = NULL)
    {
        if (is_string($expr)) {
            die($expr);
        }

        $this->columns = $expr;
    }

}
