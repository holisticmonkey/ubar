CREATE TABLE IF NOT EXISTS `blogcomments` (
  `commentid` int(11) NOT NULL auto_increment,
  `blogid` int(11) NOT NULL,
  `name` VARCHAR(255),
  `message` LONGTEXT NOT NULL,
  `created` timestamp(14) NOT NULL,
  PRIMARY KEY  (`commentid`)
) TYPE=MyISAM;