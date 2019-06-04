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
use RuntimeException;

/**
 * Class Statement
 *
 * Base statement class with all the things that INSERT, UPDATE,
 * SELECT, DELETE, DROP and any other similar statement may have in
 * common.
 *
 * @package SQL
 */
abstract class Statement
{
    protected $varValues = array();

    protected $comments = array();
    protected $where;

    /**
     * @var ExprList
     */
    protected $orderBy;

    /**
     * @var ExprList
     */
    protected $group;

    /**
     * @var Expr
     */
    protected $having;

    protected $limit;
    protected $offset;

    /**
     * @var array
     */
    protected $joins = [];

    protected $mods = array();

    /**
     * Returns whether the current expression has any GROUP BY object.
     *
     * @return bool
     */
    public function hasGroupBy()
    {
        return !empty($this->group);
    }

    /**
     * Returns the GROUP BY object
     *
     * @return ExprList|null
     */
    public function getGroupBy()
    {
        return $this->group;
    }

    /**
     * Returns whether the current expression has any HAVING
     *
     * @return bool
     */
    public function hasHaving()
    {
        return !empty($this->having);
    }

    /**
     * Returns the current HAVING expression object.
     *
     * @return Expr|null
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * Adds GROUP BY to the current object.
     *
     * @param ExprList $group
     * @param Expr|null $having
     * @return $this
     */
    public function groupBy(ExprList $group, Expr $having = null)
    {
        $this->group  = $group;
        $this->having = $having;
        return $this;
    }

    /**
     * Returns all the available options for the current
     * @return array
     */
    public function getOptions()
    {
        return $this->mods;
    }

    /**
     * Add options for the current statement.
     *
     * @param array $mods
     * @return $this
     */
    public function setOptions(array $mods)
    {
        $rules = [
            ['SQL_CACHE', 'SQL_NO_CACHE'],
            ['ALL', 'DISTINCT', 'DISTINCTROW'],
        ];

        foreach ($rules as $rule) {
            $walk = [];
            foreach ($rule as $id) {
                if (in_array($id, $mods)) {
                    $walk[] = $id;
                }
            }

            if (count($walk) > 1) {
                throw new RuntimeException("Invalid usage of " . implode(", ", $walk));
            }
        }

        $this->mods = $mods;

        return $this;
    }

    /**
     * Add JOINs to the current statements.
     *
     * @param array $joins
     * @return $this
     */
    public function joins(array $joins)
    {
        $this->joins = $joins;
        return $this;
    }

    /**
     * Returns whether the current statement has any JOIN.
     *
     * @return bool
     */
    public function hasJoins()
    {
        return !empty($this->joins);
    }

    /**
     * Returns all the JOINs in the current statement.
     *
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    public function hasWhere()
    {
        return !empty($this->where);
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

    /**
     * Returns whether the current statement has ORDER BY
     *
     * @return bool
     */
    public function hasOrderBy()
    {
        return !empty($this->orderBy);
    }

    /**
     * Returns the ORDER BY object from the current object.
     *
     * @return ExprList
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Add ORDER BY to the current statement.
     *
     * @param ExprList $orderBy
     * @return $this
     */
    public function orderBy(ExprList $orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
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


    public function setComments(array $comments)
    {
        $this->comments = $comments;
        return $this;
    }

    public function getComments()
    {
        return $this->comments;
    }

    protected function each(&$variable, Callable $callback)
    {
        if ($variable instanceof ExprList) {
            $exprs = $variable->getExprs();
            foreach ($exprs as &$value) {
                $this->each($value, $callback);
            }
            $variable->setExprs($exprs);
        } else if (is_array($variable)) {
            foreach ($variable as &$value) {
                $this->each($value, $callback);
            }
        } else if ($variable instanceof Expr) {
            $exprs = $variable->getMembers();
            foreach ($exprs as &$member) {
                $this->each($member, $callback);
            }
            $variable->setMembers($exprs);
        } else if ($variable instanceof self) {
            foreach ($variable as &$property) {
                $this->each($property, $callback);
            }
        }
        $return = $callback($variable);
        if ($return !== NULL) {
            $variable = $return;
        }
    }

    public function iterate(Callable $callback)
    {
        foreach ($this as &$value) {
            $this->each($value, $callback);
        }
    }

    public function getSubQueries()
    {
        $values = array();
        $this->iterate(function($value) use (&$values) {
            if ($value instanceof Select) {
                $values[] = $value;
            }
        });
        return $values;
    }

    public function setValues(array $variables)
    {
        $this->varValues = array_merge($this->varValues, $variables);
        return $this;
    }

    public function getVariables($scope = null)
    {
        $vars = [];
        $walk = function($value) use (&$vars) {
            if ($value instanceof VariablePlaceholder) {
                $vars[] = $value->getName();
            }
        };

        if ($scope === null) {
            $this->iterate($walk);
        } else {
            $this->each($this->$scope, $walk);
        }
        return $vars;
    }

    public function getFunctionCalls()
    {
        $vars = [];
        $this->iterate(function($value) use (&$vars) {
            if ($value instanceof Expr && $value->is('call')) {
                $vars[] = $value;
            }
        });
        return $vars;
    }

    public function __toString()
    {
        return Writer::create($this, $this->varValues);
    }

}
