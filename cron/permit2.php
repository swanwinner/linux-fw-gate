#!/usr/local/bin/php
<?php



  # 프로그램이 설치된 경로
  # command.cli.php 를 include 한다.
  $env['prefix'] = "/www/gate";
  include("$env[prefix]/inc/common.cli.php");
  include("$env[prefix]/cron/func.php");

### {{{
function _process(&$lines, $mac, $ip) {
  global $conf;

  $qry = "SELECT * FROM ipdb WHERE realmac='$mac'";
  //dd($qry."\n");
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

  dd("$row[name] $row[ip] {$row['bflag']}\n");

  $lines[]=<<<EOS
iptables -t nat -A POSTROUTING -s $ip -o {$conf['externalNIC']} -j SNAT --to {$conf['externalIP']}
EOS;
}

### }}}


  $setting = array();
  read_setting($setting);
  //dd($setting);

  $arp = array();
  read_arp_txt($arp);
  //dd($arp);

  $lastupdate = $arp['lastupdate'];

  $lines = array();

  $cnt = 0;
  foreach ($arp['data'] as $item) {
    $cnt++;

    //dd($item);
    list($ip, $mac) = $item;
    //dd("$ip, $mac\n");

    $qry = "SELECT * FROM ipdb WHERE realmac='$mac'";
    //dd($qry."\n");
    $ret = db_query($qry);
    $mc = @db_num_rows($ret); // 매치되는 row의 개수를 구함
    //print("$mc \n");

    // 2개이상 존재하면 중복
    if ($mc >= 2) {
      SaveLog("동일 맥주소 $realmac 가 DB에 2개이상 존재함");
      continue;
    }

  // 1개 존재하면 정보를 가져온다.
  $row = db_fetch($ret);
  //dd($row);
  //dd("$cnt $mc $row[name] $row[ip] {$row['bflag']}\n");

  // 차단된 경우이면 통과
  if ($row['bflag']) continue;

  //dd("$row[name] $row[ip] {$row['bflag']}\n");

  $lines[]=<<<EOS
iptables -t nat -A POSTROUTING -s $ip -o {$conf['externalNIC']} -j SNAT --to {$conf['externalIP']}
EOS;

  }
  //dd($lines);

  $file = join("\n", $lines);
  $path = "/www/gate/rule/permit.sh";
  file_put_contents($path, $file);
  SaveLog("permit.sh ");

  exit;

?>
