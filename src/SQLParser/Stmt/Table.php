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
namespace SQLParser\Stmt;

use SQLParser\Stmt\Expr;
use SQLParser\Select;
use InvalidArgumentException;

class Table
{
    public function __construct($table, $alias = null)
    {
        if (!($table instanceof Expr || $table instanceof Select)) {
            throw new InvalidArgumentException("Table must be Alpha or Select objects");
        }
        $this->table = $table;
        $this->alias = $alias;
    }

    public function getAlias()
    {
        return $this->alias ? $this->alias->getMember(0) : NULL;
    }

    public function setAlias($alias)
    {
        if (!$alias) {
            $this->alias = null;
        } else {
            $this->alias = $alias instanceof Expr ? $alias : new Expr("ALPHA", $alias);
        }
        return $this;
    }

    public function getMembers()
    {
        return [$this->table, $this->alias];
    }

    public function setValue($name)
    {
        if (is_scalar($name) || is_array($name)) {
            $expr = array("COLUMN");
            foreach ((array)$name as $part) {
                $expr[] = new Expr("ALPHA", $part);
            }
            $this->table = new Expr($expr);
        } else if ($name instanceof Select) {
            $this->table = $name;
        } else {
            throw new InvalidArgumentException("\$name should be string, Alpha or Select objects");
        }
        return $this;
    }

    public function getValue()
    {
        if ($this->table instanceof Select) {
            return $this->table;
        }

        $parts = array();
        foreach ($this->table->getMembers() as $member) {
            $parts[] = $member->getMember(0);
        }

        return count($parts) == 1 ? $parts[0] : $parts;
    }
}
