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

use PHPUnit\Framework\TestCase;
use SQL\Writer;
use SQLParser\Stmt\Expr;

/**
 * @internal
 * @coversNothing
 */
class AllTest extends TestCase
{
    public static function provider()
    {
        $data   = include __DIR__ . '/tests.php';
        $args   = [];
        $parser = new SQLParser();
        foreach ($data as $sql => $next) {
            $args[] = [$parser, $sql, $next];
        }

        return $args;
    }

    public function featuresException()
    {
        $args   = [];
        $parser = new SQLParser();
        foreach (explode(';', file_get_contents(__DIR__ . '/features/exception.sql')) as $sql) {
            if (!trim($sql)) {
                continue;
            }
            $args[] = [$sql, $parser];
        }

        return $args;
    }

    public static function featuresProviderEngines()
    {
        $args = [];
        foreach (self::featuresProvider() as $arg) {
            $arg[3] = 'mysql';
            $args[] = $arg;
            if (!preg_match('/LAST_INSERT_ID|SQL_CALC_FOUND_ROWS|SQL_CACHE|SQL_BUFFER_RESULT|engine|utf8_unicode_ci|collate/i', $arg[1])) {
                $arg[3] = 'sqlite';
                $args[] = $arg;
            }
            if (!preg_match('/LAST_INSERT_ID|SQL_CALC_FOUND_ROWS|SQL_CACHE|SQL_BUFFER_RESULT|auto_?increment|engine|utf8_unicode_ci|collate/i', $arg[1])) {
                $arg[3] = '';
                $args[] = $arg;
            }
        }

        return $args;
    }

    public static function featuresProvider()
    {
        $args   = [];
        $parser = new SQLParser();
        foreach (glob(__DIR__ . '/features/*.sql') as $file) {
            if ('exception.sql' == basename($file)) {
                continue;
            }
            $stmts = preg_split("/;\\s*(\n|$)/", file_get_contents($file));
            $type  = substr(basename($file), 0, -4);

            foreach ($stmts as $stmt) {
                $stmt = trim($stmt);
                if (!$stmt) {
                    continue;
                }
                $args[] = [$parser, $stmt, $type];
            }
        }

        return $args;
    }

    /**
     *  @dataProvider Provider
     *
     * @param mixed $parser
     * @param mixed $sql
     * @param mixed $callback
     */
    public function testMain($parser, $sql, $callback)
    {
        try {
            $parsed = $parser->parse($sql);
        } catch (\Exception $e) {
            throw $e;
        }

        Writer::setInstance('mysql');

        $strs = [];
        foreach ($parsed as $sql) {
            $strs[] = Writer::Create($sql);
        }
        $newSql = implode(';', $strs);

        if (false !== $callback($parsed, $this)) {
            // test if the generated SQL is good enough
            $callback($parser->parse($newSql), $this);
        }
    }

    /**
     *  @dataProvider featuresProvider
     *
     * @param mixed $parser
     * @param mixed $sql
     */
    public function testFeatures($parser, $sql)
    {
        try {
            $this->assertIsArray($parser->parse($sql));
        } catch (\Exception $e) {
            echo $sql . "\n";

            throw $e;
        }
    }

    public function exprToArray(Expr $expr)
    {
        $exprArray = $expr->getMembers();
        foreach ($exprArray as $i => $e) {
            if ($e instanceof Expr) {
                $exprArray[$i] = $this->exprToArray($e);
            } elseif (!is_scalar($e)) {
                $exprArray[$i] = serialize($e);
            }
        }

        return $exprArray;
    }

    /**
     *  @dataProvider featuresProviderEngines
     *
     * @param mixed $parser
     * @param mixed $sql
     * @param mixed $file
     * @param mixed $engine
     */
    public function testFeaturesGeneration($parser, $sql, $file, $engine)
    {
        try {
            Writer::setInstance($engine);
            $object = $parser->parse($sql)[0];
            $newsql = $parser->parse(SQL\Writer::create($object))[0];

            foreach ([
                'getOptions', 'hasHaving', 'hasGroupBy', 'hasWhere', 'hasOrderBy', 'hasLimit',
                'hasJoins', 'getView', 'getSelect', 'getName', 'getColumns', 'getIndexes',
                'getName', 'hasName', 'getOnDuplicate', 'getPrimaryKey', 'getModifier',
            ] as $q) {
                if (!is_callable([$object, $q])) {
                    continue;
                }
                $this->assertEquals(
                    $object->{$q}(),
                    $newsql->{$q}(),
                    "checking {$q}"
                );
            }
            foreach (['where', 'set', 'having'] as $type) {
                $check = 'has' . $type;
                $get   = 'get' . $type;
                if (is_callable([$object, $check]) && $object->{$check}()) {
                    $expr1 = $object->{$get}();
                    $expr2 = $newsql->{$get}();

                    $this->assertEquals(
                        $this->exprToArray($expr1),
                        $this->exprToArray($expr2),
                        'checking where are the same'
                    );
                }
            }
        } catch (\Exception $e) {
            echo "\nEngine: {$engine}\n";
            echo $sql . "\n";
            if (!empty($object)) {
                echo SQL\Writer::create($object) . "\n";
            }
            if (!empty($newsql)) {
                echo 'NEW SQL: ';
                echo $newsql . "\n";
            }

            throw $e;
        }
    }

    /**
     *  @dataProvider featuresException
     *
     * @param mixed $sql
     * @param mixed $parser
     */
    public function testFeaturesParsingErrors($sql, $parser)
    {
        $this->expectException(RuntimeException::class);

        $parser->parse($sql);
    }

    public function testGetSubQuery()
    {
        $parser = new SQLParser();
        $parsed = $parser->parse('SELECT * FROM (select * from xxx) as y');
        $subs   = $parsed[0]->getSubQueries();
        $this->assertIsArray($subs);
        $this->assertNotEmpty($subs);

        $parsed = $parser->parse('SELECT * FROM foobar');
        $this->assertEquals([], $parsed[0]->getSubQueries());
    }

    public function testVariableExtrapolation()
    {
        $parser = new SQLParser();
        $q      = $parser->parse('SELECT * FROM foobar LIMIT :limit');
        $this->assertEquals('SELECT * FROM foobar LIMIT 33', (string) $q[0]->setValues(['limit' => 33]));
    }

    public function testVariableExtrapolationMultipleCalls()
    {
        $parser = new SQLParser();
        $q      = $parser->parse('SELECT * FROM foobar LIMIT :limit,:offset');
        $this->assertEquals('SELECT * FROM foobar LIMIT 33,10', (string) $q[0]->setValues(['limit' => 33])->setValues(['offset' => 10]));
    }

    public function testGetAllTables()
    {
        $parser = new SQLParser();
        $q      = $parser->parse('SELECT * FROM (SELECT * FROM bar) as lol WHERE y in (SELECT x FROM y)');
        $this->assertEquals(['bar', 'y'], $q[0]->getAllTables());
    }

    public function testGetAllTablesJoin()
    {
        $parser = new SQLParser();
        $q      = $parser->parse('SELECT * FROM users INNER JOIN lol ON x = y');
        $this->assertEquals(['users', 'lol'], $q[0]->getAllTables());
    }
}
