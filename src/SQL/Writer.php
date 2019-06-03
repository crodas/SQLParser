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

use SQLParser\Stmt\ExprList;
use SQLParser\Stmt\Expr;
use SQL\AlterTable;
use SQLParser\Stmt;
use RuntimeException;
use PDO;

class Writer
{
    protected static $instance;
    protected $varValues = array();

    /**
     *  Set Write instance
     *
     *  Change the Writer instance to generate SQL-compatible
     *  with another engine.
     */
    final public static function setInstance($instance)
    {
        if ($instance instanceof PDO) {
            $instance = $instance->getAttribute(PDO::ATTR_DRIVER_NAME);
        }
        if (is_string($instance)) {
            if (class_exists('SQL\Writer\\' . $instance)) {
                $class = 'SQL\Writer\\' . $instance ;
                $instance = new $class;
            } else {
                $instance = new self;
            }
        }

        if (!($instance instanceof self)) {
            throw new RuntimeException('$instance must an instanceof SQL\Writer');
        }

        self::$instance = $instance;
    }

    final public static function getInstance()
    {
        self::$instance = self::$instance ? self::$instance : new self;
        return self::$instance;
    }

    final public static function create($object, Array $values = array())
    {
        if (empty(self::$instance)) {
            self::getInstance();
        }

        self::$instance->varValues = $values;

        if ($object instanceof Select) {
            return self::$instance->select($object);
        } else if ($object instanceof Insert) {
            return self::$instance->insert($object);
        } else if ($object instanceof Delete) {
            return self::$instance->delete($object);
        } else if ($object instanceof Drop) {
            return self::$instance->drop($object);
        } else if ($object instanceof Table) {
            return self::$instance->createTable($object);
        } else if ($object instanceof Update) {
            return self::$instance->update($object);
        } else if ($object instanceof View) {
            return self::$instance->view($object);
        } else if ($object instanceof BeginTransaction) {
            return self::$instance->begin($object);
        } else if ($object instanceof RollbackTransaction) {
            return self::$instance->rollback($object);
        } else if ($object instanceof CommitTransaction) {
            return self::$instance->commit($object);
        } else if ($object instanceof AlterTable\AddColumn) {
            return self::$instance->addColumn($object);
        } else if ($object instanceof AlterTable\ChangeColumn) {
            return self::$instance->changeColumn($object);
        } else if ($object instanceof AlterTable\SetDefault) {
            return self::$instance->setDefault($object);
        } else if ($object instanceof AlterTable\DropIndex) {
            return self::$instance->dropIndex($object);
        } else if ($object instanceof AlterTable\DropPrimaryKey) {
            return self::$instance->dropPrimaryKey($object);
        } else if ($object instanceof AlterTable\DropColumn) {
            return self::$instance->dropColumn($object);
        } else if ($object instanceof AlterTable\RenameTable) {
            return self::$instance->renameTable($object);
        } else if ($object instanceof AlterTable\RenameIndex) {
            return self::$instance->renameIndex($object);
        } else if ($object instanceof AlterTable\AddIndex) {
            return self::$instance->addIndex($object);
        }

        throw new RuntimeException("Don't know how to create " . get_class($object));
    }

    public function addIndex(AlterTable\AddIndex $alterTable)
    {
        return 'CREATE ' . $alterTable->getIndexType() . ' INDEX ' 
            . $this->escape($alterTable->getIndexName())
            . ' ON ' . $this->escape($alterTable->getTableName())
            . ' ( ' . $this->exprList($alterTable->getColumns()) . ')';
    }

    public function renameIndex(AlterTable\RenameIndex $alterTable)
    {
        return "ALTER TABLE " . $this->escape($alterTable->getTableName()) . " RENAME INDEX " 
            . $this->escape($alterTable->getOldName())
            . ' TO '
            . $this->escape($alterTable->getNewName());
    }


    public function renameTable(AlterTable\RenameTable $alterTable)
    {
        return "ALTER TABLE " . $this->escape($alterTable->getTableName()) . " RENAME TO " 
            . $this->escape($alterTable->getNewName());
    }


    public function dropColumn(AlterTable\DropColumn $alterTable)
    {
        return "ALTER TABLE " . $this->escape($alterTable->getTableName()) . " DROP  COLUMN" 
            . $this->escape($alterTable->getColumnName());
    }


    public function dropPrimaryKey(AlterTable\DropPrimaryKey $alterTable)
    {
        return "ALTER TABLE " . $this->escape($alterTable->getTableName()) . " DROP PRIMARY KEY";
    }

    public function dropIndex(AlterTable\DropIndex $alterTable)
    {
        return "ALTER TABLE " . $this->escape($alterTable->getTableName()) . " DROP INDEX " 
            . $this->escape($alterTable->getIndexName());
    }

    public function setDefault(AlterTable\SetDefault $alterTable)
    {
        $sql = "ALTER TABLE " . $this->escape($alterTable->getTableName()) . " CHANGE COLUMN " 
            . $this->escape($alterTable->getColumn());
        if ($alterTable->getValue() === NULL) {
            $sql .= " DROP DEFAULT";
        } else {
            $sql .= " SET DEFAULT " . $this->expr($alterTable->getValue());
        }

        return $sql;
    }

    public function changeColumn(AlterTable\ChangeColumn $alterTable)
    {
        $sql = "ALTER TABLE " . $this->escape($alterTable->getTableName()) . " CHANGE COLUMN " 
            . $this->escape($alterTable->getOldName())
            .  " "
            . $this->columnDefinition($alterTable->getColumn());
        if ($alterTable->isFirst()) {
            $sql .= " FIRST";
        } else if ($alterTable->getPosition()) {
            $sql .= " AFTER " . $this->escape($alterTable->getPosition());
        }

        return $sql;
    }

    public function addColumn(AlterTable\AddColumn $alterTable)
    {
        $sql =  "ALTER TABLE " . $this->escape($alterTable->getTableName()) . " ADD COLUMN " 
            . $this->columnDefinition($alterTable->getColumn());
        if ($alterTable->isFirst()) {
            $sql .= " FIRST";
        } else if ($alterTable->getPosition()) {
            $sql .= " AFTER " . $this->escape($alterTable->getPosition());
        }

        return $sql;
    }

    public function variable(Stmt\VariablePlaceholder $stmt)
    {
        $name = $stmt->getName();

        if ($name === '?') {
            return $name;
        }

        if (array_key_exists($name, $this->varValues)) {
            return $this->value($this->varValues[$name]);
        }

        return ":{$name}";
    }

    protected function value($value)
    {
        $map = [
            'SQL\Select' => 'select',
            'SQLParser\Stmt\Join' => 'join',
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
            throw new \InvalidArgumentException;
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        return '"' . str_replace('"', '\\"', substr(var_export($value, true), 1, -1)) . '"';
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
            return implode(".", array_map([$this, 'escape'], $expr->getMembers()));

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
        case 'IN':
            return $this->escape($expr->getMember(0)) . " IN {$member[1]}";
        case 'NIN':
            return $this->escape($expr->getMember(0)) . " NOT IN {$member[1]}";
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
        case 'EXPR':
            return "({$member[0]})";
        case 'INDEX':
            $rawMember = $expr->getMembers();
            $expr = $member[0];
            if (!empty($rawMember[1])) {
                $expr .= "(" . $rawMember[1] . ")";
            }
            if (!empty($rawMember[2])) {
                $expr .= " " . $rawMember[2];
            }
            return $expr;
        case 'VALUE': 
            return $member[0];
        case 'NOT':
            return "NOT {$member[0]}";
        case 'TIMEINTERVAL':
            return "INTERVAL {$member[0]} " . $expr->getMember(1);
        }

        return "{$member[0]} {$type} {$member[1]}";
    }

    protected function exprList($list)
    {
        if ($list instanceof ExprList) {
            $list = $list->getExprs();
        }
        $columns = array();
        foreach ($list as $column) {
            if ($column instanceof Expr || $column instanceof Stmt\VariablePlaceholder) {
                $columns[] = $this->value($column);
            } else if (is_string($column)) {
                $columns[] = $this->escape($column);
            } else {
                $value = $this->expr($column[0]);
                if (count($column) == 2) {
                    $columns[] = $value . ' AS '. $this->escape($column[1]);
                } else {
                    $columns[] = $value;
                }
            }
        }

        return implode(", ", $columns); 
    }

    protected function tableList(Array $tables)
    {
        $list = array();
        foreach ($tables as $key => $table) {
            if (is_array($table) && $table[1]) {
                $table = $this->escape($table[0]) . '.' . $this->escape($table[1]);
            } else if (is_array($table)) {
                $table = $this->escape($table[0]);
            } else if ($table instanceof Select) {
                $table = '(' . $this->select($table) . ')';
            } else {
                $table = $this->escape($table);
            }

            if (is_numeric($key)) {
                $list[] = $table;
            } else {
                $list[] = $table . ' AS ' . $this->escape($key);
            }
        }
        return implode(", ", $list);
    }

    public function update(Update $update)
    {
        $stmt  = 'UPDATE ' . $this->tableList($update->getTable());
        $stmt .= $this->doJoins($update);
        $stmt .= " SET " . $this->exprList($update->getSet());
        $stmt .= $this->doWhere($update);
        $stmt .= $this->doOrderBy($update);
        $stmt .= $this->doLimit($update);
        return $stmt;
    }

    public function commit(CommitTransaction $transaction)
    {
        if ($transaction->getName()) {
            return "RELEASE SAVEPOINT " . $this->escape($transaction->getName());
        }

        return "COMMIT TRANSACTION";
    }

    public function rollback(RollbackTransaction $transaction)
    {
        if ($transaction->getName()) {
            return "ROLLBACK TO " . $this->escape($transaction->getName());
        }

        return "ROLLBACK TRANSACTION";
    }

    public function begin(BeginTransaction $transaction)
    {
        if ($transaction->getName()) {
            return "SAVEPOINT " . $this->escape($transaction->getName());
        }

        return "BEGIN TRANSACTION";
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

    public function dataType($type, $size)
    {
        return $size ? "$type($size)" : $type;
    }


    public function columnDefinition(Stmt\Column $column)
    {
        $sql = $this->escape($column->GetName()) 
            . " "
            . $this->dataType($column->getType(), $column->getTypeSize())
            . $column->getModifier();

        if ($column->isNotNull()) {
            $sql .= " NOT NULL";
        }

        if ($column->defaultValue()) {
            $sql .= " DEFAULT " . $this->value($column->defaultValue());
        }

        if ($column->isPrimaryKey()) {
            $sql .= " PRIMARY KEY";
        }

        return $sql;
    }

    public function createTable(Table $table)
    {
        $columns = [];
        foreach ($table->getColumns() as $column) {
            $columns[] = $this->columnDefinition($column);
        }
        return "CREATE TABLE " . $this->escape($table->getName()) . "(" 
            . implode(",", $columns)
            . ")";
    }


    public function view(View $view)
    {
        return "CREATE VIEW " . $this->escape($view->getView()) . " AS " . $this->select($view->getSelect());
    }

    public function drop(Drop $drop)
    {
        return "DROP " . $drop->getType() . " " . $this->tableList($drop->getTable());
    }


    public function delete(Delete $delete)
    {
        $stmt  = "DELETE FROM " . $this->escape($delete->getTable());
        $stmt .= $this->doWhere($delete);
        $stmt .= $this->doOrderBy($delete);
        $stmt .= $this->doLimit($delete);

        return $stmt;
    }

    public function Insert(Insert $insert)
    {
        $sql = $insert->getOperation() . " INTO " . $this->escape($insert->getTable());
        if ($insert->hasFields()) {
            $sql .= "(" . $this->exprList($insert->getFields()) . ")";
        }
        if ($insert->getValues() instanceof Select) {
            $sql .= " " . $this->select($insert->getValues());
        } else {
            $sql .= " VALUES";
            foreach ($insert->getValues() as $expr) {
                $sql .= "(" . $this->exprList($expr) . "),";
            }
            $sql = substr($sql, 0, -1);
        }
        return $sql;
    }

    protected function doWhere(Statement $stmt)
    {
        if ($stmt->hasWhere()) {
            return ' WHERE ' . $this->expr($stmt->getWhere());
        }
    }

    protected function doOrderBy(Statement $stmt)
    {
        if ($stmt->hasOrderBy()) {
            return " ORDER BY " . $this->exprList($stmt->getOrderBy());
        }
    }

    protected function doLimit(Statement $stmt)
    {
        if ($stmt->hasLimit() || $stmt->hasOffset()) {
            if ($stmt->hasOffset()) {
                return " LIMIT " . $this->value($stmt->getOffset()) . "," . $this->value($stmt->getLimit());
            } else {
                return " LIMIT " . $this->value($stmt->getLimit());
            }
        }
    }

    public function join(Stmt\Join $join)
    {
        $str = $join->getType() . " " . $this->tableList(array($join->getTable()));
        if ($join->hasAlias()) {
            $str .= " AS " . $this->escape($join->getAlias());
        }
        if ($join->hasCondition()) {
            $str .= $join->hasOn() ? " ON " : " USING ";
            $str .= $this->value($join->getCondition());
        }

        return $str;
    }

    protected function doJoins(Statement $stmt)
    {
        if (!$stmt->hasJoins()) {
            return '';
        }

        $joins = array();
        foreach ($stmt->getJoins() as $join) {
            $joins[] = $this->join($join);
        }

        return " " . implode(" ", $joins);
    }

    protected function doGroupBy(Statement $stmt)
    {
        if ($stmt->hasGroupBy()) {
            $str = " GROUP BY " . $this->value($stmt->getGroupBy());
            if($stmt->hasHaving()) {
                $str .= " HAVING ". $this->value($stmt->getHaving());
            }

            return $str;
        }
    }

    public function Select(Select $select)
    {
        $stmt  = 'SELECT ';
        $stmt .= $this->selectOptions($select);
        $stmt .= $this->exprList($select->getColumns());

        if ($select->hasTable()) {
            $stmt .= " FROM " . $this->tableList($select->getTables());
        }
        $stmt .= $this->doJoins($select);
        $stmt .= $this->doWhere($select);
        $stmt .= $this->doGroupBy($select);
        $stmt .= $this->doOrderBy($select);
        $stmt .= $this->doLimit($select);
        return $stmt;
    }

    public function escape($value)
    {
        if (!is_string($value)) {
            return $this->value($value);
        }

        if (preg_match('/^[a-z0-9_]+$/i', $value) && empty(ReservedWords::$words[$value])) {
            return $value;
        }

        return var_export($value, true);
    }
}
