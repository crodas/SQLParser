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
class WriterTest extends TestCase
{
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
     *
     * @param $driver
     * @param $input
     * @param $output
     */
    public function testRewriteQuery($driver, $input, $output)
    {
        Writer::setInstance($driver);
        $parser  = new SQLParser();
        $queries = $parser->parse($input);

        $this->assertEquals($output, (string) $queries[0]);
    }

    public function testExprReturnsNull()
    {
        $x = new SQLParser\Stmt\Expr('xxx');
        $this->assertNull($x->getMember(10));
    }

    public function testInvalidWriterObject()
    {
        $this->expectException(InvalidArgumentException::class);
        Writer::create($this);
    }

    public function testInvalidWriterInstance()
    {
        $this->expectException(InvalidArgumentException::class);
        Writer::setInstance($this);
    }

    public function testWriterFromPDO()
    {
        $driver = new PDO('sqlite::memory:');
        Writer::setInstance($driver);

        $this->assertTrue(Writer::getInstance() instanceof SQL\Writer\SQLite);
    }

    public function testInvalidVariableValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $parser  = new SQLParser();
        $queries = $parser->parse('SELECT * FROM t WHERE y = :name');
        Writer::create($queries[0], ['name' => ['invalid value']]);
    }
}
