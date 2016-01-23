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

class Column
{
    protected $name;
    protected $type;
    protected $size;
    protected $pk = false;
    protected $notNull = false;
    protected $default;
    protected $collate;
    protected $autoincrement = false;
    protected $modifier;

    public function __construct($name, $type, $size = null, $modifier = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->modifier = $modifier;
    }

    public function getModifier()
    {
        return $this->modifier;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function collate($expr = null)
    {
        if ($expr === null) {
            return $this->collate;
        }
        $this->collate = $expr;
        return $this;
    }

    public function defaultValue(Expr $value = null)
    {
        if ($value === null) {
            return $this->default;
        }
        $this->default = $value;
        return $this;
    }

    public function getTypeSize()
    {
        return $this->size;
    }

    public function isPrimaryKey()
    {
        return $this->pk;
    }


    public function primaryKey()
    {
        $this->pk = true;
        return true;
    }

    public function isNotNull()
    {
        return (bool)$this->notNull;
    }

    public function notNull()
    {
        $this->notNull = true;
        return $this;
    }

    public function isAutoincrement()
    {
        return $this->autoincrement;
    }

    public function autoincrement()
    {
        $this->autoincrement = true;
        return $this;
    }
}
