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

/**
 * Class Insert
 * @package SQL
 */
class Insert extends Statement
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var ExprList
     */
    protected $duplicate;

    /**
     * @var array|Select
     */
    protected $values = array();

    /**
     * @var ExprList
     */
    protected $fields;

    public function __construct($type = 'INSERT')
    {
        $this->type = strtoupper($type);
    }

    /**
     * Sets the Table name
     *
     * @param $table
     * @return $this
     */
    public function into($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Returns the table name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Sets the VALUE for this insert|replace statement.
     *
     * Values may be an array of values or a SELECT statement.
     *
     * @param array|Select $values
     * @return $this
     */
    public function values($values)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * Adds a list of fields to update (related to the values)
     *
     * @param ExprList $fields
     * @return $this
     */
    public function fields(ExprList $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Returns the ExprList that the database should perform if a duplicate
     * entry is attempted to be inserted.
     *
     * @return ExprList
     */
    public function getOnDuplicate()
    {
        return $this->duplicate;
    }

    /**
     * @param ExprList $expr
     * @return $this
     */
    public function onDuplicate(ExprList $expr)
    {
        $this->duplicate = $expr;

        return $this;
    }

    /**
     * Returns the list of fields
     *
     * @return ExprList
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Returns whether the current insert has fields defined.
     *
     * @return bool
     */
    public function hasFields()
    {
        return !empty($this->fields);
    }

    /**
     * Returns the current values for this insert|replace statement
     *
     * @return array|Select
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Returns the type of this statement
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}
