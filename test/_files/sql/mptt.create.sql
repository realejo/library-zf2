 CREATE TABLE IF NOT EXISTS `mptt`  (
                  `id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
                  `name` varchar(100) NOT NULL,
                  `parent_id` tinyint(1) unsigned,
                  `lft` tinyint(1) unsigned,
                  `rgt` tinyint(1) unsigned,
                  PRIMARY KEY  (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

