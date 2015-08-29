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
namespace SQLParser\Writer;

use SQLParser\Stmt;
use SQLParser\Stmt\Table;
use SQLParser\Drop;
use SQLParser\Delete;
use SQLParser\Select;
use SQLParser\View;
use SQLParser\Update;
use SQLParser\Insert;
use RuntimeException;
use InvalidArgumentException;

class SQL
{
    protected static $instance;

    /**
     *  Set Write instance
     *
     *  Change the Writer instance to generate SQL-compatible
     *  with another engine.
     */
    final public static function setInstance(self $instance)
    {
        self::$instance = $instance;
    }

    final public static function create($object)
    {
        if (empty(self::$instance)) {
            self::getInstance();
        }

        if ($object instanceof Select) {
            return self::$instance->select($object);
        } else if ($object instanceof View) {
            return self::$instance->view($object);
        } else if ($object instanceof Insert) {
            return self::$instance->insert($object);
        } else if ($object instanceof Update) {
            return self::$instance->update($object);
        } else if ($object instanceof Drop) {
            return self::$instance->drop($object);
        } else if ($object instanceof Delete) {
            return self::$instance->delete($object);
        }

    }

    final public static function getInstance()
    {
        self::$instance = self::$instance ?: new self;
        return self::$instance;
    }

    public function expr(Stmt\Expr $expr)
    {
        $method = 'expr' . $expr->getType();
        if (is_callable([$this, $method])) {
            return $this->$method($expr);
        }

        $member = array();
        foreach ($expr->GetMembers() as $part) {
            $member[] = $this->value($part);
        }

        $type = $expr->getType();
        switch ($type) {
        case 'COLUMN':
            return implode(".", $member);

        case 'ALPHA':
            return $member[0];

        case 'CASE':
            $else = NULL;
            if ($expr->getMember(-1) instanceof Stmt\Expr) {
                $else = array_pop($member);
            }
            $stmt = "CASE " . implode(" ", $member);
            if ($else !== NULL) {
                $stmt .= " ELSE " . $else;
            }
            $stmt .= " END";
            return $stmt;
        case 'WHEN':
            return "WHEN {$member[0]} THEN {$member[1]}";

        case 'ALIAS':
            return "{$member[0]} AS {$member[1]}";
        case 'ALL':
            return "*";
        case 'CALL':
            return $expr->getMember(0) . "({$member[1]})";
        case 'ASC':
        case 'DESC':
            return "{$member[0]} {$type}";
        case 'IN':
            return "{$member[0]} IN ({$member[1]})";
        case 'EXPR':
            return "({$member[0]})";
        case 'EMPTY':
            return '';
        case 'VALUE': 
            return $member[0];
        case 'NOT':
            return "NOT {$member[0]}";
        case 'TIMEINTERVAL':
            return "INTERVAL {$member[0]} " . $expr->getMember(1);
        }

        return "{$member[0]} {$type} {$member[1]}";
    }

    public function variable(Stmt\VariablePlaceholder $stmt)
    {
        $name = $stmt->getName();
        return $name != "?"  ? ":{$name}" : "?";
    }

    public function drop(Drop $drop)
    {
        return "DROP " . $drop->getType() . " " . $this->tableName($drop->getTable());
    }

    public function delete(Delete $delete)
    {
        $stmt  = "DELETE FROM " . $this->tableName($delete->getTable());
        $stmt .= $this->where($delete);
        $stmt .= $this->orderBy($delete);
        $stmt .= $this->limit($delete);

        return $stmt;
    }

    protected function value($value)
    {
        $map = [
            'SQLParser\Select' => 'select',
            'SQLParser\Stmt\Expr' => 'expr',
            'SQLParser\Stmt\ExprList' => 'exprList',
            'SQLParser\Stmt\VariablePlaceholder' => 'variable',
        ];

        foreach ($map as $class => $callback) {
            if ($value instanceof $class) {
                return $this->$callback($value);
            }
        }

        if ($value === null) {
            return 'NULL';
        }

        if (!is_scalar($value)) {
            throw new InvalidArgumentException;
        }

        if ($value === 'EMTPY') {
            return '';
        }

        return var_export($value, true);
    }

    protected function tableName($table)
    {
        if (is_array($table)) {
            $tables = array();
            foreach ($table as $t) {
                $tables[] = $this->tableName($t);
            }
            return implode(",", $tables);
        }
        if (!($table instanceof Table)) {
            throw new InvalidArgumentException("Expecting a table object");
        }
        $table = $table->getMembers();
        $stmt  = $table[0] instanceof Select ? "(" . $this->select($table[0]) . ")" : $this->expr($table[0]);
        if ($table[1]) {
            $stmt .= " AS " . $this->expr($table[1]);
        }

        return $stmt;
    }

    protected function exprList(Stmt\ExprList $list, $prefix = '', $postfix = '')
    {
        $fields = [];
        $inner  = false;
        foreach ($list->getTerms() as $field) {
            if ($field instanceof Stmt\ExprList) {
                $fields[] = $this->exprList($field, $prefix, $postfix);
                $inner    = true;
                continue;
            }

            $fields[] = $this->value($field);
        }

        if ($inner) {
            $prefix  = '';
            $postfix = '';
        }

        return $prefix . implode(",", $fields) . $postfix;
    }

    public function join(Stmt\Join $join)
    {
        $str  = $join->getType() . " " . $this->tableName($join->getTable());
        if ($join->hasCondition()) {
            $str .= $join->hasOn() ? " ON " : " USING ";
            $str .= $this->value($join->getCondition());
        }

        return $str;
    }

    public function joins(Stmt $stmt) 
    {
        if (!$stmt->hasJoins()) {
            return "";
        }
        $joins = $stmt->getJoins(); 
        $stmt  = [];
        foreach ($joins as $join) {
            $stmt[] = $this->join($join);
        }

        return " " . implode(" ", $stmt);
    }

    public function update(Update $update)
    {
        $stmt  = 'UPDATE ' . $this->tableName($update->getTable());
        $stmt .= $this->joins($update);
        $stmt .= " SET " . $this->exprList($update->getSet());
        $stmt .= $this->where($update);
        $stmt .= $this->orderBy($update);
        $stmt .= $this->limit($update);
        return $stmt;
    }

    public function groupBy(Stmt $stmt)
    {
        if ($stmt->hasGroupBy()) {
            $str = " GROUP BY " . $this->value($stmt->getGroupBy());
            if($stmt->hasHaving()) {
                $str .= " HAVING ". $this->value($stmt->getHaving());
            }

            return $str;
        }
    }

    public function limit(Stmt $stmt)
    {
        if ($stmt->hasLimit()) {
            return " LIMIT " . $this->exprList($stmt->getLimit());
        }
        return "";
    }

    public function where(Stmt $stmt)
    {
        if ($stmt->hasWhere()) {
            return " WHERE " . $this->expr($stmt->getWhere());
        }

        return "";
    }

    public function orderBy(Stmt $stmt)
    {
        if ($stmt->hasOrderBy()) {
            return " ORDER BY " . $this->exprList($stmt->getOrderBy());
        }

        return "";
    }

    public function selectOptions(Select $select)
    {
        $options = array_intersect(
            array("ALL", "DISTINCT", "DISTINCTROW"),
            $select->getOptions()
        );

        if (!empty($options)) {
            return implode(" ", $options) . " ";
        }
    }

    public function select(Select $select)
    {
        $stmt  = 'SELECT ';
        $stmt .= $this->selectOptions($select);
        $stmt .= $this->exprList($select->getFields());
        if ($select->getTable()) {
            $stmt .= " FROM " . $this->tableName($select->getTable());
        }
        $stmt .= $this->joins($select);
        $stmt .= $this->where($select);
        $stmt .= $this->groupBy($select);
        $stmt .= $this->orderBy($select);
        $stmt .= $this->limit($select);
        return $stmt;
    }

    public function view(View $view)
    {
        return "CREATE VIEW " . $this->value($view->getView()) . " AS " . $this->select($view->getSelect());
    }

    public function insert(Insert $insert)
    {
        $sql = $insert->getOperation() . " INTO " . $this->tableName($insert->getTable());
        if ($insert->hasFields()) {
            $sql .= "(" . $this->exprList($insert->getFields()) . ")";
        }

        if ($insert->getValues() instanceof Select) {
            $sql .= " " . $this->select($insert->getValues());
        } else {
            $sql .= " VALUES" . $this->exprList($insert->getValues(), '(', ')');
        }

        return $sql;
    }

    public function quote($str)
    {
        return var_export($str, true);
    }

}
