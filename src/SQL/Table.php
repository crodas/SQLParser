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

/**
 * Class Table.
 */
class Table extends Statement
{
    protected $name;
    protected $options;
    protected $columns = [];
    protected $keys    = [];

    public function __construct($alpha, array $columns, array $options = [])
    {
        $this->name    = $alpha;
        $this->options = $options;
        $this->columns = array_filter($columns, 'is_object');

        foreach (array_filter($columns, 'is_array') as $column) {
            switch ($column[0]) {
            case 'primary':
                foreach ($column[1]->getExprs() as $field) {
                    $field = $field->getMember(0)->getMember(0);
                    foreach ($this->columns as $column) {
                        if ($column->getName() === $field) {
                            $column->primaryKey();

                            break;
                        }
                    }
                }

                break;

            case 'unique':
                $this->keys[$column[1]] = [
                    'unique' => true,
                    'cols'   => $column[2]->getExprs(),
                ];

                break;

            case 'key':
                $this->keys[$column[1]] = [
                    'unique' => false,
                    'cols'   => $column[2]->getExprs(),
                ];

                break;
            }
        }
    }

    public function getIndexes()
    {
        return $this->keys;
    }

    public function getPrimaryKey()
    {
        return array_filter($this->columns, function ($column) {
            return $column->isPrimaryKey();
        });
    }

    public function addIndex(AlterTable\AddIndex $index)
    {
        $this->keys[$index->getIndexName()] = [
            'unique' => 'UNIQUE' === $index->getIndexType(),
            'cols'   => $index->getColumns()->getExprs(),
        ];
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
