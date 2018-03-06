<?php

  include_once("./path.php");
  include_once("$env[prefix]/inc/common.login.php");

### {{{

// 맥 주소를 구함
function _get_mac_addr() {
  $ip = $_SERVER['REMOTE_ADDR'];

  $command = "/sbin/arp -n | grep '$ip '";
  $lastline = exec($command, $out, $retval);
  list($a,$b,$c,$d,$e) = preg_split("/ +/", $lastline);
  return $c;
}
### }}}
 
  // 설정 내용
  $setting = array();
  read_setting($setting);

  // 보안점검 시작
  $security_check_start = $setting['security_check_start'];
  // 보안점검 시작 일자
  $security_check_date = $setting['security_check_date'];

  // 접속 IP 주소와 MAC 주소
  $ip = $_SERVER['REMOTE_ADDR'];
  $mac = get_mac_addr();

  // 내부게이트로 접속했을때만
  $http_host = $_SERVER['HTTP_HOST'];
  $server_addr = $_SERVER['SERVER_ADDR'];

# // 보안점검
# if ($server_addr == $conf['security_check_gate']) {
# //print_r($conf);
#   include("secchk.php");
#   exit;
# }

  // 예를 들어 www.daum.net  으로 접속을 시도하여 iptables 규칙으로
  // internal gate 로 포워딩 되었을 경우 $http_host 값은
  // www.daum.net  이 되므로 내부 게이트 주소와 다르게 된다.
  // http://내부게이트주소/ 로 접속을 유도한다.
  if ($http_host != $conf['internalgate']) {
    //print_r($_SERVER);
    $h = $_SERVER['HTTP_HOST'];
    $u = $_SERVER['REQUEST_URI'];
    $internal_gate = $conf['internalgate'];
    $url = "http://$h$u";
    $urle = urlencode($url);
    print<<<EOS
잘못된 접근입니다.<br>
접속하신 주소: <a href="$url">$url</a><br>
<a href='http://$internal_gate/?url=$urle'>여기</a>를 클릭하세요.
<script>
function _redirect() {
  document.location = "http://$internal_gate/?url=$urle";
}
setTimeout("_redirect()", 3000);
</script>
EOS;
    exit;
  }

  // 맥주소로 테이블을 조회
  $sql_select = " SELECT i.*";
  $sql_from = " FROM ipdb i";
  $sql_join = "";
  $sql_where = " WHERE i.realmac='$mac'";

  // 보안점검 중이면 필요한 테이블을 조인
  if ($security_check_start == '1') {
    $sql_select .= ", s.confirm1";
    $sql_join .= " LEFT JOIN security_check_log s ON i.mac=s.mac and s.start_date='$security_check_date'";
  }

  $qry = $sql_select.$sql_from.$sql_join.$sql_where;
  $ret = db_query($qry);
  $row = db_fetch($ret);

  // 차단 정보를 조회
  $bflag = $row['bflag'];
  $bcode = $row['bcode'];
  $regip = $row['ip'];

  // 보안점검 대상(sflag) 이고, 보안점검시작(sec_chk_sflag) 이면
  if ($row['sflag'] && $row['sec_chk_sflag']) {
    // 보안점검 중이며, 아직 확인이 안됨
    if ($security_check_start == '1' && !$row['confirm1']) {
      Redirect("security_check_redirect.php?mac=$mac"); exit;
    }
  }

  // 차단은 되지 않았으나, 크론 프로그램 주기가 안되서 아직 열리지 않음
  if ($bflag == 0) {
    $qry = "SELECT updatetime FROM timeboard";
    $ret = db_query($qry);
    $row = db_fetch($ret);
    $updatetime = $row['updatetime']; 
    $now = date('Y-m-d H:i:s');
    print<<<EOS
<pre>
IP: $ip
MAC: $mac

아래 인터넷 접속 링크를 눌러보시기 바랍니다.

인터넷이 차단될 경우 최대 5분정도 기다리시면 인터넷에 연결됩니다.

문제가 계속되면 {$conf['contact_point']}로 연락바랍니다.

<a href='http://www.daum.net/'>인터넷 접속</a>
$updatetime (update)
$now (now)

</pre>
EOS;
    exit;
  }


###{{{
  $cause = $conf['bcode_list'][$bcode];
  $msg=<<<EOS
인터넷이 차단되었습니다. 코드: $bcode ($cause)<br>
EOS;

  if ($bflag == 1) $bflag_s = '차단됨'; else $bflag_s = '해제';

  if ($bcode == 'NEW_MAC_ADDR') {
    $msg .=<<<EOS
지금 사용하시는 컴퓨터는 등록되어 있지 않으므로 인터넷 사용을 제한합니다.<br>
등록하신 후 사용하시기 바랍니다.<br>
<br>
IP: $ip, MAC: $mac<br>
EOS;
    if ($bflag == 0) {
      $msg.=<<<EOS
<br>
5분 정도 기다리시면 인터넷 사용이 가능합니다.
<a href='http://www.daum.net/'>네이버 접속</a>
EOS;
    }

  // 등록된 IP와 달라서 차단된 경우
  } else if ($bcode == 'CHANGE_IP') {

    // $msg 변수를 설정해 주는 것이 목적
    $msg .=<<<EOS
현재 사용하시는 IP($ip)가 등록된 IP($regip)와 다름니다.<br>
(성전에서는 지정된 IP만 사용하셔야 합니다.)<br>
<font color=red>네트워크 설정에서 IP주소를 $regip 로 바꾸어 주십시오.</font><br>
위와 같이 IP를 변경하기 전까지 인터넷 접속은 제한됩니다.<br>
- IP를 변경하신 후 5분정도 기다리시면 차단 상태가 해제됩니다.<br>
- 문의사항은 {$conf['contact_point']}에 문의해 주세요.<br>
<br>
<div style='margin-top:5px; margin-bottom:5px; text-align:center; border:1px solid #fff;'>
<table border='2' style='border-collapse:collapse; align:center;' align='center'>
<tr>
 <td>IP 주소</td>
 <td>$ip<br></td>
</tr>
<tr>
 <td>MAC 주소</td>
 <td>$mac<br></td>
</tr>
<tr>
 <td>부서</td>
 <td>$row[dept]<br></td>
</tr>
<tr>
 <td>사용자</td>
 <td>$row[name]<br></td>
</tr>
<tr>
 <td>차단상태</td>
 <td>$bflag_s<br>
</td>
</tr>
<tr>
 <td>차단사유</td>
 <td>등록된 IP와 틀림<br></td>
</tr>
</table>
</div>
EOS;

  // 보안점검 차단
  } else if ($bcode == 'SECURITY_CHECK') {

    $msg .=<<<EOS
지금 사용하시는 컴퓨터는 보안점검을 실시하지 않아 인터넷 사용을 제한합니다.<br>
<br>
IP: $ip, MAC: $mac<br>
EOS;
    include("secchk_msg.php");

  // 보안점검 차단
  } else if ($bcode == 'SEC_CHECK_FAIL') {

    $msg .=<<<EOS
문서보안(DRM) 미설치 문제로 인터넷 사용을 제한합니다.<br>
정보통신부로 문의하시기 바랍니다.<br>
<br>
IP: $ip<br>
MAC: $mac<br>
EOS;
    include("secchk_doumi.php");


  // 그외 정보 없음이나. 기타
  } else {
    $msg .=<<<EOS
정보통신부로 문의하시기 바랍니다.<br>
<br>
IP: $ip<br>
MAC: $mac<br>
EOS;
  }
###}}}


  $url = $_GET['url'];

  print<<<EOS
<html>
<head>
<link rel="shortcut icon" href="/favicon.ico"/>
<title>인터넷 접속 제한</title>
<style type='text/css'>
body,input {  font-size: 12px; font-family: 굴림,돋움,verdana;
  font-style: normal; line-height: 12pt;
  text-decoration: none; color: #333333;
}
table,td,th { font-size: 12px; font-family: 돋움,verdana; white-space: nowrap; }
</style>
</head>
<body>
<br>
<div style='text-align:center;'>
$msg<br>
EOS;
  if ($url) print ("<a href='$url'>$url</a>로 다시 연결");
  print<<<EOS
</div>
</body>
</html>
EOS;

  exit;

?>
