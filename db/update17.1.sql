CREATE TABLE `zt_riskissue` (
  `risk` mediumint(8) unsigned NOT NULL,
  `issue` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `risk_issue` (`risk`,`issue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `zt_kanban` ADD `alignment` varchar(10) NOT NULL DEFAULT 'center' AFTER `object`;

