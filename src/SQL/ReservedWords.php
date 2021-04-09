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
        self::T_ADD                 => true,
        self::T_AFTER               => true,
        self::T_ALL                 => true,
        self::T_ALTER               => true,
        self::T_AND                 => true,
        self::T_AS                  => true,
        self::T_ASC                 => true,
        self::T_AUTO_INCREMENT      => true,
        self::T_BEGIN               => true,
        self::T_BETWEEN             => true,
        self::T_BINARY              => true,
        self::T_BITWISE             => true,
        self::T_BY                  => true,
        self::T_CASE                => true,
        self::T_CHANGE              => true,
        self::T_COLLATE             => true,
        self::T_COLUMN              => true,
        self::T_COMMIT              => true,
        self::T_CREATE              => true,
        self::T_DEFAULT             => true,
        self::T_DELETE              => true,
        self::T_DESC                => true,
        self::T_DISTINCT            => true,
        self::T_DISTINCTROW         => true,
        self::T_DROP                => true,
        self::T_DUPLICATE           => true,
        self::T_ELSE                => true,
        self::T_END                 => true,
        self::T_FILTER_PIPE         => true,
        self::T_FIRST               => true,
        self::T_FROM                => true,
        self::T_GLOB                => true,
        self::T_GROUP               => true,
        self::T_HAVING              => true,
        self::T_HIGH_PRIORITY       => true,
        self::T_IN                  => true,
        self::T_INDEX               => true,
        self::T_INNER               => true,
        self::T_INSERT              => true,
        self::T_INTERVAL            => true,
        self::T_INTO                => true,
        self::T_IS                  => true,
        self::T_JOIN                => true,
        self::T_KEY                 => true,
        self::T_LEFT                => true,
        self::T_LIKE                => true,
        self::T_LIMIT               => true,
        self::T_LT                  => true,
        self::T_MODIFY              => true,
        self::T_NATURAL             => true,
        self::T_NOT                 => true,
        self::T_NULL                => true,
        self::T_OFFSET              => true,
        self::T_ON                  => true,
        self::T_OR                  => true,
        self::T_ORDER               => true,
        self::T_OUTER               => true,
        self::T_PIPE                => true,
        self::T_PRIMARY             => true,
        self::T_RELEASE             => true,
        self::T_RENAME              => true,
        self::T_ROLLBACK            => true,
        self::T_RT_IGHT             => true,
        self::T_SAVEPOINT           => true,
        self::T_SELECT              => true,
        self::T_SET                 => true,
        self::T_SQL_BIG_RESULT      => true,
        self::T_SQL_BUFFER_RESULT   => true,
        self::T_SQL_CACHE           => true,
        self::T_SQL_CALC_FOUND_ROWS => true,
        self::T_SQL_NO_CACHE        => true,
        self::T_SQL_SMALL_RESULT    => true,
        self::T_STRAIGHT_JOIN       => true,
        self::T_TABLE               => true,
        self::T_THEN                => true,
        self::T_TO                  => true,
        self::T_TRANSACTION         => true,
        self::T_UNIQUE              => true,
        self::T_UNSIGNED            => true,
        self::T_UPDATE              => true,
        self::T_USING               => true,
        self::T_VALUES              => true,
        self::T_VIEW                => true,
        self::T_WHEN                => true,
        self::T_WHERE               => true,
        self::T_WORK                => true,
    ];
}
