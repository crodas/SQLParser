ALTER TABLE testalter_tbl ADD i INT AFTER c;
ALTER TABLE testalter_tbl ADD x INT AFTER x;
ALTER TABLE testalter_tbl ADD i INT FIRST;
ALTER TABLE testalter_tbl1 CHANGE foo bar int;
ALTER TABLE testalter_tb1  CHANGE foo SET DEFAULT 'ceasr';
ALTER TABLE testalter_tb1  CHANGE foo DROP DEFAULT;
ALTER TABLE testalter_tbl1 CHANGE COLUMN foo bar int;
ALTER TABLE testalter_tbl1 CHANGE COLUMN foo bar int default 'y';
ALTER TABLE foobar DROP KEY yy;
ALTER TABLE foobar DROP INDEX yy;
ALTER TABLE t1 CHANGE b b BIGINT NOT NULL;
ALTER TABLE t1 MODIFY b BIGINT NOT NULL;
ALTER TABLE foobar DROP PRIMARY KEY;
