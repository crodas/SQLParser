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

class ReservedWords
{
    const T_ADD = 'add';

    const T_AFTER = 'after';

    const T_ALL = 'all';

    const T_ALTER = 'alter';

    const T_AND = 'and';

    const T_AS = 'as';

    const T_ASC = 'asc';

    const T_AUTO_INCREMENT = 'auto_increment';

    const T_BEGIN = 'begin';

    const T_BETWEEN = 'between';

    const T_BINARY = 'binary';

    const T_BITWISE = 'bitwise';

    const T_BY = 'by';

    const T_CASE = 'case';

    const T_CHANGE = 'change';

    const T_COLLATE = 'collate';

    const T_COLUMN = 'column';

    const T_COMMIT = 'commit';

    const T_CREATE = 'create';

    const T_DEFAULT = 'default';

    const T_DELETE = 'delete';

    const T_DESC = 'desc';

    const T_DISTINCT = 'distinct';

    const T_DISTINCTROW = 'distinctrow';

    const T_DROP = 'drop';

    const T_DUPLICATE = 'duplicate';

    const T_ELSE = 'else';

    const T_END = 'end';

    const T_FILTER_PIPE = 'filter_pipe';

    const T_FIRST = 'first';

    const T_FROM = 'from';

    const T_GLOB = 'glob';

    const T_GROUP = 'group';

    const T_HAVING = 'having';

    const T_HIGH_PRIORITY = 'high_priority';

    const T_IN = 'in';

    const T_INDEX = 'index';

    const T_INNER = 'inner';

    const T_INSERT = 'insert';

    const T_INTERVAL = 'interval';

    const T_INTO = 'into';

    const T_IS = 'is';

    const T_JOIN = 'join';

    const T_KEY = 'key';

    const T_LEFT = 'left';

    const T_LIKE = 'like';

    const T_LIMIT = 'limit';

    const T_LT = 'lt';

    const T_MODIFY = 'modify';

    const T_NATURAL = 'natural';

    const T_NOT = 'not';

    const T_NULL = 'null';

    const T_OFFSET = 'offset';

    const T_ON = 'on';

    const T_OR = 'or';

    const T_ORDER = 'order';

    const T_OUTER = 'outer';

    const T_PIPE = 'pipe';

    const T_PRIMARY = 'primary';

    const T_RELEASE = 'release';

    const T_RENAME = 'rename';

    const T_ROLLBACK = 'rollback';

    const T_RT_IGHT = 'rt_ight';

    const T_SAVEPOINT = 'savepoint';

    const T_SELECT = 'select';

    const T_SET = 'set';

    const T_SQL_BIG_RESULT = 'sql_big_result';

    const T_SQL_BUFFER_RESULT = 'sql_buffer_result';

    const T_SQL_CACHE = 'sql_cache';

    const T_SQL_CALC_FOUND_ROWS = 'sql_calc_found_rows';

    const T_SQL_NO_CACHE = 'sql_no_cache';

    const T_SQL_SMALL_RESULT = 'sql_small_result';

    const T_STRAIGHT_JOIN = 'straight_join';

    const T_TABLE = 'table';

    const T_THEN = 'then';

    const T_TO = 'to';

    const T_TRANSACTION = 'transaction';

    const T_UNIQUE = 'unique';

    const T_UNSIGNED = 'unsigned';

    const T_UPDATE = 'update';

    const T_USING = 'using';

    const T_VALUES = 'values';

    const T_VIEW = 'view';

    const T_WHEN = 'when';

    const T_WHERE = 'where';

    const T_WORK = 'work';

    public static $words = [
        'add'                 => 44,
        'after'               => 53,
        'all'                 => 55,
        'alter'               => 35,
        'and'                 => 2,
        'as'                  => 49,
        'asc'                 => 79,
        'auto_increment'      => 96,
        'begin'               => 26,
        'between'             => 98,
        'binary'              => 99,
        'bitwise'             => 21,
        'by'                  => 77,
        'case'                => 100,
        'change'              => 50,
        'collate'             => 94,
        'column'              => 51,
        'commit'              => 33,
        'create'              => 46,
        'default'             => 42,
        'delete'              => 85,
        'desc'                => 78,
        'distinct'            => 56,
        'distinctrow'         => 57,
        'drop'                => 37,
        'duplicate'           => 90,
        'else'                => 101,
        'end'                 => 34,
        'filter_pipe'         => 22,
        'first'               => 52,
        'from'                => 66,
        'glob'                => 8,
        'group'               => 82,
        'having'              => 83,
        'high_priority'       => 58,
        'in'                  => 14,
        'index'               => 40,
        'inner'               => 69,
        'insert'              => 87,
        'interval'            => 104,
        'into'                => 89,
        'is'                  => 97,
        'join'                => 68,
        'key'                 => 39,
        'left'                => 70,
        'like'                => 7,
        'limit'               => 80,
        'lt'                  => 12,
        'modify'              => 43,
        'natural'             => 72,
        'not'                 => 3,
        'null'                => 95,
        'offset'              => 81,
        'on'                  => 47,
        'or'                  => 1,
        'order'               => 76,
        'outer'               => 73,
        'pipe'                => 20,
        'primary'             => 38,
        'release'             => 28,
        'rename'              => 45,
        'rollback'            => 29,
        'rt_ight'             => 71,
        'savepoint'           => 27,
        'select'              => 54,
        'set'                 => 41,
        'sql_big_result'      => 61,
        'sql_buffer_result'   => 64,
        'sql_cache'           => 62,
        'sql_calc_found_rows' => 63,
        'sql_no_cache'        => 65,
        'sql_small_result'    => 60,
        'straight_join'       => 59,
        'table'               => 36,
        'then'                => 103,
        'to'                  => 30,
        'transaction'         => 31,
        'unique'              => 48,
        'unsigned'            => 93,
        'update'              => 86,
        'using'               => 74,
        'values'              => 84,
        'view'                => 91,
        'when'                => 102,
        'where'               => 75,
        'work'                => 32,
    ];
}
