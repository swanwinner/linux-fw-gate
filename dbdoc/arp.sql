
drop table if exists arp ;
create table arp (
  id   int auto_increment primary key,
  ip   char(20),
  mac  char(20),
  udate  datetime
);

