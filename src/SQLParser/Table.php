<?php

namespace SQLParser;

use SQLParser\Stmt\Alpha;

class Table extends Stmt
{
    protected $name;
    protected $columns;
    protected $options;
    protected $keys = array();

    protected function listToArray(Stmt\ExprList $list)
    {
        $array = [];
        foreach ($list->getTerms() as $member) {
            $array[] = $member->getMember(0);
        }

        return $array;
    }

    public function getIndexes()
    {
        return $this->keys;
    }

    public function __construct(Alpha $alpha, Array $columns, Array $options = array())
    {
        $this->name = $alpha;
        $this->options = $options;
        $this->columns = array_filter($columns, 'is_object');

        $key = [];
        foreach (array_filter($columns, 'is_array') as $column) {
            switch ($column[0]) {
            case 'primary':
                $primary = $this->listToArray($column[1]);
                foreach ($this->listToArray($column[1]) as $field) {
                    foreach ($this->columns as $column) {
                        if ($column->getName()->GetMember(0) == $field) {
                            $column->primaryKey();
                            break;
                        }
                    }
                }
                break;
            case 'unique':
                $this->keys[$column[1]->getMember(0)] = array(
                    'unique' => true,
                    'cols' => $this->listToArray($column[2])
                );
                break;
            case 'key':
                $this->keys[$column[1]->getMember(0)] = array(
                    'unique' => false,
                    'cols' => $this->listToArray($column[2])
                );
                break;
            }
        }
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getColumns()
    {
        return $this->columns;
    }
}
