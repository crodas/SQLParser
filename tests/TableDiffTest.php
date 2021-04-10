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

/**
 * @internal
 * @coversNothing
 */
class TableDiffTest extends TestCase
{
    public static function featuresDiff()
    {
        $args   = [];
        $diff   = new SQL\TableDiff();
        $parser = new SQLParser();
        Writer::setInstance('mysql');
        foreach (glob(__DIR__ . '/features/alter-table/*.sql') as $file) {
            $content = file_get_contents($file);
            if ($parts = preg_split('/--\\s*(END|NEW|EXPECTED)/', $content)) {
                $old    = $parts[0];
                $new    = $parts[1];
                $sqls   = $parser->parse($parts[2]);
                $args[] = [$diff, $old, $new, array_filter($sqls)];
            }
        }

        return $args;
    }

    /**
     *  @dataProvider featuresDiff
     *
     * @param mixed $tableDiff
     * @param mixed $prev
     * @param mixed $current
     */
    public function testTableDiff($tableDiff, $prev, $current, array $expected)
    {
        $changes = $tableDiff->diff((string) $prev, (string) $current);
        $this->assertEquals(count($changes), count($expected));
        foreach ($changes as $id => $change) {
            $this->assertEquals($change, $expected[$id]);
        }
    }

    public function testTableDiffException()
    {
        $this->expectException(InvalidArgumentException::class);
        $diff = new SQL\TableDiff();
        $diff->diff('SELECT 1', 'SELECT 2');
    }
}
