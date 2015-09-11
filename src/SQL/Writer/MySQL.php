<?php

namespace SQL\Writer;

use SQL\Writer;
use SQL\Select;
use SQL\Table;
use SQL\Insert;
use SQLParser\Stmt;

class MySQL extends Writer
{
    public function escape($value)
    {
        if (!is_string($value)) {
            return $this->value($value);
        }
        return "`$value`";
     }

    public function selectOptions(Select $select)
    {
        $options = $select->getOptions();
        if (!empty($options)) {
            return implode(" ", $options) . " ";
        }
    }

    public function createTable(Table $table)
    {
        $columns = [];
        foreach ($table->getColumns() as $column) {
            $columns[] = $this->columnDefinition($column);
        }

        $keys = array();
        foreach ($table->getIndexes() as $name => $definition) {
            $keys[] = ($definition['unique']  ? "UNIQUE KEY " : "KEY ") 
                . $this->escape($name) . "(" . $this->exprList($definition['cols']) . ")";
        }

        $sql = "CREATE TABLE " . $this->escape($table->getName()) . "(" 
            . implode(",", array_merge($columns, $keys))
            . ")";
        foreach ($table->getOptions() as $key => $value) {
            $sql .= " $key = $value";
        }
        return $sql;
    }

    public function insert(Insert $insert)
    {
        $stmt = parent::insert($insert);
        if ($insert->getOnDuplicate()) {
            $stmt .= ' ON DUPLICATE KEY UPDATE ' . $this->exprList($insert->getOnDuplicate());
        }
        return $stmt;
    }

    public function columnDefinition(Stmt\Column $column)
    {
        $sql = $this->escape($column->GetName()) 
            . " "
            . $this->dataType($column->getType(), $column->getTypeSize());


        if ($column->isNotNull()) {
            $sql .= " NOT NULL";
        }

        if ($column->isAutoIncrement()) {
            $sql .= " AUTO_INCREMENT";
        }

        if ($column->defaultValue()) {
            $sql .= " DEFAULT " . $this->value($column->defaultValue());
        }

        if ($column->collate()) {
            $sql .= " COLLATE" . $this->value($column->collate());
        } 

        if ($column->isPrimaryKey()) {
            $sql .= " PRIMARY KEY";
        }

        return $sql;
    }

}
