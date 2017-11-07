CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `memo` char(255) DEFAULT NULL,
  `idate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ;
