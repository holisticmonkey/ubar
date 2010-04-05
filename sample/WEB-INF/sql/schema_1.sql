CREATE TABLE IF NOT EXISTS `blog` (
  `blogid` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `message` LONGTEXT NOT NULL,
  `created` timestamp(14) NOT NULL,
  `modified` timestamp(14),
  PRIMARY KEY  (`blogid`)
) TYPE=MyISAM;