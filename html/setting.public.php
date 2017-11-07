<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");


### {{{

function _error($msg) {
  print<<<EOS
<p style='font-size:large; font-weight:bold;'>
에러: $msg
</p>
EOS;
  exit;
}

### }}}


  $title = '설정 변경 - 공인 IP 설정';
  AdminPageHead($title);
  ParagraphTitle($title);


if ($mode == 'doadd' or $mode == 'dodel') {
  //print_r($form);
  //exit;

  $ipaddr = $form['ip'];

  $enic = $conf['externalNIC'];

  $valid = preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $ipaddr);
  if (!$valid) {
    _error("유효한 IP 주소가 아닙니다.");
  }

  $surun = $conf['surun_path'];

  if ($mode == 'doadd') {
    $command = "$surun root \"/sbin/ip addr add  $ipaddr  dev $enic\" ";
    $successmsg = "IP가 추가되었습니다.";
  } else if ($mode == 'dodel') {
    $command = "$surun root \"/sbin/ip addr del  $ipaddr  dev $enic\" ";
    $successmsg = "IP가 삭제되었습니다.";
  }
  //print $command;

  $lastline = exec($command, $out, $retval);
  //print_r($out);
  //print ("retval = $retval");
  //print ("lastline = $lastline");

  if ($retval) _error("ip addr return $retval");
  if (!$retval) {
    print($successmsg);
  }
  exit;
}


  ParagraphTitle('외부 랜카드 정보', 1);
# print("<pre>");
# print_r($_SERVER);
# print("</pre>");
  $server_addr = $_SERVER['SERVER_ADDR'];
  print<<<EOS
웹서버 IP: $server_addr<br>

<form name='form' action='$env[self]' style='margin:0 0 0 0px;'>
IP: <input type='text' name='ip' size='20'>
<input type='hidden' name='mode' value='doadd'>
<input type='submit' value='추가'>
</form>

<form name='form' action='$env[self]' style='margin:0 0 0 0px;'>
IP: <input type='text' name='ip' size='20'>
<input type='hidden' name='mode' value='dodel'>
<input type='submit' value='삭제'>
</form>
EOS;
  
function get_ipaddresses($out) {
  $html = '';
  $len = count($out);
  for ($i = 0; $i < $len; $i++) {
    $line = $out[$i];

    $matches = array();
    if (preg_match("/inet ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)\/(\d+)/", $line, $matches)) {
      //print_r($matches);
      $ip = $matches[1];
      $mask  = $matches[2];
      print("$ip $mask<br>");
    }
  }
  print $html;
}


  $command = "/sbin/ip addr show dev {$conf['externalNIC']}";

  $lastline = exec($command, $out, $retval);
  //print_r($out);
  get_ipaddresses($out);


  $html = '';
  $len = count($out);
  for ($i = 0; $i < $len; $i++) {
    $line = $out[$i];

    $line = preg_replace("/ /", '&nbsp;', $line);

    $html .= $line."<br>\n";
  }

  print<<<EOS
<p style='font-family:fixedsys, consolas, monospace;'>
$html
</p>
EOS;


  AdminPageTail();
  exit;

?>
