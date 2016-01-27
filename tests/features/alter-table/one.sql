-- OLD

create table foobar(
            id int not null,
            y int not null,
            xx int,
            foobar int,
            key foo (y)
);

-- NEW

create table foobarx(
    id int not null,
    x int not null,
    y int not null default 99,
    xx int,
    key foo(y desc, x asc)
);

ALTER TABLE `foobar` RENAME TO `foobarx`;
ALTER TABLE `foobarx` ADD COLUMN `x` int NOT NULL AFTER `id`;
ALTER TABLE `foobarx` CHANGE COLUMN `y` `y` int NOT NULL DEFAULT 99 AFTER `x`;
ALTER TABLE `foobarx` DROP  COLUMN`foobar`;
ALTER TABLE `foobarx` DROP INDEX `foo`;
CREATE  INDEX `foo` ON `foobarx` ( `y` DESC, `x` ASC);
