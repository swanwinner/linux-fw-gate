<?php


// 차단 플래그 설정/해제
function set_bflag($mac, $bcode='') {
  $qry = "UPDATE ipdb SET bflag=1, bcode='$bcode' WHERE mac='$mac'";
  $ret = db_query($qry);
}

function unset_bflag($mac) {
  $qry = "UPDATE ipdb SET bflag=0, bcode='' WHERE mac='$mac'";
  $ret = db_query($qry);
}

// 보안점검 대상 체크
############################
# 보안점검 대상(sflag=1)인것 중에서, sec_chk_sflag=1 인것 중에서
#   허용 -> 차단으로 변경  (bflag=1, bcode=SEC_CHECK_FAIL)
#     o sec_installed_flag=0 이면 즉시 차단
#     o sec_installed_flag=1 and sec_running_flag=0 이면 sec_installed_time 으로 부터 limit 지났으면 차단
#     o sec_installed_flag=1 and sec_running_flag=1 이면 sec_running_time 으로 부터 limit 지났으면 차단
#   차단 -> 허용으로 변경 (bflag=1, bcode=SEC_CHECK_FAIL 인것 중에서)
#     o sec_installed_flag=1 and sec_running_flag=1 이고 sec_installed_time sec_running_time 가 최신이면 즉시 허용
############################
function decision_drm_check($debug=false, $debug_func='') {
  global $conf;

  $list_block = array();
  $list_open  = array();

  $qry = "SELECT i.*
, UNIX_TIMESTAMP(i.sec_installed_time) ts1
, UNIX_TIMESTAMP(i.sec_running_time) ts2
 FROM ipdb i
 WHERE sflag='1'
 AND sec_chk_sflag='1'";
  $ret = db_query($qry);

  $target_bcode = 'SEC_CHECK_FAIL';
  $limit = $conf['SEC_CHECK_TIME'];

  while ($row = db_fetch($ret)) {
    $cnt++;

    $now = time();
    $bflag = $row['bflag'];
    $bcode = $row['bcode'];
    $mac = $row['mac'];

    $installed_flag = $row['sec_installed_flag'];
    $installed_ts = $row['ts1'];
    $running_flag = $row['sec_running_flag'];
    $running_ts = $row['ts2'];

    // 허용 -> 차단
    if ($bflag == 0) {

      if ($debug) $debug_func("MAC=$mac INSTALLED=$installed_flag RUNNING=$running_flag");

      if ($installed_flag == 0) { // 설치 안됨
        $list_block[] = $mac;

      } else if ($installed_flag == 1 && $running_flag == 0) { // 설치했으나 실행안됨
        $diff = $now - $installed_ts;
        if ($diff > $limit) {
          if ($debug) $debug_func("MAC=$mac diff=$diff > limit=$limit BLOCK");
          $list_block[] = $mac;
        }

      } else if ($installed_flag == 1 && $running_flag == 1) { // 설치하고 실행됨
       #$diff = $now - $running_ts;
       #if ($diff > $limit) {
          if ($debug) $debug_func("MAC=$mac ALLOW");
          $list_block[] = $mac;
       #}

      }
    }

    // 차단 -> 허용
    if ($bflag == '1' && $bcode == $target_bcode) {
      if ($installed_flag == 1 && $running_flag == 1) {
        $d1 = $now - $installed_ts;
        $d2 = $now - $running_ts;
        if ($d1 < $limit && $d2 < $limit) {
          $list_open[] = $mac;
        }
      }
    }

  }
  return array($target_bcode, $list_block, $list_open);
}


// 차단된 항목 리스트를 처리함
function blocked_client_list($callback=null) {
  if (!$callback) return;

  $qry = "SELECT i.* FROM ipdb i WHERE bflag=1 ORDER BY ip3,ip4";
  $ret = db_query($qry);
  $cnt = 0;
  while ($row = db_fetch($ret)) {
    $cnt++;
    $callback($cnt, $row); // 콜백 호출
  }
}

// arp.txt 파일을 읽음
// $list = get_arp_statue_from_file();
function get_arp_statue_from_file() {
  global $conf;

  $file = $conf['arp_txt'];
  $content = file_get_contents($file);
  $list = preg_split("/\n/", $content);
  //$n = count($list);
  return $list;
}

// arp.txt 파일을 읽음
function read_arp_txt(&$arp) {
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


// 맥 주소를 구함
function get_mac_addr() {
  $ip = $_SERVER['REMOTE_ADDR'];
  $command = "/sbin/arp -n | grep '$ip '";
  $lastline = exec($command, $out, $retval);
  list($a,$b,$c,$d,$e) = preg_split("/ +/", $lastline);
  return $c;
}


// 맥주소로 테이블을 조회
function get_record_by_mac($mac) {
  $qry = "SELECT * FROM ipdb WHERE realmac='$mac'";
  $ret = db_query($qry);
  $row = db_fetch($ret);
 //dd($row);
  return $row;
}

// 해당 맥 주소를 차단 해제
function unblock_mac_address($mac) {
  $qry = "UPDATE ipdb SET bflag='0' WHERE mac='$mac'";
  $ret = db_query($qry);
}

// 해당 맥 주소를 차단
function block_mac_address($mac) {
  $qry = "UPDATE ipdb SET bflag='1' WHERE mac='$mac'";
  $ret = db_query($qry);
}


// 설정 테이블을 읽는다. --> $setting
// $setting = array();
// read_setting($setting);
function read_setting(&$setting) {
  $setting = array();
  $qry = "SELECT * FROM settings";
  $ret = db_query($qry);
  while ($row = db_fetch($ret)) {
    //print_r($row);
    $skey = $row['skey'];
    $svalue = $row['svalue'];
    $setting[$skey] = $svalue;
  }
  //dd($setting);
}


// 보안점검 완료후에 기록 로그에 남김
function security_check_done($mac) {

  $setting = array();
  read_setting($setting);

  // 보안점검 시작
  $security_check_start = $setting['security_check_start'];
  // 보안점검 시작 일자
  $security_check_date = $setting['security_check_date'];

  $s = array();
  $s[] = "start_date='$security_check_date'";
  $s[] = "confirm1='1'";
  $s[] = "time1=now()";
  $s[] = "mac='$mac'";
  $sql_set = " SET ".join(",", $s);

  $qry = "INSERT INTO security_check_log".$sql_set;
  $ret = db_query($qry);
}

// 보안점검 기록
// $info = security_check_log_info($mac);
function security_check_log_info($mac) {
  $qry = "SELECT * FROM security_check_log WHERE mac='$mac' ORDER BY time1";
  $ret = db_query($qry);

  $info = array();
  while ($row = db_fetch($ret)) {
    $info[] = $row;
  }
  return $info;
}



?>
