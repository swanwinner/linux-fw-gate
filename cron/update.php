#!/usr/local/bin/php
<?php

  $env['prefix'] = "/www/gate";
  include("$env[prefix]/inc/common.cli.php");
  include("$env[prefix]/cron/func.php");

### {{{
// 디버그 함수
function _dd($msg) {
       if (is_string($msg)) print($msg."\n");
  else if (is_array($msg)) { print("---\n"); print_r($msg); print("---\n"); }
  else print_r($msg);
}
### }}}

  $debug = false;

  $a1 = $argv[1];
  if ($a1 == 'debug') $debug = true;
  
  // 실행 시간을 기록
  $qry = "UPDATE timeboard SET updatetime=NOW()";
  $ret = db_query($qry);

  $setting = array();
  read_setting($setting);
  //dd($setting);

  $arp = array();
  read_arp_txt($arp);
  //dd($arp);
  # foreach ($arp['data'] as $item) {
  #   list($ip, $mac) = $item;
  # }

  $lastupdate = $arp['lastupdate'];

  $mac2ip = array();
  $ip2mac = array();
  $msg_arr = array();
  $row_arr = array();
  $new_mac = array();

  $n = count($list);
  foreach ($arp['data'] as $item) {
    //dd($item);

    list($ip, $mac) = $item;

      // 캐시안에서 서로 중복되는 것이 있는가?
      if ($mac2ip[$mac]) {
        $msg_arr[$mac] .= "(캐시MAC중복)";
        SaveLog("ARP 캐시에서 맥주소 중복됨 MAC:$mac");
        continue;
      }

      $mac2ip[$mac] = $ip;
      $ip2mac[$ip] = $mac;

      // 해당맥주소가 테이블에 존재하는가?
      $qry = "SELECT * FROM ipdb WHERE realmac='$mac'";
      $ret = db_query($qry);
      $mc = @db_num_rows($ret); // 매치되는 row의 개수를 구함

      // 2개이상 존재하면 중복
      if ($mc >= 2) {
        $msg_arr[$mac] .= "(등록MAC중복)";
        SaveLog("동일 맥주소 $realmac 가 DB에 2개이상 존재함");
      }

      // 1개 존재하면 정보를 가져온다.
      $row = db_fetch($ret);
      $row_arr[$mac] = $row;

      // 정보를 가져올 수 없으면 신규 mac 주소임
      if (!$row) $new_mac[] = $mac;
  }

  //_dd($mac2ip);
  //_dd($ip2mac);
  //_dd($msg_arr);
  //_dd($row_arr);
  //_dd($new_mac);


  // 신규 발견된 맥주소 처리
  ### {{{
  for ($i = 0; $i < count($new_mac); $i++) {
    $mac = $new_mac[$i];
    $ip = $mac2ip[$mac];

    # 처음 발견된 MAC 일 경우 설정에 따름
    if ($setting['block_new_mac_address']) {
      $bflag = 1; // 차단함
      SaveLog("신규 맥주소 발견 MAC:$mac IP:$ip (정책설정:차단)");
    } else {
      $bflag = 0;  // 차단안함
      SaveLog("신규 맥주소 발견 MAC:$mac IP:$ip (정책설정:허용)");
    }

    list($ip1,$ip2,$ip3,$ip4) = explode('.',$ip);
    $qry = "INSERT INTO ipdb SET ip='$ip',ip3='$ip3',ip4='$ip4'"
       .",mac='$mac'"
       .",realmac='$mac'"
       .",name='???',dept='???'"
       .",bflag='$bflag',bcode='NEW_MAC_ADDR'"
       .",sflag='0'"
       .",staticip='0'"
       .",idate=now()";
    $ret = db_query($qry);
  }
  ### }}}

function _changed_ip_block($mac, $registered_ip, $ip, $row) {

  # // 정책이 적용안됐으면 스킵
  # global $conf;
  # if (!$conf['block_changed_static_ip']) return;

  if ($row['use_the_ip']) {
    $msg = "고정IP 변경됨 정책:차단, MAC:$mac, 등록IP:$registered_ip, 실제IP:$ip";
    _dd($msg);
    SaveLog($msg);
    $qry = "UPDATE ipdb SET bflag=1,bcode='CHANGE_IP' WHERE realmac='$mac'";
    $ret = db_query($qry);
  }
}

function _changed_ip_permit($mac, $registered_ip, $ip, $row) {
  $msg = "IP변경 차단해제, MAC:$mac, 등록IP:$registered_ip, 실제IP:$ip";
  _dd($msg);
  SaveLog($msg);
  $qry = "UPDATE ipdb SET bflag=0,bcode='' WHERE realmac='$mac'";
  $ret = db_query($qry);
}


  $maclist = array_keys($mac2ip);
  $n = count($maclist);
  for ($i = 0; $i < $n; $i++) {
    $mac = $maclist[$i];
    $ip = $mac2ip[$mac];
    $row = $row_arr[$mac];
    $registered_ip = $row['ip'];
    if ($row['staticip']) {
      // 등록되지 않은 IP를 사용중이면 (IP를 임의로 바꿔서 사용하는 경우)
      // 고정IP인 경우만 해당
      if ($registered_ip != $ip) {
        //_dd("고정 IP 사용자 등록된 아이피와 다름, {$mac}, $ip, $registered_ip");
        _changed_ip_block($mac, $registered_ip, $ip, $row);
      }

      // 고정 IP 가 등록되지 않은 IP로 변경 사용해서 차단된 경우
      // 다시 원래의 IP로 변경한 경우 차단을 해제해 줌
      if ($registered_ip == $ip and $row['bflag']==1 and $row['bcode']=='CHANGE_IP') {
        _changed_ip_permit($mac, $registered_ip, $ip, $row);
      }

    }

  }

  // 마지막으로 발견된 시각을 업데이트
  // 유동IP인 경우, 변경된 유동 IP까지 변경
  $maclist = array_keys($mac2ip);
  $n = count($maclist);
  for ($i = 0; $i < $n; $i++) {
    $mac = $maclist[$i];
    $ip = $mac2ip[$mac];
    $row = $row_arr[$mac];

    // 2014.7.3 ipnow 필드를 추가하여 항상 업데이트함
    if ($row['staticip']) { // 고정 IP
      $qry = "UPDATE ipdb SET udate='$lastupdate', ipnow='$ip' WHERE mac='$mac'";
      $ret = db_query($qry);

    } else { // 유동IP 는 ip와 ipnow 를 둘다 업데이트
      list($ip1,$ip2,$ip3,$ip4) = explode('.',$ip);
      $qry = "UPDATE ipdb SET udate='$lastupdate',ip='$ip',ip3='$ip3',ip4='$ip4', ipnow='$ip'"
            ." WHERE mac='$mac'";
      $ret = db_query($qry);
    }

  }
  //PrintLog("total $cnt lines");


  //dd($list_block);
  //dd($list_open);
  if ($list_block) {
    foreach ($list_block as $mac) {
      SaveLog("DRM 차단 $mac bcode:$target_bcode");
      set_bflag($mac, $target_bcode);
    }
  }
  if ($list_open) {
    foreach ($list_open as $mac) {
      SaveLog("DRM 차단허용 $mac");
      unset_bflag($mac);
    }
  }


############################
# 차단된 맥주소를 뽑아서 block.sh 에 기록한다.
# 정책은 PREROUTING 체인대상, tcp 만 해당, 내부 랜카드로 들어오는 것, 해당 맥주소에 대하여
# DNAT 규칙 내부 랜카드 주소로 주소 변경
# 보안점검 대상(sflag=1)인것 중에서
#  1) 차단된 상태(bflag=1)이면 차단
############################

  # {{{
  $qry = "SELECT i.* FROM ipdb i WHERE bflag=1";
  $ret = db_query($qry);

  $cnt = 0;
  $macs = array();
  while ($row = db_fetch($ret)) {
    $cnt++;

    $mac = $row['mac'];

    // MAC 주소가 길이가 안 맞는 경우가 있어 iptables 에러 방지를 위해 길이를 체크함
    $len = strlen($mac);
    if ($len != 17) continue;

    $macs[$mac] = true;
  }
  if ($cnt > 0) SaveLog("맥주소차단 현재 {$cnt}개");


/*
  $qry = "SELECT i.*, s.confirm1
 FROM ipdb i
 left join secchk3212 s on i.mac=s.mac
 WHERE i.sflag=1" // 보안점검 대상이고
  ." and i.sec_chk_sflag=1" // DRM 보안 점검 대상임
  ." and (s.confirm1 is null or s.confirm1=0)" // 아직 공지 확인을 하지 않음
  ;
  $ret = db_query($qry);
  while ($row = db_fetch($ret)) {
    //print_r($row);
    $mac = $row['mac'];
    $macs[$mac] = true;
  }
*/

  // block.sh 파일을 기록
  $file = '';
  foreach ($macs as $mac=>$tmp) {
    $cmd = "/sbin/iptables -t nat -A PREROUTING  -p tcp -i $conf[internalNIC]"
        ." -m mac --mac-source $mac -j DNAT --to-destination $conf[internalgate]";
    $file .= "$cmd \n";
  }
  $bfile = "/www/gate/rule/block.sh";
  file_put_contents($bfile, $file);

  # }}}


  // 외부 공인 IP 가 지정된 내부 아이피에 대해 macip.sh 를 작성
  # {{{
  $qry = "SELECT i.* FROM ipdb i WHERE natpubip != ''";
  $ret = db_query($qry);

  $cnt = 0;
  $file = '';
  while ($row = db_fetch($ret)) {
    $cnt++;

    $realmac = $row['realmac'];
    $natpubip = $row['natpubip'];

    $cmd = "/sbin/iptables -t nat -A POSTROUTING  -m mac  --mac-source $realmac"
         ." -o $conf[externalNIC] -j SNAT --to  $natpubip";

    $file .= "$cmd \n";
#   print("$cmd<br>");
  }

  //if ($cnt > 0) SaveLog("맥주소차단 현재 {$cnt}개");

  $bfile = "/www/gate/rule/macip.sh";
  file_put_contents($bfile, $file);
  # }}}


  exit;

?>
