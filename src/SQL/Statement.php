<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2015-2021 César Rodas
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * -
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * -
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace SQL;

use RuntimeException;
use SQLParser\Stmt\Expr;
use SQLParser\Stmt\ExprList;
use SQLParser\Stmt\VariablePlaceholder;

/**
 * Class Statement.
 *
 * Base statement class with all the things that INSERT, UPDATE,
 * SELECT, DELETE, DROP and any other similar statement may have in
 * common.
 */
abstract class Statement
{
    protected $varValues = [];

    /**
     * @var array
     */
    protected $comments = [];

    /**
     * @var Expr
     */
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

    /**
     * @var Expr|VariablePlaceholder
     */
    protected $limit;

    /**
     * @var Expr|VariablePlaceholder
     */
    protected $offset;

    /**
     * @var array
     */
    protected $joins = [];

    protected $mods = [];

    /**
     * Converts the current statement into an string, using the default writer.
     *
     * @return string
     */
    public function __toString()
    {
        return Writer::create($this, $this->varValues);
    }

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
     * Returns the GROUP BY object.
     *
     * @return null|ExprList
     */
    public function getGroupBy()
    {
        return $this->group;
    }

    /**
     * Returns whether the current expression has any HAVING.
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
     * @return null|Expr
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * Adds GROUP BY to the current object.
     *
     * @return $this
     */
    public function groupBy(ExprList $group, Expr $having = null)
    {
        $this->group  = $group;
        $this->having = $having;

        return $this;
    }

    /**
     * Returns all the available options for the current.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->mods;
    }

    /**
     * Adds options for the current statement.
     *
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
                if (\in_array($id, $mods, true)) {
                    $walk[] = $id;
                }
            }

            if (\count($walk) > 1) {
                throw new RuntimeException('Invalid usage of '.implode(', ', $walk));
            }
        }

        $this->mods = $mods;

        return $this;
    }

    /**
     * Adds JOINs to the current statements.
     *
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

    /**
     * Returns whether the current statement has a WHERE.
     *
     * @return bool
     */
    public function hasWhere()
    {
        return !empty($this->where);
    }

    /**
     * Returns the WHERE expression for this statement.
     *
     * @return null|Expr
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * Adds a WHERE expression to the current statement.
     *
     * @return $this
     */
    public function where(Expr $expr)
    {
        $this->where = $expr;

        return $this;
    }

    /**
     * Returns the OFFSET for the current statement.
     *
     * @return Expr|VariablePlaceholder
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Returns whether the current statement has an OFFSET.
     *
     * @return bool
     */
    public function hasOffset()
    {
        return null !== $this->offset;
    }

    /**
     * Returns whether the current statement has any LIMIT.
     *
     * @return bool
     */
    public function hasLimit()
    {
        return null !== $this->limit;
    }

    /**
     * Returns the LIMIT for the current statement.
     *
     * @return Expr|VariablePlaceholder
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param Expr|VariablePlaceholder      $limit
     * @param null|Expr|VariablePlaceholder $offset
     *
     * @return $this
     */
    public function limit($limit, $offset = null)
    {
        $this->limit  = $limit;
        $this->offset = $offset;

        return $this;
    }

    /**
     * Returns whether the current statement has ORDER BY.
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
     * Adds ORDER BY to the current statement.
     *
     * @return $this
     */
    public function orderBy(ExprList $orderBy)
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * Add a list of comments associated with this statement.
     *
     * @return $this
     */
    public function setComments(array $comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Return a list of comments.
     *
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Iterates recursively over all parts of the current statement. The $callback is called
     * for each value.
     *
     * This function is useful to get information (Expr, Functions, Sub queries) that may exists
     * somewhere in this statement.
     *
     * This function is also useful to replace Statements or Expressions if the callback returns
     * something other than NULL.
     */
    public function iterate(callable $callback)
    {
        foreach ($this as &$value) {
            $this->each($value, $callback);
        }
    }

    /**
     * Returns all the sub queries (SELECT) that exists in the current statement.
     *
     * @return array
     */
    public function getSubQueries()
    {
        $values = [];
        $this->iterate(function ($value) use (&$values) {
            if ($value instanceof Select) {
                $values[] = $value;
            }
        });

        return $values;
    }

    /**
     * Assign values to any variables that may exists.
     *
     * This function do not check if a given variable exists.
     *
     * @return $this
     */
    public function setValues(array $variables)
    {
        $this->varValues = array_merge(
            $this->varValues,
            $variables
        );

        return $this;
    }

    /**
     * Returns all the variables defined in the entire statement or in a given
     * scope.
     *
     * @param null $scope
     *
     * @return array
     */
    public function getVariables($scope = null)
    {
        $vars = [];
        $walk = function ($value) use (&$vars) {
            if ($value instanceof VariablePlaceholder) {
                $vars[] = $value->getName();
            }
        };

        if (null === $scope) {
            $this->iterate($walk);
        } else {
            $this->each($this->{$scope}, $walk);
        }

        return $vars;
    }

    /**
     * Returns all the function calls that may exists in the statements.
     *
     * @return array
     */
    public function getFunctionCalls()
    {
        $vars = [];
        $this->iterate(function ($value) use (&$vars) {
            if ($value instanceof Expr && $value->is('call')) {
                $vars[] = $value;
            }
        });

        return $vars;
    }

    /**
     * Iterates recursively over a given $variable, calling a $callback
     * for each value.
     *
     * This function is also useful to replace Statements or Expressions if the callback returns
     * something other than NULL.
     *
     * @param $variable
     */
    protected function each(&$variable, callable $callback)
    {
        if ($variable instanceof ExprList) {
            $exprs = $variable->getExprs();
            foreach ($exprs as &$value) {
                $this->each($value, $callback);
            }
            $variable->setExprs($exprs);
        } elseif (\is_array($variable)) {
            foreach ($variable as &$value) {
                $this->each($value, $callback);
            }
        } elseif ($variable instanceof Expr) {
            $exprs = $variable->getMembers();
            foreach ($exprs as &$member) {
                $this->each($member, $callback);
            }
            $variable->setMembers($exprs);
        } elseif ($variable instanceof self) {
            foreach ($variable as &$property) {
                $this->each($property, $callback);
            }
        }
        $return = $callback($variable);
        if (null !== $return) {
            $variable = $return;
        }
    }
}
