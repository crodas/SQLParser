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

class Join
{
    protected $type;
    protected $prefix;
    protected $sufix;
    protected $table;
    protected $on;
    protected $using;

    public function __construct($type, $prefix = '', $sufix = '')
    {
        $this->type   = strtoupper($type);
        $this->prefix = strtoupper($prefix);
        $this->sufix  = strtoupper($sufix);
    }

    public function getTable()
    {
        return $this->table;
    }

    public function hasCondition()
    {
        return !empty($this->on ?: $this->using);
    }

    public function getCondition()
    {
        return $this->on ?: $this->using;
    }

    public function hasOn()
    {
        return !empty($this->on);
    }

    public function hasUsing()
    {
        return !empty($this->using);
    }

    public function getType()
    {
        return trim($this->prefix . " " . $this->type . " " . $this->sufix) . " JOIN";
    }

    public function setTable(Table $table)
    {
        $this->table = $table;
        return $this;
    }

    public function using(ExprList $expr)
    {
        $this->on = null;
        $this->using = $expr;
        return $this;
    }
    
    public function on($expr)
    {
        $this->using = null;
        $this->on = $expr;
        return $this;
    }
}
