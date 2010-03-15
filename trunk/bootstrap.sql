CREATE TABLE IF NOT EXISTS `ubarmetainfo` (
  `infoid` int(11) NOT NULL auto_increment,
  `name` varchar(255) UNIQUE NOT NULL,
  `val` varchar(255) NOT NULL,
  PRIMARY KEY  (`infoid`)
) TYPE=MyISAM;

INSERT INTO ubarmetainfo SET name='schemaversion', val='0';