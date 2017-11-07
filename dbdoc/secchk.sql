
drop TABLE `secchk3212`;
CREATE TABLE `secchk3212` (
  `id` int(11) NOT NULL,

  confirm1  tinyint default 0,
  time1  datetime,
  `mac` char(17) DEFAULT NULL COMMENT 'MAC 주소',

  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4790 DEFAULT CHARSET=euckr ;


