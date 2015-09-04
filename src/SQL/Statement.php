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

use SQLParser\Stmt\VariablePlaceholder;
use SQLParser\Stmt\ExprList;
use SQLParser\Stmt\Expr;

class Statement
{
    protected $comments = array();
    protected $where;
    protected $orderBy;
    protected $limit;
    protected $offset;
    protected $joins;
    protected $mods = array();
    protected $group;

    public function hasGroupBy()
    {
        return !empty($this->group);
    }

    public function getGroupBy()
    {
        return $this->group;
    }

    public function groupBy(ExprList $group, $having)
    {
        $this->group  = $group;
        $this->having = $having;
        return $this;
    }

    public function getHaving()
    {
        return $this->having;
    }

    public function hasHaving()
    {
        return !empty($this->having);
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
                throw new \RuntimeException("Invalid usage of " . implode(", ", $check));
            }
        }

        $this->mods = $mods;

        return $this;
    }


    public function hasWhere()
    {
        return !empty($this->where);
    }

    public function joins(Array $joins)
    {
        $this->joins = $joins;
        return $this;
    }

    public function hasJoins()
    {
        return !empty($this->joins);
    }

    public function getJoins()
    {
        return $this->joins;
    }

    public function getWhere()
    {
        return $this->where;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function hasOffset()
    {
        return $this->offset !== NULL;
    }

    public function hasLimit()
    {
        return $this->limit !== NULL;
    }

    public function Offset()
    {
        return $this->offset;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function hasOrderBy()
    {
        return !empty($this->orderBy);
    }

    public function orderBy($orderBy)
    {
        if (is_string($orderBy)) {
            die('here');
        }
        $this->orderBy = $orderBy;
        return $this;
    }

    public function getOrderBy()
    {
        return $this->orderBy;
    }

    public function limit($limit, $offset = NULL)
    {
        foreach (['limit', 'offset'] as $var) {
            if ($$var instanceof Expr) {
                $$var = $$var->getValue();
            }
            $this->$var = $$var;
        }

        return $this;
    }

    public function where($expr)
    {
        if (is_string($expr)) {
            die($expr);
        }

        $this->where = $expr;

        return $this;
    }


    public function setComments(Array $comments)
    {
        $this->comments = $comments;
        return $this;
    }

    public function getComments()
    {
        return $this->comments;
    }

    protected function getVars(&$vars, $term)
    {
        if ($term instanceof ExprList) {
            foreach ($term->getExprs() as $value) {
                $this->getVars($vars, $value);
            }
            return;
        } else if (is_array($term)) {
            foreach ($term as $value) {
                $this->getVars($vars, $value);
            }
        } else if ($term instanceof Expr) {
            foreach ($term->getMembers() as $member) {
                $this->getVars($vars, $member);
            }
        } else if ($term instanceof self) {
            foreach ($term as $property) {
                $term->getVars($vars, $property);
            }
        } else if ($term instanceof VariablePlaceholder) {
            $vars[] = $term->getName();
        }
    }

    public function getVariables()
    {
        $vars = [];
        foreach ($this as $key => $value) {
            $this->getVars($vars, $value);
        }
        return $vars;
    }

    public function __toString()
    {
        return Writer::create($this);
    }

}
