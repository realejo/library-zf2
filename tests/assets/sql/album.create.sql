 CREATE TABLE IF NOT EXISTS `album`  (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `artist` varchar(100) NOT NULL,
                  `title` varchar(100) NOT NULL,
                  `deleted` tinyint(1) unsigned NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;