<?php

use SQLParser\Stmt\Expr;
use SQL\Writer;
use PHPUnit\Framework\TestCase;

class AllTest extends TestCase
{
    public static function provider()
    {
        $data = include __DIR__ . '/tests.php';
        $args = [];
        $parser = new SQLParser;
        foreach ($data as $sql => $next) {
            $args[] = [$parser, $sql, $next];
        }

        return $args;
    }

    public function featuresException()
    {
        $args = [];
        $parser = new SQLParser;
        foreach(explode(";", file_get_contents(__DIR__ . '/features/exception.sql')) as $sql) {
            if (!trim($sql)) continue;
            $args[] = [$sql, $parser];
        }

        return $args;
    }

    public static function featuresProviderEngines()
    {
        $args = array();
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
        $args = [];
        $parser = new SQLParser;
        foreach(glob(__DIR__ . "/features/*.sql") as $file) {
            if (basename($file) == 'exception.sql') {
                continue;
            }
            $stmts = preg_split("/;\s*(\n|$)/", file_get_contents($file));
            $type  = substr(basename($file), 0, -4);

            foreach ($stmts as $stmt) {
                $stmt = trim($stmt);
                if (!$stmt) continue;
                $args[] = [$parser, $stmt, $type];
            }
        }

        return $args;
    }

    public static function featuresDiff()
    {
        $args = [];
        $diff   = new SQL\TableDiff;
        $parser = new SQLParser;
        Writer::setInstance('mysql');
        foreach (glob(__DIR__ . '/features/alter-table/*.sql') as $file) {
            $sqls = $parser->parse(file_get_contents($file));
            $args[] = [$diff, array_shift($sqls), array_shift($sqls), array_filter($sqls)];
        }

        return $args;
    }

    /**
     *  @dataProvider featuresDiff
     */
    public function testTableDiff($tableDiff, $prev, $current, Array $expected)
    {
        $changes = $tableDiff->diff((String)$prev, (string)$current);
        $this->assertEquals(count($changes), count($expected));
        foreach ($changes as $id => $change) {
            $this->assertEquals($change, $expected[$id]);
        }
    }


    /**
     *  @dataProvider Provider
     */
    public function testMain($parser, $sql, $callback)
    {
        try {
            $parsed = $parser->parse($sql);
        } catch (\Exception $e) {
            throw $e;
        }

        $strs = [];
        foreach ($parsed as $sql) {
            $strs[] = SQL\Writer::Create($sql);
        }
        $newSql = implode(";", $strs);

        if ($callback($parsed, $this) !== false) {
            // test if the generated SQL is good enough
            $callback($parser->parse($newSql), $this);
        }
    }

    /**
     *  @dataProvider featuresProvider
     */
    public function testFeatures($parser, $sql)
    {
        try {
            $this->assertTrue( is_array( $parser->parse($sql) ) );
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
            } else if (!is_scalar($e)) {
                $exprArray[$i] = serialize($e);
            }
        }

        return $exprArray;
    }

    /**
     *  @dataProvider featuresProviderEngines
     */
    public function testFeaturesGeneration($parser, $sql, $file, $engine)
    {
        try {
            Writer::setInstance($engine);
            $object = $parser->parse($sql)[0];
            $newsql = $parser->parse(SQL\Writer::create($object))[0];


            foreach ([
                    'getOptions', 'hasHaving', 'hasGroupBy','hasWhere', 'hasOrderBy', 'hasLimit',
                    'hasJoins', 'getView', 'getSelect', 'getName', 'getColumns', 'getIndexes',
                    'getName', 'hasName', 'getOnDuplicate', 'getPrimaryKey', 'getModifier',
                ] as $q) {
                if (!is_callable([$object, $q])) {
                    continue;
                }
                $this->assertEquals(
                    $object->$q(),
                    $newsql->$q(),
                    "checking $q"
                );

            }
            foreach (['where', 'set', 'having'] as $type) {
                $check = 'has' . $type;
                $get   = 'get' . $type;
                if (is_callable([$object, $check]) && $object->$check()) {
                    $expr1 = $object->$get();
                    $expr2 = $newsql->$get();

                    $this->assertEquals(
                        $this->exprToArray($expr1),
                        $this->exprToArray($expr2),
                        "checking where are the same"
                    );
                }
            }

        } catch (\Exception $e) {
            echo "\nEngine: $engine\n";
            echo $sql . "\n";
            echo SQL\Writer::create($object) . "\n";
            if (!empty($newsql)) {
                echo "NEW SQL: ";
                echo $newsql . "\n";
            }
            throw $e;
        }
    }

    /**
     *  @dataProvider featuresException
     *  @expectedException RuntimeException
     */
    public function testFeaturesParsingErrors($sql, $parser)
    {
        $data = $parser->parse($sql);
    }

    public function testGetSubQuery()
    {
        $parser = new SQLParser;
        $parsed = $parser->parse("SELECT * FROM (select * from xxx) as y");
        $subs = $parsed[0]->getSubQueries();
        $this->assertTrue(is_array($subs));
        $this->assertFalse(empty($subs));

        $parsed = $parser->parse("SELECT * FROM foobar");
        $this->assertEquals(array(), $parsed[0]->getSubQueries());
    }

    public function testVariableExtrapolation()
    {
        $parser = new SQLParser;
        $q = $parser->parse("SELECT * FROM foobar LIMIT :limit");
        $this->assertEquals("SELECT * FROM foobar LIMIT 33", (string)$q[0]->setValues(array('limit' => 33)));
    }

    public function testVariableExtrapolationMultipleCalls()
    {
        $parser = new SQLParser;
        $q = $parser->parse("SELECT * FROM foobar LIMIT :limit,:offset");
        $this->assertEquals("SELECT * FROM foobar LIMIT 33,10", (string)$q[0]->setValues(array('limit' => 33))->setValues(array('offset'=> 10)));
    }

    public function testGetAllTables() {
        $parser = new SQLParser;
        $q = $parser->parse("SELECT * FROM (SELECT * FROM bar) as lol WHERE y in (SELECT x FROM y)");
        $this->assertEquals(['bar', 'y'], $q[0]->getAllTables());
    }
    public function testGetAllTablesJoin() {
        $parser = new SQLParser;
        $q = $parser->parse("SELECT * FROM users INNER JOIN lol ON x = y");
        $this->assertEquals(['users', 'lol'], $q[0]->getAllTables());
    }

    public static function expectedRewrites()
    {
        return [
            [
                'mysql',
                'SELECT * FROM db.stable WHERE foo IS NULL',
                'SELECT * FROM `db`.`stable` WHERE `foo` IS NULL',
            ],
            [
                'mysql',
                'SELECT * FROM db.stable WHERE foo IS NOT NULL',
                'SELECT * FROM `db`.`stable` WHERE `foo` IS NOT NULL',
            ],
            [
                'sqlite',
                'SELECT * FROM db.stable WHERE foo IS NULL',
                'SELECT * FROM db.stable WHERE foo IS NULL',
            ],
            [
                'sqlite',
                'SELECT * FROM db.`table` WHERE foo IS NOT NULL',
                "SELECT * FROM db.'table' WHERE foo IS NOT NULL",
            ],
            [
                'mysql',
                'SELECT * FROM db.stable WHERE (foo = :foo AND bar = :bar) OR xxx = :xxx',
                'SELECT * FROM `db`.`stable` WHERE (`foo` = :foo AND `bar` = :bar) OR `xxx` = :xxx',
            ],
            [
                'mysql',
                'SELECT * FROM db.stable',
                'SELECT * FROM `db`.`stable`',
            ],
            [
                'mysql',
                'SELECT * FROM db.stable as foo',
                'SELECT * FROM `db`.`stable` AS `foo`',
            ],
            [
                'mysql',
                'SELECT * FROM stable as stable',
                'SELECT * FROM `stable` AS `stable`',
            ],
            [
                'mysql',
                'SELECT * FROM stable stable WHERE x = 1',
                'SELECT * FROM `stable` AS `stable` WHERE `x` = 1',
            ],
        ];
    }

    /**
     * @dataProvider expectedRewrites
     * @param $driver
     * @param $input
     * @param $output
     */
    public function testRewriteQuery($driver, $input, $output)
    {
        Writer::setInstance($driver);
        $parser = new SQLParser;
        $queries = $parser->parse($input);

        $this->assertEquals($output, (string)$queries[0]);
    }
}
