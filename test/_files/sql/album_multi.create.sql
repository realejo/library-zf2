 CREATE TABLE IF NOT EXISTS `album_multi`  (
  `id_int` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_string` CHAR(1) NOT NULL default 'N',
  `artist` varchar(100) NULL,
  `title` varchar(100) NULL,
  `deleted` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_int`, `id_string`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
