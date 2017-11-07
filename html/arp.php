<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");

  $title = 'ARP 캐시 조회';
  AdminPageHead($title);
  ParagraphTitle($title);

  print<<<EOS
<style>
table.main { border:0px solid red; border-collapse:collapse; }
table.main th, table.main td { border:1px solid #999; padding:1px 5px 1px 5px; }
table.main th { background-color:#eeeeee; font-weight:bold; text-align:center; }
table.main td.c { text-align:center; }
table.main td.r { text-align:right; }

table.main td.a { text-align:right; font-family:돋움체; text-align:left; }
pre.line { background-color:white; margin:0 0 0 0; }
</style>
EOS;


  print<<<EOS
<pre>
- 매 1분 마다 갱신됨
- MAC주소가 중복된 것(빨간색)으로 체크
- 컴퓨터가 꺼져 있거나, 랜선이 빠져 있거나, 켜져 있지만 오래동안 인터넷 사용을 하지 않을 경우 나타나지 않음
- 가끔씩 동일한 IP와 MAC으로 2개 이상 나타나 보이는 증상이 있지만, 정상적임
</pre>
EOS;

  ParagraphTitle('ARP 캐시 테이블', 1);
  print<<<EOS
<table class='main'>
<tr>
<th>번호</th>
<th>IP</th>
<th>MAC</th>
<th>제조사</th>
<th>차단여부</th>
<th>IP검색</th>
<th>MAC검색</th>
<th>부서</th>
<th>이름</th>
<th>IP구분</th>
<th>장비분류</th>
</tr>
EOS;

  $list = get_arp_statue_from_file();
  $n = count($list);

  $pattern = "/[0-9A-Fa-f][0-9A-Fa-f]:[0-9A-Fa-f][0-9A-Fa-f]:[0-9A-Fa-f][0-9A-Fa-f]:/";

  // 중복된 ip, mac 이 있는지 채크한다.
  $dup_mac_list = array();
  $dup_ip_list = array();
  $mac_list = array();
  $ip_list = array();
  for ($i = 0; $i < $n; $i++) {
    $line = $list[$i];
    if (preg_match($pattern, $line)) {
      list($a,$b,$c,$d,$e) = preg_split("/ +/", $line);
      //print("$a,$b,$c,$d,$e\n");

      $ip = $a;
      $mac = $c;
      //print("$ip , $mac<br>");

      if (in_array($mac, $mac_list)) {
        $dup_mac_list[] = $mac;
      }
      $mac_list[] = $mac;

      if (in_array($ip, $ip_list)) {
        $dup_ip_list[] = $ip;
      }
      $ip_list[] = $ip;
    }
  }
  //print_r($dup_mac_list);
  //print_r($dup_ip_list);
  //print_r($ip_list);


  $cnt = 0;
  for ($i = 0; $i < $n; $i++) {
    $line = $list[$i];
    if (preg_match($pattern, $line)) {
      $cnt++;

      list($a,$b,$c,$d,$e) = preg_split("/ +/", $line);
      //print("$a,$b,$c,$d,$e\n");

      $ip = $a;
      $mac = $c;

      // ip 로 검색
      $qry1 = "SELECT * FROM ipdb WHERE ip='$ip'";
      $ret1 = db_query($qry1);
      $mc1 = db_affected_rows($ret1);
      $row1 = db_fetch($ret1);

      // mac 으로 검색
      $qry2 = "SELECT oui.company, a.*"
       ." FROM (SELECT u.*, CONCAT(SUBSTRING(u.mac,1,2),'-',SUBSTRING(u.mac,4,2),'-',SUBSTRING(u.mac,7,2)) AS mac6 "
       ." FROM ipdb u WHERE u.mac='$mac') a"
       ." LEFT JOIN oui ON a.mac6=oui.mac6";

      $ret2 = db_query($qry2);
      $mc2 = db_affected_rows($ret2);
      $row2 = db_fetch($ret2);

      $row = array();

      // IP검색, MAC 모두 1개이면
      if ($mc1 == 1 and $mc2 == 1) {
        $star = '*';
        $row = $row2;

      // MAC 검색 1개이면
      } else if ($mc2 == 1) {
        $row = $row2;

      // IP검색 1개이면
      } else if ($mc1 == 1) {
        $row = $row1;

      // 두 개이상 존재하면
      } else if ($mc1 >= 2 or $mc2 >= 2) {
        $dup = "<font color='red'>(중복)</font>";
        $row = $row1;

      // 존재하지 않으면
      } else if ($mc1 == 0 and $mc2 == 0) {
      }

      $mac_s = $mac;
      $ip_s = $ip;

      $bflag = $row['bflag'];
      if ($bflag) $bflag_s = '차단'; else $bflag_s = '-';

      $dup = '';
      if (in_array($mac, $dup_mac_list)) {
        $dup .= "<font color='red'>(MAC중복)</font>";
        $mac_s = "<font color='red'>$mac</font>";
      }
      if (in_array($ip, $dup_ip_list)) {
        $dup .= "<font color='red'>(IP중복)</font>";
        $ip_s = "<font color='red'>$ip</font>";
      }

      if ($row['staticip']) $str = "고정";
      else $str = "유동";
      $staticip_s = $str;


      $company = $row['company'];
      $mac6 = preg_replace("/:/", "", substr($mac, 0, 8));
      $company=<<<EOS
<a href='http://standards.ieee.org/cgi-bin/ouisearch?$mac6'>$mac6</a>
<a href='http://aruljohn.com/mac/$mac6'>$mac6</a>
$company
EOS;

      print<<<EOS
<tr>
<td class='a'>$cnt</td>
<td class='a'>$ip_s</td>
<td class='a'>$mac_s</td>
<td class='a'>$company</td>
<td class='c'>$bflag_s</td>
<td class='c'><a href="home.php?mode=search&ip=$ip&ipmatch=1">($mc1)</a></td>
<td class='c'><a href="home.php?mode=search&mac=$mac">($mc2)</a></td>
<td>$row[dept]$dup</td>
<td>$row[name]$dup</td>
<td class='c'>$staticip_s</td>
<td class='c'>$row[dtype]</td>
</tr>
EOS;
    }
  }
 
  print<<<EOS
</table>
EOS;

  AdminPageTail();
  exit;

?>