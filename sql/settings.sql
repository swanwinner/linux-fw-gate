
 CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `skey` char(100) DEFAULT NULL,
  `svalue` char(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `skey` (`skey`),
  KEY `id` (`id`)
) ;

insert into settings set skey='block_new_mac_address', svalue='0';


