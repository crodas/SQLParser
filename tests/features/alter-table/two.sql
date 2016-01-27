-- OLD

create table foobar(
    id int not null
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
ALTER TABLE `foobarx` ADD COLUMN `y` int NOT NULL DEFAULT 99 AFTER `x`;
ALTER TABLE `foobarx` ADD COLUMN `xx` int AFTER `y`;
CREATE  INDEX `foo` ON `foobarx` ( `y` DESC, `x` ASC);

