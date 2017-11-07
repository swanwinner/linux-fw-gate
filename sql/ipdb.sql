
 CREATE TABLE `ipdb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` char(20) DEFAULT NULL COMMENT 'IP주소',
  `ip3` int(11) DEFAULT NULL COMMENT 'IP주소 3번째자리',
  `ip4` int(11) DEFAULT NULL COMMENT 'IP주소 4번째자리',
  `secondip` char(20) DEFAULT NULL COMMENT 'IP가2개 할당인 경우',
  `staticip` tinyint(4) DEFAULT '1' COMMENT '고정 IP인지 여부',
  `ipnow` char(20) DEFAULT NULL COMMENT '현재사용중인IP',
  `mac` char(17) DEFAULT NULL COMMENT 'MAC 주소',
  `realmac` char(17) DEFAULT NULL COMMENT '실제 MAC 주소 (삭제예정)',
  `dept` char(100) DEFAULT NULL COMMENT '부서',
  `name` char(100) DEFAULT NULL COMMENT '이름',
  `memo` char(200) DEFAULT NULL COMMENT '메모',
  `dtype` char(50) DEFAULT NULL COMMENT '장비구분',
  `bflag` tinyint(4) DEFAULT '0' COMMENT '차단여부',
  `sflag` tinyint(4) DEFAULT NULL COMMENT '보안점검 대상인지 여부',
  `bcode` char(20) DEFAULT NULL COMMENT '차단사유',
  `wireless` tinyint(4) DEFAULT '0' COMMENT '무선인지 여부',
  `natpubip` char(20) DEFAULT '' COMMENT 'NAT 공인 IP',
  `udate` datetime DEFAULT NULL COMMENT '업데이트 날짜',
  `idate` datetime DEFAULT NULL COMMENT '마지막 발견된 시간',
  `sec_chk_sflag` tinyint(4) DEFAULT '1' COMMENT '보안점검 프로그램 점검 대상',
  `sec_installed_flag` tinyint(4) DEFAULT '0' COMMENT 'DRM 설치여부',
  `sec_installed_time` datetime DEFAULT NULL COMMENT 'DRM 설치여부 조사시간',
  `sec_running_flag` tinyint(4) DEFAULT '0' COMMENT 'DRM 실행여부',
  `sec_running_time` datetime DEFAULT NULL COMMENT 'DRM 실행여부 조사시간',
  `use_the_ip` tinyint(4) DEFAULT '0' COMMENT '고정IP설정시 지정된 아이피만 사용',
  `checkStar` tinyint(4) DEFAULT '0' COMMENT '별표시',
  `ostype` char(50) DEFAULT '',
  `drm_time` datetime DEFAULT NULL,
  `drm_server` char(20) DEFAULT NULL,
  `drm_port` int(11) DEFAULT NULL,
  `point` char(100) DEFAULT '' COMMENT '보안점검 점수',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
);


alter table ipdb add column ds_usage_check tinyint default 0 comment '문서보안 사용여부 체크 0=모름 1=사용 2=미사용';
alter table ipdb add column ds_no_use_cause char(100) default '' comment '문서보안 미사용 사유';


