-- @name users
CREATE TABLE weather (
        city            varchar(80) primary key,
        temp_lo         int,           -- low temperature
        temp_hi         int,           -- high temperature
        prcp            real,          -- precipitation
        date            date
        );

CREATE TABLE Persons
(
 PersonID int,
 LastName varchar(255),
 FirstName varchar(255),
 Address varchar(255),
 City varchar(255)
 );

CREATE TABLE users (
    user_id int not null primary key,
    email varchar(250) not null,
    password varchar(250) not null
);

CREATE TABLE `weather` (
    `city` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
    `temp_lo` int(11) DEFAULT NULL,
    `temp_hi` int(11) DEFAULT NULL,
    `prcp` double DEFAULT NULL,
    `date` date DEFAULT NULL
);

CREATE TABLE `all_strings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `text` text NOT NULL,
        `description` text NOT NULL,
        `sectionid` int(11) NOT NULL,
        `timestamp` int(11) NOT NULL,
        `still_used` tinyint(4) DEFAULT '0',
        PRIMARY KEY (`id`),
        UNIQUE KEY `text` (`text`),
        KEY `still_used` (`still_used`)
) ENGINE=InnoDB AUTO_INCREMENT=7575 DEFAULT CHARSET=latin1;
