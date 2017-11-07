
 CREATE TABLE `login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` char(30) DEFAULT NULL,
  `password` char(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
);

insert into login set username='admin', password=md5('0000');

