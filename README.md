# SQLParser

SQL-Parser

Why?
---

Sometimes we need to parse and validate SQL.

What does it do?
---------------

It parse SQL (mostly MySQL's SQL) and exposes the SQL query as an object so it can be manipulated and assembled back to a query.

How to install?
--------------

```
composer install crodas/sql-parser
```

How to use it?
-------------

```php
require __DIR__ . "/vendor/autoload.php";

$parser = new SQLParser;
$queries = $parser->parse("SELECT * FROM table1 WHERE id = :id");
var_dump(get_class($queries[0])); // string(16) "SQLParser\Select"
var_dump($queries[0]->getTable()[0]->getValue()); // string(6) "table1"
/*
 array(1) {
   [0] =>
   string(2) "id"
   }
*/
var_dump($queries[0]->getVariables()); 

// SELECT * FROM 'table1' WHERE 'id' = :id
echo $queries[0] . "\n";

SQLParser\Writer\SQL::setInstance(new SQLParser\Writer\MySQL);

// SELECT * FROM `table1` WHERE `id` = :id
echo $queries[0] . "\n";
```

TODO:
----

1. Better documentation
2. Fluent-Interface to generate SQL statements and alter the parsed content
3. parse CREATE TABLE/ALTER TABLE (for SQLite, MySQL and PostgreSQL flavors)
