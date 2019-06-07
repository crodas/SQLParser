<?php

use SQL\Writer;
use SQLParser\Stmt\VariablePlaceholder;
use SQLParser\Stmt\Expr;
use SQLParser\Writer\SQL;

return [
    "SELECT x, 5+10 as a, 90*10" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals($query->getTables(), array());
        $phpunit->assertFalse($query->hasWhere());
        $phpunit->assertEquals("SELECT `x`, 5 + 10 AS `a`, 90 * 10", Writer::create($query));
        return true;
    },
    "SELECT 5+10, 90*10" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals($query->getTables(), array());
        $phpunit->assertFalse($query->hasWhere());
        return true;
    },
    "SELECT 5+10, 90*10 WHERE 2" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals($query->getTables(), array());
        $phpunit->assertTrue($query->hasWhere());
        $phpunit->assertEquals(2, $query->getWhere()->getMembers()[0]);
    },
    "((SELECT * FROM `table` X))" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query  = $query[0];
        $tables = $query->getTables();
        $phpunit->assertEquals(['X' => 'table'], $tables);

        $tables = ['X' => 'foobar'];
        $query->setTableS($tables);

        $phpunit->assertEquals("SELECT * FROM `foobar` AS `X`", Writer::create($query));

        $tables = ['Y' => 'foobar'];
        $query->setTableS($tables);
        $phpunit->assertEquals("SELECT * FROM `foobar` AS `Y`", Writer::create($query));

        $tables = ['foobar'];
        $query->setTableS($tables);

        $phpunit->assertEquals("SELECT * FROM `foobar`", Writer::create($query));

        $tables = ['something' => 'foobar'];
        $query->setTables($tables);
        $phpunit->assertEquals("SELECT * FROM `foobar` AS `something`", Writer::create($query));

        $tables = ['something' => ['x', 'foobar']];
        $query->setTables($tables);
        $phpunit->assertEquals("SELECT * FROM `x`.`foobar` AS `something`", Writer::create($query));
        $phpunit->assertFalse($query->hasWhere());
        $phpunit->assertFalse($query->hasOrderBy());
        $phpunit->assertFalse($query->hasLimit());
        $phpunit->assertEquals([], $query->getVariables());
    },
    "SELECT * FROM `table` X" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query  = $query[0];
        $phpunit->assertEquals($query->getTables(), ['X' => 'table']);

        $query->setTables(['X' => 'foobar']);
        $phpunit->assertEquals("SELECT * FROM `foobar` AS `X`", Writer::create($query));

        $query->setTables(['Y' => 'foobar']);
        $phpunit->assertEquals("SELECT * FROM `foobar` AS `Y`", Writer::create($query));

        $query->setTables(['foobar']);
        $phpunit->assertEquals("SELECT * FROM `foobar`", Writer::create($query));

        $query->setTables(['something' => 'foobar']);
        $phpunit->assertEquals("SELECT * FROM `foobar` AS `something`", Writer::create($query));

        $query->setTables(['something' => ['x', 'foobar']]);
        $phpunit->assertEquals("SELECT * FROM `x`.`foobar` AS `something`", Writer::create($query));
        $phpunit->assertFalse($query->hasWhere());
        $phpunit->assertFalse($query->hasOrderBy());
        $phpunit->assertFalse($query->hasLimit());
        $phpunit->assertEquals([], $query->getVariables());
    },
    "SELECT * FROM `db`.`table` AS `X`" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals($query->getTables(), ["X" => ["db", "table"]]);
        $phpunit->assertFalse($query->hasWhere());
        $phpunit->assertFalse($query->hasOrderBy());
        $phpunit->assertFalse($query->hasLimit());
        $phpunit->assertEquals([], $query->getVariables());
    },
    "SELECT `foo`.* FROM `table` WHERE `foo`.`bar` = 99 and `xx` = \$foobar LIMIT 10" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals("SELECT `foo`.* FROM `table` WHERE `foo`.`bar` = 99 AND `xx` = :foobar LIMIT 10", Writer::create($query));
        $phpunit->assertEquals($query->getTables(), ["table"]);
        $phpunit->assertTrue($query->hasWhere());
        $phpunit->assertTrue($query->hasLimit());
        $phpunit->assertFalse($query->hasOrderBy());
        $phpunit->assertEquals(['foobar'], $query->getVariables());
    },
    "SELECT `foo`.* FROM `table` WHERE `id` IN (SELECT `id` FROM `yy` WHERE `foo`.`bar` = 99 and `xx` = ?) LIMIT 10" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals($query->getTables(), ["table"]);
        $phpunit->assertTrue($query->hasWhere());
        $phpunit->assertTrue($query->hasLimit());
        $phpunit->assertFalse($query->hasOrderBy());
        $phpunit->assertEquals(['?'], $query->getVariables());
        $phpunit->assertEquals("SELECT `foo`.* FROM `table` WHERE `id` IN (SELECT `id` FROM `yy` WHERE `foo`.`bar` = 99 AND `xx` = ?) LIMIT 10", Writer::create($query));
    },
    "SELECT * FROM table1 WHERE foo = bar AND foo = 'bar'" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $phpunit->assertEquals(
            'SELECT * FROM `table1` WHERE `foo` = `bar` AND `foo` = "bar"',
            Writer::create($query[0])
        );
    },
    "SELECT ID, CONCAT(NAME, ' FROM ', DEPT) AS NAME, SALARY FROM employee" => function($query, $phpunit){
        $phpunit->assertEquals(count($query), 1);
        $phpunit->assertEquals(
            'SELECT `ID`, CONCAT(`NAME`, " FROM ", `DEPT`) AS `NAME`, `SALARY` FROM `employee`',
            Writer::create($query[0])
        );
    },
    "SELECT * FROM (SELECT * FROM `table`) X" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query  = $query[0];
        $tables = $query->getTables();
        $phpunit->assertTrue($tables['X'] instanceof \SQL\Select);
        $phpunit->assertEquals("SELECT * FROM (SELECT * FROM `table`) AS `X`", Writer::create($query));

        $tables = $query->getTables();
        $query->setTables(['yyy' => current($tables)]);
        $phpunit->assertEquals("SELECT * FROM (SELECT * FROM `table`) AS `yyy`", Writer::create($query));

        $sub = $query->getTables()['yyy'];
        $sub->setTables(['x' => $sub->getTables()[0]]);
        $phpunit->assertEquals("SELECT * FROM (SELECT * FROM `table` AS `x`) AS `yyy`", Writer::create($query));

        $query->setTables(['yyy' => 'y']);
        $phpunit->assertEquals("SELECT * FROM `y` AS `yyy`", Writer::create($query));
        return false;
    },
    "-- some comment here
    SELECT * FROM `table` -- more here
    WHERE id = ?; SELECT 1 -- not here" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 2);
        $phpunit->assertEquals(['some comment here', 'more here'], $queries[0]->getComments());
        $phpunit->assertEquals(['not here'], $queries[1]->getComments());
        return false;
    },
    "INSERT INTO `table` SET foo=:foo,bar=:bar" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(['foo', 'bar'], $queries[0]->getVariables());
        $phpunit->assertEquals('INSERT INTO `table`(`foo`, `bar`) VALUES(:foo, :bar)', (string)$queries[0]);
    },
    "INSERT INTO `table` SET foo=?,bar=?" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(['?', '?'], $queries[0]->getVariables());
        $phpunit->assertEquals('INSERT INTO `table`(`foo`, `bar`) VALUES(?, ?)', (string)$queries[0]);
    },
    "INSERT INTO `table`(foo,bar) VALUES(?, ?)" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(['?', '?'], $queries[0]->getVariables());
        $phpunit->assertEquals('INSERT INTO `table`(`foo`, `bar`) VALUES(?, ?)', (string)$queries[0]);
    },
    "INSERT INTO `table` VALUES(?, ?)" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(['?', '?'], $queries[0]->getVariables());
        $phpunit->assertEquals('INSERT INTO `table` VALUES(?, ?)', (string)$queries[0]);
    },

    "INSERT INTO `table` SELECT foo, bar, xxx FROM `table2` WHERE x = ? and y = ? ORDER BY id DESC LIMIT 20" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(['?', '?'], $queries[0]->getVariables());
        $phpunit->assertEquals('INSERT INTO `table` SELECT `foo`, `bar`, `xxx` FROM `table2` WHERE `x` = ? AND `y` = ? ORDER BY `id` DESC LIMIT 20', (string)$queries[0]);
    },
    "SELECT * FROM 
        (select x FROM y) as x 
    WHERE 
        x.y IN (SELECT foo FROM x) AND 
        x = (SELECT x FROM y LIMIT 1,?) 
    ORDER BY foobar DESC
    LIMIT 20" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(['?'], $queries[0]->getVariables());
        $phpunit->assertEquals(
            "SELECT * FROM (SELECT `x` FROM `y`) AS `x` WHERE `x`.`y` IN (SELECT `foo` FROM `x`) AND `x` = (SELECT `x` FROM `y` LIMIT 1,?) ORDER BY `foobar` DESC LIMIT 20",
            (String)$queries[0]
        );
    },
    "SELECT 
        x.*,
        f.*,
        b.*
    FROM 
        (select x FROM y) as x,
        `another tabl` as y
    JOIN foo f ON f.x = x.y
    LEFT JOIN bar b ON b.x = x.y
    WHERE 
        x.y IN (SELECT foo FROM x) AND 
        x = (SELECT x FROM y LIMIT 1,?) 
    ORDER BY foobar DESC
    LIMIT 20" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(['?'], $queries[0]->getVariables());
        $phpunit->assertEquals(
            "SELECT `x`.*, `f`.*, `b`.* FROM (SELECT `x` FROM `y`) AS `x`, `another tabl` AS `y` INNER JOIN `foo` AS `f` ON `f`.`x` = `x`.`y` LEFT JOIN `bar` AS `b` ON `b`.`x` = `x`.`y` WHERE `x`.`y` IN (SELECT `foo` FROM `x`) AND `x` = (SELECT `x` FROM `y` LIMIT 1,?) ORDER BY `foobar` DESC LIMIT 20",
            (String)$queries[0]
        );
    },
    "DELETE FROM table3 WHERE y = ? ORDER BY e DESC LIMIT 1" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(['?'], $queries[0]->getVariables());
        $phpunit->assertEquals(
            "DELETE FROM `table3` WHERE `y` = ? ORDER BY `e` DESC LIMIT 1",
            (String)$queries[0]
        );
    },
    "SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type='sub_logo' and media.id = subs.id and media.version = 0) where subs.name = 'mnm'
    " => function ($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(
            'SELECT `subs`.*, `media`.`id` AS `media_id`, `media`.`size` AS `media_size`, `media`.`dim1` AS `media_dim1`, `media`.`dim2` AS `media_dim2`, `media`.`extension` AS `media_extension`, UNIX_TIMESTAMP(`media`.`date`) AS `media_date` FROM `subs` LEFT JOIN `media` ON (`media`.`type` = "sub_logo" AND `media`.`id` = `subs`.`id` AND `media`.`version` = 0) WHERE `subs`.`name` = "mnm"',
            (string)$queries[0]
        );
    },
    "SELECT
        SQL_NO_CACHE
        ALL
        SQL_CALC_FOUND_ROWS a.* 
    FROM 
         articles AS a
    JOIN tags2articles AS ta  ON a.id=ta.idArticle
    JOIN tags AS t ON ta.idTag=t.id
    WHERE 
        t.id IN (12,13,16) 
    GROUP BY a.id
    HAVING
      COUNT(t.id)=3
    "   => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(
            "SELECT SQL_NO_CACHE ALL SQL_CALC_FOUND_ROWS `a`.* FROM `articles` AS `a` INNER JOIN `tags2articles` AS `ta` ON `a`.`id` = `ta`.`idArticle` INNER JOIN `tags` AS `t` ON `ta`.`idTag` = `t`.`id` WHERE `t`.`id` IN (12, 13, 16) GROUP BY `a`.`id` HAVING COUNT(`t`.`id`) = 3",
            (string)$queries[0]
        );
        $old = Writer::getInstance();
        Writer::setInstance('sqlite');
        $phpunit->assertEquals(
            "SELECT ALL a.* FROM articles AS a INNER JOIN tags2articles AS ta ON a.id = ta.idArticle INNER JOIN tags AS t ON ta.idTag = t.id WHERE t.id IN (12, 13, 16) GROUP BY a.id HAVING COUNT(t.id) = 3",
            (string)$queries[0]
        );
        Writer::setInstance($old);
    },
    "SELECT city, max(temp_lo)
    FROM weather
    WHERE city LIKE 'S%'
    GROUP BY city
    HAVING max(temp_lo) < 40" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(
            'SELECT `city`, max(`temp_lo`) FROM `weather` WHERE `city` LIKE "S%" GROUP BY `city` HAVING max(`temp_lo`) < 40',
            (string)$queries[0]
        );

    },
    'INSERT INTO `table` VALUES(sha1(xx))' => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $query = $queries[0];
        $phpunit->assertEquals(1, count($query->getFunctionCalls()));
        $query->iterate(function($expr) {
            if ($expr instanceof Expr && $expr->is('call')) {
                return new VariablePlaceholder("xxx");
            }
        });
        $phpunit->assertEquals(
            'INSERT INTO `table` VALUES(:xxx)',
            (string)$query
        );
    },
    'SELECT * FROM url WHERE hash = url($sha1)' => function($queries, $phpunit) {
        $phpunit->assertEquals(1, count($queries));
        $query = $queries[0];
        $phpunit->assertEquals(1, count($query->getFunctionCalls()));
        $query->iterate(function($expr) {
            if ($expr instanceof Expr && $expr->is('call')) {
                return new VariablePlaceholder("xxx");
            }
        });

        $phpunit->assertEquals(['xxx'], $query->getVariables('where'));

        $phpunit->assertEquals(
            "SELECT * FROM `url` WHERE `hash` = :xxx",
            (string)$query
        );
    },
    'alter table xxx change column yyy yyy int first' => function($queries, $phpunit) {
        $phpunit->assertEquals(1, count($queries));
        $phpunit->assertTrue($queries[0] instanceof \SQL\AlterTable\AlterTable);
        $phpunit->assertTrue($queries[0]->isFirst());
    },
    'alter table xxx change column yyy yyy int after xxx' => function($queries, $phpunit) {
        $phpunit->assertEquals(1, count($queries));
        $phpunit->assertTrue($queries[0] instanceof \SQL\AlterTable\AlterTable);
        $phpunit->assertFalse($queries[0]->isFirst());
    },
];
