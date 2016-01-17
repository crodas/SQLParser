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

class Expr
{
    protected $type;
    protected $members = array();

    public function __construct()
    {
        $args = func_get_args();
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }
        foreach ($args as $id => $value) {
            if ($id === 0) {
                $this->type = strtoupper($value);
            } else {
                $this->members[] = $value;
            }
        }
    }

    public function getValue()
    {
        if ($this->type === "VALUE") {
            return $this->members[0];
        }

        return $this;
    }

    public function is($type)
    {
        return strtoupper($type) === $this->type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMember($id)
    {
        if (is_numeric($id) && $id < 0) {
            $id = count($this->members) + $id;
        }
        if (!array_key_exists($id, $this->members)) {
            return NULL;
        }
        return $this->members[$id];
    }

    public function setMembers(Array $members)
    {
        $this->members = $members;
        return $this;
    }

    public function getMembers()
    {
        return $this->members;
    }

}
