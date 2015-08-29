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
namespace SQLParser;

use SQLParser\Stmt\Alpha;
use SQLParser\Stmt\Expr;
use SQLParser\Stmt\ExprList;
use SQLParser\Stmt\Table;
use RuntimeException;

class Select extends Stmt
{
    protected $fields;
    protected $mods = array();

    public function __construct(ExprList $fields)
    {
        $this->fields = $fields;
    }

    public function getOptions()
    {
        return $this->mods;
    }

    public function setOptions(Array $mods)
    {
        $rules = [
            ['SQL_CACHE', 'SQL_NO_CACHE'],
            ['ALL', 'DISTINCT', 'DISTINCTROW'],
        ];

        foreach ($rules as $rule) {
            $check = [];
            foreach ($rule as $id) {
                if (in_array($id, $mods)) {
                    $check[] = $id;
                }
            }

            if (count($check) > 1) {
                throw new RuntimeException("Invalid usage of " . implode(", ", $check));
            }
        }

        $this->mods = $mods;

        return $this;
    }

    public function from(Array $table)
    {
        $this->table = $table;
        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    } 

}
