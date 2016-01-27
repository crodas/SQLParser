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
        PRIMARY KEY (`id` asc, `text` desc),
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

CREATE TABLE `attaches` (
          `attach_id` int(11) NOT NULL AUTO_INCREMENT,
            `sha2` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
              `type` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                `size` int(11) DEFAULT '0',
                  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `content` longblob,
                      `fname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                        `created` datetime DEFAULT NULL,
                          PRIMARY KEY (`attach_id`),
                            KEY `IDX_EB74E74FAF37D4EA` (`fname`)
        ) ENGINE=InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `wp_commentmeta` (
          `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `comment_id` bigint(20) unsigned NOT NULL DEFAULT '0',
              `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `meta_value` longtext COLLATE utf8mb4_unicode_ci,
                  PRIMARY KEY (`meta_id`),
                    KEY `comment_id` (`comment_id`),
                      KEY `meta_key` (`meta_key`(191))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
