<?php


function _read_setting(&$setting) {
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
}


function _read_arp_txt(&$arp) {
  global $conf;

  # arp.txt 파일을 읽는다.
  $file = $conf['arp_txt'];
  $content = file_get_contents($file);

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
  //dd("lastupdate=$lastupdate \n");

  $arp = array();
  $arp['lastupdate'] = $lastupdate;
  $arp['data'] = array();

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
      //dd("$a,$b,$c,$d,$e\n");

      $mac = $c; // 세번째 칼럼이 mac 주소
      $ip = $a; // 첫번째 칼럼이 ip 주소임
      //dd("$ip, $mac\n");
      $arp['data'][] = array($ip, $mac);
    }
  }
}


?>
