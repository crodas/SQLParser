<?php

use SQL\Writer;
use PHPUnit\Framework\TestCase;

class TableDiffTest extends TestCase
{
    public static function featuresDiff()
    {
        $args = [];
        $diff   = new SQL\TableDiff;
        $parser = new SQLParser;
        Writer::setInstance('mysql');
        foreach (glob(__DIR__ . '/features/alter-table/*.sql') as $file) {
            $content = file_get_contents($file);
            if ($parts = preg_split("/--\s*(END|NEW|EXPECTED)/", $content)) {
                $old  = $parts[0];
                $new  = $parts[1];
                $sqls = $parser->parse($parts[2]);
                $args[] = [$diff, $old, $new, array_filter($sqls)];
            }
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
     * @expectedException InvalidArgumentException
     */
    public function testTableDiffException()
    {
        $diff = new SQL\TableDiff;
        $diff->diff("SELECT 1", "SELECT 2");
    }
}
