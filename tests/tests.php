<?php

use SQLParser\Writer;
use SQLParser\Writer\SQL;

return array(
    "SELECT 5+10 as a, 90*10" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals($query->getTable()[0], null);
        $phpunit->assertFalse($query->hasWhere());
        return true;
    },
    "SELECT 5+10, 90*10" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals($query->getTable()[0], null);
        $phpunit->assertFalse($query->hasWhere());
        return true;
    },
    "SELECT 5+10, 90*10 WHERE 2" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals($query->getTable()[0], null);
        $phpunit->assertTrue($query->hasWhere());
        $phpunit->assertEquals(2, $query->getWhere()->getMembers()[0]);
    },
    "((SELECT * FROM 'table' X))" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals($query->getTable()[0]->getValue(), "table");
        $phpunit->assertEquals($query->getTable()[0]->getAlias(), "X");

        $query->getTable()[0]->setValue("foobar");
        $phpunit->assertEquals("SELECT * FROM `foobar` AS `X`", (string)$query);

        $query->getTable()[0]->setAlias("Y");
        $phpunit->assertEquals("SELECT * FROM `foobar` AS `Y`", (string)$query);
        $query->getTable()[0]->SetAlias(null);
        $phpunit->assertEquals("SELECT * FROM `foobar`", (string)$query);
        $query->getTable()[0]->SetAlias('something');
        $phpunit->assertEquals("SELECT * FROM `foobar` AS `something`", (string)$query);

        $query->getTable()[0]->setValue(['x', 'foobar']);
        $phpunit->assertEquals("SELECT * FROM `x`.`foobar` AS `something`", (string)$query);
        $phpunit->assertFalse($query->hasWhere());
        $phpunit->assertFalse($query->hasOrderBy());
        $phpunit->assertFalse($query->hasLimit());
        $phpunit->assertEquals([], $query->getVariables());
    },
    "SELECT * FROM 'table' X" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals($query->getTable()[0]->getValue(), "table");
        $phpunit->assertEquals($query->getTable()[0]->getAlias(), "X");

        $query->getTable()[0]->setValue("foobar");
        $phpunit->assertEquals("SELECT * FROM `foobar` AS `X`", (string)$query);

        $query->getTable()[0]->setAlias("Y");
        $phpunit->assertEquals("SELECT * FROM `foobar` AS `Y`", (string)$query);
        $query->getTable()[0]->SetAlias(null);
        $phpunit->assertEquals("SELECT * FROM `foobar`", (string)$query);
        $query->getTable()[0]->SetAlias('something');
        $phpunit->assertEquals("SELECT * FROM `foobar` AS `something`", (string)$query);

        $query->getTable()[0]->setValue(['x', 'foobar']);
        $phpunit->assertEquals("SELECT * FROM `x`.`foobar` AS `something`", (string)$query);
        $phpunit->assertFalse($query->hasWhere());
        $phpunit->assertFalse($query->hasOrderBy());
        $phpunit->assertFalse($query->hasLimit());
        $phpunit->assertEquals([], $query->getVariables());
    },
    "SELECT * FROM `db`.`table` AS `X`" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals($query->getTable()[0]->getValue(), ["db", "table"]);
        $phpunit->assertEquals($query->getTable()[0]->getAlias(), "X");
        $phpunit->assertFalse($query->hasWhere());
        $phpunit->assertFalse($query->hasOrderBy());
        $phpunit->assertFalse($query->hasLimit());
        $phpunit->assertEquals([], $query->getVariables());
    },
    "SELECT `foo`.* FROM `table` WHERE `foo`.`bar` = 99 and `xx` = \$foobar LIMIT 10" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals("SELECT `foo`.* FROM `table` WHERE `foo`.`bar` = 99 AND `xx` = :foobar LIMIT 10", (string)$query);
        $phpunit->assertEquals($query->getTable()[0]->getValue(), "table");
        $phpunit->assertTrue($query->hasWhere());
        $phpunit->assertTrue($query->hasLimit());
        $phpunit->assertFalse($query->hasOrderBy());
        $phpunit->assertEquals(['foobar'], $query->getVariables());
    },
    "SELECT `foo`.* FROM `table` WHERE `id` IN (SELECT `id` FROM `yy` WHERE `foo`.`bar` = 99 and `xx` = ?) LIMIT 10" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertEquals($query->getTable()[0]->getValue(), "table");
        $phpunit->assertTrue($query->hasWhere());
        $phpunit->assertTrue($query->hasLimit());
        $phpunit->assertFalse($query->hasOrderBy());
        $phpunit->assertEquals(['?'], $query->getVariables());
        $phpunit->assertEquals("SELECT `foo`.* FROM `table` WHERE `id` IN (SELECT `id` FROM `yy` WHERE `foo`.`bar` = 99 AND `xx` = ?) LIMIT 10", (string)$query);
    },
    "SELECT * FROM table1 WHERE foo = bar AND foo = 'bar'" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $phpunit->assertEquals(
            "SELECT * FROM `table1` WHERE `foo` = `bar` AND `foo` = 'bar'",
            (string)$query[0]
        );
    },
    "SELECT ID, CONCAT(NAME, ' FROM ', DEPT) AS NAME, SALARY FROM employee" => function($query, $phpunit){
        $phpunit->assertEquals(count($query), 1);
        $phpunit->assertEquals(
            "SELECT `ID`,CONCAT(`NAME`,'FROM',`DEPT`) AS `NAME`,`SALARY` FROM `employee`",
            (string)$query[0]
        );
    },
    "SELECT * FROM (SELECT * FROM 'table') X" => function($query, $phpunit) {
        $phpunit->assertEquals(count($query), 1);
        $query = $query[0];
        $phpunit->assertTrue($query->getTable()[0]->getValue() instanceof \SQLParser\Select);
        $phpunit->assertEquals("SELECT * FROM (SELECT * FROM `table`) AS `X`", (string)$query);

        $query->getTable()[0]->setAlias('yyy');
        $phpunit->assertEquals("SELECT * FROM (SELECT * FROM `table`) AS `yyy`", (string)$query);

        $query->getTable()[0]->getValue()->getTable()[0]->setAlias('x');
        $phpunit->assertEquals("SELECT * FROM (SELECT * FROM `table` AS `x`) AS `yyy`", (string)$query);

        $query->getTable()[0]->setValue('y');
        $phpunit->assertEquals("SELECT * FROM `y` AS `yyy`", (string)$query);
        return false;
    },
    "-- some comment here
    SELECT * FROM 'table' -- more here
    WHERE id = ?; SELECT 1 -- not here" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 2);
        $phpunit->assertEquals(['some comment here', 'more here'], $queries[0]->getComment());
        $phpunit->assertEquals(['not here'], $queries[1]->getComment());
        return false;
    },
    "INSERT INTO `table` SET foo=?,bar=?" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(['?', '?'], $queries[0]->getVariables());
        $phpunit->assertEquals('INSERT INTO `table`(`foo`,`bar`) VALUES(?,?)', (string)$queries[0]);
    },
    "INSERT INTO `table`(foo,bar) VALUES(?,?)" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(['?', '?'], $queries[0]->getVariables());
        $phpunit->assertEquals('INSERT INTO `table`(`foo`,`bar`) VALUES(?,?)', (string)$queries[0]);
    },
    "INSERT INTO `table` VALUES(?,?)" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(['?', '?'], $queries[0]->getVariables());
        $phpunit->assertEquals('INSERT INTO `table` VALUES(?,?)', (string)$queries[0]);
    },

    "INSERT INTO `table` SELECT foo, bar, xxx FROM `table2` WHERE x = ? and y = ? ORDER BY id DESC LIMIT 20" => function($queries, $phpunit) {
        $phpunit->assertEquals(count($queries), 1);
        $phpunit->assertEquals(['?', '?'], $queries[0]->getVariables());
        $phpunit->assertEquals('INSERT INTO `table` SELECT `foo`,`bar`,`xxx` FROM `table2` WHERE `x` = ? AND `y` = ? ORDER BY `id` DESC LIMIT 20', (string)$queries[0]);
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
            "SELECT `x`.*,`f`.*,`b`.* FROM (SELECT `x` FROM `y`) AS `x`,`another tabl` AS `y` INNER JOIN `foo` AS `f` ON `f`.`x` = `x`.`y` LEFT JOIN `bar` AS `b` ON `b`.`x` = `x`.`y` WHERE `x`.`y` IN (SELECT `foo` FROM `x`) AND `x` = (SELECT `x` FROM `y` LIMIT 1,?) ORDER BY `foobar` DESC LIMIT 20",
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
            "SELECT `subs`.*,`media`.`id` AS `media_id`,`media`.`size` AS `media_size`,`media`.`dim1` AS `media_dim1`,`media`.`dim2` AS `media_dim2`,`media`.`extension` AS `media_extension`,UNIX_TIMESTAMP(`media`.`date`) AS `media_date` FROM `subs` LEFT JOIN `media` ON (`media`.`type` = 'sub_logo' AND `media`.`id` = `subs`.`id` AND `media`.`version` = 0) WHERE `subs`.`name` = 'mnm'",
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
        $phpunit->assertEquals(
            "SELECT SQL_NO_CACHE ALL SQL_CALC_FOUND_ROWS `a`.* FROM `articles` AS `a` INNER JOIN `tags2articles` AS `ta` ON `a`.`id` = `ta`.`idArticle` INNER JOIN `tags` AS `t` ON `ta`.`idTag` = `t`.`id` WHERE `t`.`id` IN (12,13,16) GROUP BY `a`.`id` HAVING COUNT(`t`.`id`) = 3",
            (string)$queries[0]
        );
        SQL::setInstance(new Writer\SQL);
        $phpunit->assertEquals(
            "SELECT ALL 'a'.* FROM 'articles' AS 'a' INNER JOIN 'tags2articles' AS 'ta' ON 'a'.'id' = 'ta'.'idArticle' INNER JOIN 'tags' AS 't' ON 'ta'.'idTag' = 't'.'id' WHERE 't'.'id' IN (12,13,16) GROUP BY 'a'.'id' HAVING COUNT('t'.'id') = 3",
            (string)$queries[0]
        );
        SQL::setInstance(new Writer\MySQL);
    }
);
