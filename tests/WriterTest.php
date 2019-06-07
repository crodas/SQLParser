<?php

use SQLParser\Stmt;
use SQL\Writer;
use PHPUnit\Framework\TestCase;

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

    public function testExprReturnsNull()
    {
        $x = new SQLParser\Stmt\Expr('xxx');
        $this->assertNull($x->getMember(10));
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidWriterObject()
    {
        Writer::create($this);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidWriterInstance()
    {
        Writer::setInstance($this);
    }

    public function testWriterFromPDO()
    {
        $driver = new PDO("sqlite:memory");
        Writer::setInstance($driver);

        $this->assertTrue(Writer::getInstance() instanceof SQL\Writer\SQLite);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidVariableValue()
    {
        $parser = new SQLParser();
        $queries = $parser->parse("SELECT * FROM t WHERE y = :name");
        Writer::create($queries[0], ['name' => ['invalid value']]);
    }
}
