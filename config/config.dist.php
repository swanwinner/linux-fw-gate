<?php

  # database connection
  $conf['dbhost']   = "localhost";
  $conf['dbuser']   = "root";
  $conf['dbpasswd'] = "************";
  $conf['dbname']   = "ipdb";

  $conf['head_title'] = 'IP Manager';

  $conf['externalNIC']   = "eth0";
  $conf['internalNIC']   = "eth1";
  $conf['internalgate']   = "192.168.0.254";

  $conf['rrd_dir_path'] = "/www/gate/rrd";
  $conf['rrd_img_path'] = "/www/gate/html/rrdimg";
  $conf['rrd_img_url'] = "/rrdimg";

  $conf['surun_path'] = "/www/gate/com/surun";
  $conf['cron_path'] = "/www/gate/cron/cron.sh";
  $conf['arp_txt'] = "/www/gate/cron/arp.txt";

  $conf['net_class'] = '192.168';

  $conf['show_arp_graph'] = true;

  $conf['dtype'] = array('데스크톱','노트북','서버','무선공유기','프린터','IP카메라','전화기','스마트폰','지문인증기');

  $conf['subnets'] = array('0','100');
  $conf['subnet_title'] = array(
    0 => '업무망',
    100 => 'DHCP',
  );

  $conf['developer_pass'] = '930ccd6232ec9a1990e9043b691f1698';
  $conf['contact_point'] = '전산관리자';

  $conf['html_notice1'] =<<<EOS
EOS;

  $conf['html_health1'] =<<<EOS
안내내용
EOS;

  $conf['menu_html'] =<<<EOS
<a href='/home.php'>IP등록 홈</a>
:: <a href='block.php'>차단적용</a>
:: <a href='arp.php'>ARP캐시</a>
:: <a href='dept.php'>부서등록</a>
:: <a href='/health.php'>Health</a>
:: <a href='/changepw.php'>비밀번호변경</a>
:: <a href='/log.php'>로그</a>
:: <a href='/setting.php'>설정</a>
:: <a href='/help.php'>도움말</a>
:: <a href='/logout.php'>로그아웃</a>
<hr size='1'>
EOS;

?>
