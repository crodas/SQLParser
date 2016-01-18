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
        PRIMARY KEY (`id`, `text`),
        UNIQUE KEY `text` (`text`),
        KEY `still_used` (`still_used`)
) ENGINE=InnoDB AUTO_INCREMENT=7575 DEFAULT CHARSET=latin1;

CREATE TABLE `sync_os` (
          `sync_os_id` int(11) NOT NULL AUTO_INCREMENT,
            `os` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `changelog` longtext COLLATE utf8_unicode_ci,
                `saved` tinyint(1) DEFAULT '0',
                  `created` datetime DEFAULT NULL,
                    `url32` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                      `url32n` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                        `url64` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                          `url64n` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                            `help_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                              PRIMARY KEY (`sync_os_id`),
                                KEY `IDX_2FD9E663F9E3D325` (`saved`),
                                  KEY `IDX_2FD9E663B23DB7B8` (`created`)
        ) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `about_teams` (
          `about_team_id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `role` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                `twitter` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `linkedin` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `saved` tinyint(1) DEFAULT '0',
                      `created` datetime DEFAULT NULL,
                        PRIMARY KEY (`about_team_id`),
                          KEY `IDX_FDBA6687F9E3D325` (`saved`)
        ) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
