#!/usr/local/bin/php
<?php


  # 프로그램이 설치된 경로
  # command.cli.php 를 include 한다.
  $env['prefix'] = "/www/gate";
  include("$env[prefix]/inc/common.cli.php");

### {{{

// 디버그 함수
function _dd($msg) {
       if (is_string($msg)) print($msg);
  else if (is_array($msg)) { print("<pre>"); print_r($msg); print("</pre>"); }
  else print_r($msg);
}

function _process(&$lines, $mac, $ip) {
  global $conf;

  $qry = "SELECT * FROM ipdb WHERE realmac='$mac'";
  $ret = db_query($qry);
  $mc = @db_num_rows($ret); // 매치되는 row의 개수를 구함
  //print("$mc \n");

  // 2개이상 존재하면 중복
  if ($mc >= 2) {
    SaveLog("동일 맥주소 $realmac 가 DB에 2개이상 존재함");
    return;
  }

  // 1개 존재하면 정보를 가져온다.
  $row = db_fetch($ret);

  // 차단된 경우이면 통과
  if ($row['bflag']) return;

  //dd($row);
  //print("$row[name] $row[ip]\n");
  $lines[]=<<<EOS
iptables -t nat -A POSTROUTING -s $ip -o {$conf['externalNIC']} -j SNAT --to {$conf['externalIP']}
EOS;
}

function _get_row($ip) {
  $qry = "SELECT * FROM ipdb WHERE ipnow='$ip'";
  $ret = db_query($qry);
  $row = db_fetch($ret);
  return $row;
}
function _geoip_lookup($ip) {

  list($a, $b, $c, $d) = preg_split("/\./", $ip);
  $n = $a*256*256*256 + $b*256*256 + $c*256 + $d;

  $qry = "SELECT * FROM GeoIP WHERE ipnfrom<='$n' AND '$n'<=ipnto";
  $ret = db_query($qry);
  $row = db_fetch($ret);

  $nation = $row['nation'];
  return $nation;
}

function  _parse_iftop_txt($path) {
  $content = file_get_contents($path);
  //print $content;

  $list = preg_split("/\n/", $content);

  $info = array();

  $n = count($list);
  $ln = 0;
  for ($i = 0; $i < $n; $i++) {
    $ln++;
    $line = $list[$i];
    if ($ln < 6) continue; // start from line
    if ($ln > 25) continue; // end to line 
    //print("$ln $line<br>");

    $num = substr($line, 0,4); // column 0~4
    $num = trim($num);

    $line = substr($line, 5); // start from column 5
    
    $item = preg_split("/ +/", $line);
    //dd($item);
    $a = $item[0];
    $b = $item[1];
    $c = $item[2];
    $d = $item[3];
    $e = $item[4];
    $f = $item[5];
    list($ip, $port) = preg_split("/:/", $a);

    $nation = _geoip_lookup($ip);

    $row = _get_row($ip);
    //dd($row);

    $item = array();
    $item[] = $num;
    $item[] = $ip;
    $item[] = $port;
    $item[] = $nation;
    $item[] = $row['dept'];
    $item[] = $row['name'];
    $item[] = $b;
    $item[] = $c;
    $item[] = $d;
    $item[] = $e;
    $item[] = $f;

    $info[] = $item;
  } 
  return $info;
}



### }}}

  //print_r($argv);

  $path = "/www/gate/cron/iftop.txt";
  $info = _parse_iftop_txt($path);
  //dd($info);

  foreach ($info as $item) {
    print<<<EOS
{$item[0]} {$item[1]} {$item[2]} {$item[3]} {$item[4]} {$item[5]} {$item[6]} {$item[7]} {$item[8]} {$item[9]} {$item[10]} {$item[11]}

EOS;
  }

  exit;

?>
