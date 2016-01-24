ALTER TABLE testalter_tbl ADD i INT AFTER c;
ALTER TABLE testalter_tbl ADD x INT AFTER x;
ALTER TABLE testalter_tbl ADD i INT FIRST;
ALTER TABLE testalter_tbl1 CHANGE foo bar int;
ALTER TABLE testalter_tbl1 CHANGE COLUMN foo bar int;
ALTER TABLE testalter_tbl1 CHANGE COLUMN foo bar int default 'y';
