

drop table points;

CREATE TABLE `points` (
  `id` int(11) DEFAULT NULL,
  `mac` char(17) DEFAULT NULL COMMENT 'MAC 주소',
  `point` smallint(6) DEFAULT '0' comment '합계',
  `p1` smallint(6) DEFAULT '0',
  `p2` smallint(6) DEFAULT '0',
  `p3` smallint(6) DEFAULT '0',
  `p4` smallint(6) DEFAULT '0',
  `p5` smallint(6) DEFAULT '0',
  `p6` smallint(6) DEFAULT '0',
  `p7` smallint(6) DEFAULT '0',
  `p8` smallint(6) DEFAULT '0',
  `p9` smallint(6) DEFAULT '0',
  `p10` smallint(6) DEFAULT '0',
  `p11` smallint(6) DEFAULT '0',
  `p12` smallint(6) DEFAULT '0',
  `p13` smallint(6) DEFAULT '0',
  `p14` smallint(6) DEFAULT '0',
  `p15` smallint(6) DEFAULT '0',
  `p16` smallint(6) DEFAULT '0',
  `p17` smallint(6) DEFAULT '0',
  `p18` smallint(6) DEFAULT '0',
  `p19` smallint(6) DEFAULT '0',
  `p20` smallint(6) DEFAULT '0',
  unique mac(mac),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

