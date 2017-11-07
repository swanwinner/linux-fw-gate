
CREATE TABLE `oui` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mac6` char(8) DEFAULT NULL,
  `company` char(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `mac6` (`mac6`)
) ;

