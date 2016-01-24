ALTER TABLE testalter_tbl ADD i INT AFTER c;
ALTER TABLE testalter_tbl ADD x INT AFTER x;
ALTER TABLE testalter_tbl ADD i INT FIRST;
ALTER TABLE testalter_tbl1 CHANGE foo bar int;
--alter table testalter_tb1  CHANGE foo SET DEFAULT = 'ceasr'
--alter table testalter_tb1  CHANGE foo DROP DEFAULT
ALTER TABLE testalter_tbl1 CHANGE COLUMN foo bar int;
ALTER TABLE testalter_tbl1 CHANGE COLUMN foo bar int default 'y';
