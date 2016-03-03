<?php

use SQLParser\Stmt\Expr;
use SQL\Writer;

class AllTest extends PHPUnit_Framework_TestCase
{
    public static function provider()
    {
        $data = include __DIR__ . '/tests.php';
        $args = [];
        $parser = new SQLParser;
        foreach ($data as $sql => $next) {
            $args[] = [$parser, $sql, $next];
        }

        Writer::setInstance('mysql');

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

    public static function testFeaturesDiff()
    {
        $args = [];
        $diff   = new SQL\TableDiff;
        $parser = new SQLParser;
        foreach (glob(__DIR__ . '/features/alter-table/*.sql') as $file) {
            $sqls = $parser->parse(file_get_contents($file));
            $args[] = [$diff, array_shift($sqls), array_shift($sqls), array_filter($sqls)];
        }

        return $args;
    }

    /**
     *  @dataProvider testFeaturesDiff
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
            $parser->parse($sql);
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
     *  @dataProvider featuresProvider
     */
    public function testFeaturesGeneration($parser, $sql)
    {
        try {
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
            echo $sql . "\n";
            echo SQL\Writer::create($object) . "\n";
            if (!empty($newsql)) {
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

}
