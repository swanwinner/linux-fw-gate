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



### }}}


  // 설정 테이블을 읽는다. --> $setting
  $setting = array();
  $qry = "SELECT * FROM settings";
  $ret = db_query($qry);
  while ($row = db_fetch($ret)) {
    //print_r($row);
    $skey = $row['skey'];
    $svalue = $row['svalue'];
    $setting[$skey] = $svalue;
  }
  //print_r($setting);


  # arp.txt 파일을 읽는다.
  $file = $conf['arp_txt'];
  $content = file_get_contents($file);
  //print $content;
  # 줄바꿈 (\n) 을 기준으로 나눈다.
  $list = preg_split("/\n/", $content);
  //print_r($list);

/* arp.txt 예
UPDATE @2011-04-22 23:15:01
Fri Apr 22 23:15:01 KST 2011
/sbin/arp -i eth1
Address                  HWtype  HWaddress           Flags Mask            Iface
192.168.100.224          ether   00:26:18:7D:D4:1B   C                     eth1
192.168.100.115          ether   00:03:2A:0F:BB:77   C                     eth1
192.168.100.182          ether   00:30:67:8D:10:17   C                     eth1
192.168.100.148                  (incomplete)                              eth1
192.168.100.209          ether   00:26:E8:CC:46:9F   C                     eth1
192.168.100.145          ether   00:16:E6:DD:62:E5   C                     eth1
*/


  // 첫번째 라인에는 파일이 생성된 시간이 들어 있음
  list($a, $b) = preg_split("/@/", $list[0]);
  # @ 를 기준으로 분리시킨 뒤부분 (lastupdate 시간 정보)
  $lastupdate = $b;
  //print("lastupdate=$lastupdate<br>");

  $lines = array();

  $n = count($list);
  for ($i = 0; $i < $n; $i++) { // arp.txt 모든 라인에 대해서 반복
    $line = $list[$i]; // 각 라인에 대해서

    # arp 실행 결과에서
    # 0-9까지숫자 A-F 영문자로 구성된 패턴 [0-9A-F][0-9A-F]
    # 이 패턴 3개가 :으로 연결된 패턴이 발견되면 처리한다.
    # 패턴이 맞지 않으면 처리하지 않는다. 
    if (preg_match("/[0-9A-Fa-f][0-9A-Fa-f]:[0-9A-Fa-f][0-9A-Fa-f]:[0-9A-Fa-f][0-9A-Fa-f]:/", $line)) {

      # 라인을 공백 여러개 " +" 를 기준으로 분리시킨다.
      list($a,$b,$c,$d,$e) = preg_split("/ +/", $line);
      //print("$a,$b,$c,$d,$e\n");

      $mac = $c; // 세번째 칼럼이 mac 주소
      $ip = $a; // 첫번째 칼럼이 ip 주소임
      //print("$ip, $mac\n");

      _process($lines, $mac, $ip);
    }
  }
  //dd($lines);

  $file = join("\n", $lines);
  $path = "/www/gate/rule/permit.sh";
  file_put_contents($path, $file);
  SaveLog("permit.sh ");

  exit;

?>
