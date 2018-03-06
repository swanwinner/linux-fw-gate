<?php

  unset($env);
  $env['prefix'] = "/www/gate";
  include("$env[prefix]/inc/common.login.php");

### {{{ functions
// 맥 주소를 구함
function get_mac_addr() {
  $ip = $_SERVER['REMOTE_ADDR'];

  $command = "/sbin/arp | grep '$ip '";
  $lastline = exec($command, $out, $retval);
# print("<pre>");
# print $lastline;
# print("</pre>");
  list($a,$b,$c,$d,$e) = preg_split("/ +/", $lastline);
# print("$a,$b,$c,$d,$e");
  return $c;
}
### }}} functions


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
EOS;
    exit;
  }
  //print_r($_SERVER);

  // 맥주소로 테이블을 조회
  $qry = "SELECT * FROM ipdb WHERE realmac='$mac'";
  $ret = mysql_query($qry);
  $row = mysql_fetch_array($ret);


  // 차단 정보를 조회
  $bflag = $row['bflag'];
  $bcode = $row['bcode'];
  $regip = $row['ip'];

  if ($bflag == 1) $bflag_s = '차단됨';
  else $bflag_s = '해제';


  if ($bcode == 'NEW_MAC_ADDR') {
    $msg=<<<EOS
지금 사용하시는 컴퓨터는 정보통신부에 등록되어 있지 않으므로 인터넷 사용을 제한합니다.<br>
정보통신부에 등록하신 후 사용하시기 바랍니다.<br>
<br>
IP: $ip, MAC: $mac<br>
EOS;
    if ($bflag == 0) {
      $msg.=<<<EOS
<br>
5분 정도 기다리시면 인터넷 사용이 가능합니다.
<a href='http://www.daum.net/'>인터넷 접속</a>
EOS;
    }

  // 등록된 IP와 달라서 차단된 경우
  } else if ($bcode == 'CHANGE_IP') {
    include("inc.changeip.php");

  // 그외 정보 없음이나. 기타
  } else {
    $msg =<<<EOS
지금 사용하시는 컴퓨터는 정보통신부에 등록되어 있지 않으므로 인터넷 사용을 제한합니다.<br>
정보통신부(2층)에 등록하신 후 사용하시기 바랍니다.<br>
<br>
IP: $ip, MAC: $mac<br>
EOS;
  }

# //print_r($_SERVER);
# $hh = $_SERVER['HTTP_HOST'];
# $qu = $_SERVER['REQUEST_URI'];
# $url = "http://$hh$qu";

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
<center>
$msg
<br>
<a href='$url'>$url</a>로 다시 연결
<center>
</body>
</html>
EOS;

  exit;


?>
