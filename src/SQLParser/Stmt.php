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

use SQLParser\Stmt\Expr;
use SQLParser\Stmt\ExprList;
use SQLParser\Stmt\VariablePlaceholder;

abstract class Stmt
{
    protected $comments = array();
    protected $table;
    protected $order;
    protected $limit;
    protected $where;
    protected $group;
    protected $having;
    protected $joins = array();

    public function joins(Array $joins)
    {
        $this->joins = $joins;
        return $this;
    }

    public function getTable()
    {
        return $this->table;
    }


    public function getJoins()
    {
        return $this->joins;
    }

    public function getLimit()
    {
        return $this->limit;
    }


    public function hasWhere()
    {
        return !empty($this->where);
    }

    public function getWhere()
    {
        return $this->where;
    }

    public function hasLimit()
    {
        return !empty($this->limit);
    }

    public function hasJoins()
    {
        return !empty($this->joins);
    }

    public function hasOrderBy()
    {
        return !empty($this->order);
    }

    public function getOrderBy()
    {
        return $this->order;
    }

    public function orderBy(ExprList $order)
    {
        $this->order = $order;
        return $this;
    }

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

    public function where(Expr $expr)
    {
        $this->where = $expr;
        return $this;
    }

    public function limit(ExprList $limit)
    {
        $this->limit = $limit;
        return $this;
    }



    public function setComments(Array $comments)
    {
        $this->comments = $comments;
        return $this;
    }

    public function getComment()
    {
        return $this->comments;
    }

    protected function getVars(&$vars, $term)
    {
        if ($term instanceof ExprList) {
            foreach ($term->getTerms() as $value) {
                $this->getVars($vars, $value);
            }
            return;
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
        return Writer\SQL::create($this);
    }

}
