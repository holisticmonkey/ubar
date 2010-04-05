CREATE TABLE IF NOT EXISTS `users` (
  `userid` int(11) NOT NULL auto_increment,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `isadmin` boolean DEFAULT false,
  `confirmed` boolean DEFAULT false,
  PRIMARY KEY  (`userid`),
  KEY `email` (`email`)
) TYPE=MyISAM;

INSERT INTO users SET email='user@domain.com', password=MD5('default'), isadmin=true, confirmed=true;