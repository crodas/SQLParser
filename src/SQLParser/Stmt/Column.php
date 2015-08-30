<?php

namespace SQLParser\Stmt;

class Column
{
    protected $name;
    protected $type;
    protected $size;
    protected $pk = false;
    protected $notNull = false;
    protected $default;
    protected $collate;
    protected $autoincrement = false;

    public function __construct(Alpha $name, Alpha $type, $size = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function collate(Alpha $expr = null)
    {
        if ($expr === null) {
            return $this->collate;
        }
        $this->collate = $expr;
        return $this;
    }

    public function defaultValue(Expr $value = null)
    {
        if ($value === null) {
            return $this->default;
        }
        $this->default = $value;
        return $this;
    }

    public function getTypeSize()
    {
        return $this->size;
    }

    public function isPrimaryKey()
    {
        return $this->pk;
    }


    public function primaryKey()
    {
        $this->pk = true;
        return true;
    }

    public function isNotNull()
    {
        return (bool)$this->notNull;
    }

    public function notNull()
    {
        $this->notNull = true;
        return $this;
    }

    public function isAutoincrement()
    {
        return $this->autoincrement;
    }

    public function autoincrement()
    {
        $this->autoincrement = true;
        return $this;
    }
}
