<?php


# display an error message and terminate program
function iError($msg, $go_back=1, $win_close=0, $exit=1) {
  //$msg = preg_replace("/\n/", "\\n", $msg);
  print("<script>\n");
  print("alert(\"$msg\");\n");
  if ($go_back) print("history.go(-1);\n");
  if ($win_close) print("window.close();");
  print("</script>\n");
  if ($exit) exit;
}

# fatal error (프로그램 오류, 일어나지 말아야 할 에러)
function fError($msg) {
  print<<<EOS
<script>
alert("시스템오류: $msg");
</script>
EOS;
  exit;
}

function cliError($msg) {
  print("cliError: $msg\n");
  exit;
}

# 팝업창에 표시한다.
function pError($msg) {
  PopupPageHead('오류');
  print<<<EOS
<table width='100%' height='100%'>
<tr><td valign='middle'>
<font color='red'>$msg</font>
</td></tr></table>
EOS;
  PopupPageTail();
  exit;
}

function Redirect($url, $http=true) {
  # HTTP 헤더가 이미 전송되었으면 자바스크립트 방식을 이용해야함
  if (headers_sent()) $http=false;
  if ($http) {
    header("Location: $url");
    exit;
  } else {
    print("<script>\n");
    print("window.location='$url';\n");
    print("</script>\n");
    exit;
  }
}

# display an error message and redirect to url
function ErrorRedir($msg, $url) {
  $msg = ereg_replace("\n", "\\n", $msg);
  print("<script>\n");
  print("alert(\"$msg\");\n");
  print("window.location='$url';\n");
  print("</script>\n");
  exit;
}
function InformRedir($msg, $url) {
  ErrorRedir($msg, $url);
}
function InformAndCloseWindow($msg) {
  print<<<EOS
<script>
alert("$msg");
window.close();
</script>
EOS;
}
function CloseAndChangeParentWindow($url) {
  print<<<EOS
<script>
window.opener.document.location = "$url";
window.close();
</script>
EOS;
}
function CloseAndReloadParentWindow() {
  print<<<EOS
<script>
window.opener.document.location.reload();
window.close();
</script>
EOS;
}

function Pager($url, $page, $total, $ipp) {
  global $conf, $env;
# print("$url $page $total<br>");

  $lang['prev'] = "<img src='/img/calendar/l.gif' border=0 width=11 height=11>";
  $lang['next'] = "<img src='/img/calendar/r.gif' border=0 width=11 height=11>";

  $lang['prev2'] = "<img src='/img/calendar/l2.gif' border=0 width=11 height=11>";
  $lang['next2'] = "<img src='/img/calendar/r2.gif' border=0 width=11 height=11>";

  $last = ceil($total/$ipp);
  if ($last == 0) $last = 1;

  $start = floor(($page - 1) / 10) * 10 + 1;
  $end = $start + 9;
# print("$start $end $last<br>");

  print("<table border='0' cellpadding='2' cellspacing='0'><tr><td>"); # table 1

  # previous link
  if ($start > 1) {
    $prevpage = $start - 1;
    print("<a href='$url&page=$prevpage'>$lang[prev2]</a>");
  } else print("$lang[prev2]");
  print("</td><td width='5'/><td>"); # table 1
  if ($page > 1) {
    $prevpage = $page - 1;
    print("<a href='$url&page=$prevpage'>$lang[prev]</a>");
  } else print("$lang[prev]");


  if ($end > $last) $end = $last;
# print("</td><td>"); # table 1
# print("|");
  $attr = " onmouseover=\"this.style.background='#eeeeee'\""
         ." onmouseout=\"this.style.background='#ffffff'\""
         ." width='20' align='center' style='cursor:hand;'";
  print("</td>"); # table 1
  for ($i = $start; $i <= $end; $i++) {
#   if ($i < 10) $s = "$i";
#   else if ($i < 100) $s = "$i";
#   else $s = "$i";
    $s = "$i";
    if ($i != $page) {
      print("<td$attr onclick=\"document.location='$url&page=$i'\">$s</td>");
    } else {
      print("<td$attr><font color='darkorange'><b>$s</b></font></td>");
    }
#   print("</td><td>"); # table 1
#   print("|");
#   print("<td$attr>"); # table 1
  }

  print("<td>"); # table 1
  # next link
  if ($page < $last) {
    $nextpage = $page + 1;
    print("<a href='$url&page=$nextpage'>$lang[next]</a>");
  } else print("$lang[next]");
  print("</td><td width='5'/><td>"); # table 1
  if ($end < $last) {
    $nextpage = $end + 1;
    print(" <a href='$url&page=$nextpage'>$lang[next2]</a>");
  } else print(" $lang[next2]");

  print("</td></tr></table>"); # table 1
}


function Pager_s($url, $page, $total, $ipp) {
  global $conf, $env;
  $html = '';

  $btn_prev = "<img src='/img/calendar/l.gif' border=0 width=11 height=11>";
  $btn_next = "<img src='/img/calendar/r.gif' border=0 width=11 height=11>";
  $btn_prev10 = "<img src='/img/calendar/l2.gif' border=0 width=11 height=11>";
  $btn_next10 = "<img src='/img/calendar/r2.gif' border=0 width=11 height=11>";

  $last = ceil($total/$ipp);
  if ($last == 0) $last = 1;

  $start = floor(($page - 1) / 10) * 10 + 1;
  $end = $start + 9;

  $html .= "<table border='0' cellpadding='2' cellspacing='0'><tr>"; # table 1

  $attr1 = " onmouseover=\"this.className='pager_on'\""
         ." onmouseout=\"this.className='pager_off'\""
         ." class='pager_off' align='center' style='cursor:pointer;'";
  $attr2 = " onmouseover=\"this.className='pager_sel_on'\""
         ." onmouseout=\"this.className='pager_sel_off'\""
         ." class='pager_sel_off' align='center' style='cursor:pointer;'";
 
  # previous link
  if ($start > 1) {
    $prevpage = $start - 1;
    $html .= "<td$attr1 align=center onclick=\"document.location='$url&page=$prevpage'\"><a href='$url&page=$prevpage'>$btn_prev10</a></td>\n";
  } else $html .= "<td align=center class='pager_static'>$btn_prev10</td>\n";

  if ($page > 1) {
    $prevpage = $page - 1;
    $html .= "<td$attr1 align=center onclick=\"document.location='$url&page=$prevpage'\"><a href='$url&page=$prevpage'>$btn_prev</a></td>\n";
  } else $html .= "<td align=center class='pager_static'>$btn_prev</td>\n";


  if ($end > $last) $end = $last;
 $html .= "</td>";
  for ($i = $start; $i <= $end; $i++) {
    $s = "$i";
    if ($i != $page) {
      $html .= "<td$attr1 onclick=\"document.location='$url&page=$i'\">$s</td>\n";
    } else {
      $html .= "<td$attr2>$s</td>\n";
    }
  }

  # next link
  if ($page < $last) {
    $nextpage = $page + 1;
    $html .= "<td$attr1 align=center onclick=\"document.location='$url&page=$nextpage'\"><a href='$url&page=$nextpage'>$btn_next</a></td>\n";
  } else $html .= "<td align=center class='pager_static'>$btn_next</td>\n";

  if ($end < $last) {
    $nextpage = $end + 1;
    $html .= "<td$attr1 align=center onclick=\"document.location='$url&page=$nextpage'\"><a href='$url&page=$nextpage'>$btn_next10</a></td>\n";
  } else $html .= "<td align=center class='pager_static'>$btn_next10</td>\n";

  $html .= "</tr></table>\n";

  return $html;
}


function MakeHttpQS($qopt=null, $extra=null) {
  global $env;
  if ($qopt == null) $qopt = $env['qopt'];
  if (!is_array($qopt)) $qopt = array();
  $retval = "?d";
  if ($extra != null) {
    $qopt = array_merge($qopt, $extra);
  }
  ksort($qopt);
  while (list($k, $v) = each($qopt)) {
    if ($v != '') $retval .= "&$k=$v";
  # print("$k, $v");
  }
  return $retval;
}



# 날짜입력폼
# input_empty=true 이면 빈(empty) 선택을 넣는다.
function DateInput3($prefix, $year=0, $month=0, $day=0, $onchange="",
      $sel_year=1, $disp_dow=1, $tostring=false, $input_empty=true) {
  global $conf, $lang;

  $lang['year']  = '년';
  $lang['month'] = '월';
  $lang['day']   = '일';

  $before = (isset($conf['year_before'])) ? $conf['year_before'] : 10;
  $after  = (isset($conf['year_after'])) ? $conf['year_after'] : 10;
  $html = '';
  $now = CurrentTime();
  $s_year = (int)$now['year'] - $before;
  $e_year = (int)$now['year'] + $after;

  $dow = DayOfWeek($year, $month, $day);
  $dows = explode(":", $lang['dayofweek']);
  $dow = $dows[$dow];

  if ($onchange != "") $onchange = " onchange=\"$onchange\"";

  if ($sel_year) {
    $html .= "<select name='${prefix}_year'$onchange>";
    if ($input_empty) $html .= "<option value=''></option>";
    for ($y = $e_year; $y >= $s_year; $y--) {
      $sel = ($y == $year) ? " selected" : "";
      $html .= "<option value='$y'$sel>$y</option>\n";
    }
    $html .= "</select>";
  } else {
    $html .= "<input type='text' name='${prefix}_year' size='5' value=\"$year\">";
  }
  $html .= "$lang[year]&nbsp;&nbsp;";

  $html .= "<select name='${prefix}_month'$onchange>";
  if ($input_empty) $html .= "<option value=''></option>";
  for ($m = 1; $m <= 12; $m++) {
    $sel = ($m == $month) ? " selected" : "";
    $html .= "<option value='$m'$sel>$m</option>\n";
  }
  $html .= "</select>";
  $html .= "$lang[month]&nbsp;&nbsp;";

  $html .= "<select name='${prefix}_day'$onchange>";
  if ($input_empty) $html .= "<option value=''></option>";
  for ($d = 1; $d <= 31; $d++) {
    $sel = ($d == $day) ? " selected" : "";
    $html .= "<option value='$d'$sel>$d</option>\n";
  }
  $html .= "</select>";
  $html .= "$lang[day]&nbsp;&nbsp;";
  if ($disp_dow) {
    $html .= "<input type='text' name='${prefix}_dow' size='3' value='$dow' readonly>\n";
  }
  if (!$tostring) print($html);
  else return $html;

  return null;
}
function DateInput2($name, $value) {
  print("<input type='text' name='$name' id='$name' style='ime-mode:disabled' value='$value' size='10' class='calendar' readonly>");
}

# 시간 입력폼
# prefix는 select폼 이름의 접두어 --> prefix_hour, prefix_min
# ival는 기본값 (array또는 string)
function TimeInput($prefix, $ival=null) {
  global $conf, $lang;

  if (is_array($ival)) { # array('hour'=>12,'min'=>59)
    $hour = $ival['hour'];
    $min  = $ival['min'];
  } else if (is_string($ival)) { # string ('12:59:00')
    $hour = substr($ival, 0, 2);
    $min = substr($ival, 3, 2);
  } else { $hour=0; $min=0; }

  //print("$hour $min");
  print("<select name='${prefix}_hour'>");
  for ($h = 0; $h <= 24; $h++) {
    if ($h == $hour) $selected = " selected"; else $selected = "";
    printf("<option value='$h'$selected>%02d</option>", $h);
  }
  print("</select>시");
  print("<select name='${prefix}_min'>");
  for ($m = 0; $m <= 60; $m+=5) {
    if ($m <= $min and $min <= $m+5) $selected = " selected"; else $selected = "";
    printf("<option value='$m'$selected>%02d</option>", $m);
  }
  print("</select>분");
}



function SafeFormValue($key, $maxlen=100) {
  global $form;
  $raw = $form[$key];
  if (strlen($raw) > $maxlen) iError("unsafe form value");
  return $raw;
}

# clear all cookies
function ClearCookies($time=0) {
  global $conf;
  $keys = array_keys($_COOKIE);
  $len = sizeof($keys);
  for ($i = 0; $i < $len; $i++) {
    $key = $keys[$i];
    setcookie($key, "", $time, "/");
  }
}


# URL 형식에 맞는 base64 인코드
function b64encode($str) {
  $data = base64_encode($str);
  // MIME::Base64::URLSafe implementation
  $data = str_replace(array('+','/','='),array('-','_',','),$data);
  //$data = str_replace(array('+','/'),array('-','_'),$data); // Python raise "TypeError: Incorrect padding" if you remove "=" chars when decoding 
  return $data;
}
# URL 형식에 맞는 base64 디코드
function b64decode($data) {
  $data = str_replace(array('-','_',','),array('+','/','='),$data);
  $str = base64_decode($data);
  return $str;
}


# 'yyyy-mm-dd hh:mm:ss' 형식의 datetime 문자열을
# mktime을 이용하여 타임스템프값(초단위)을 구함
function GetTimeStamp($date) {
  $y = substr($date,0,4);
  $m = substr($date,5,2);
  $d = substr($date,8,2);
  $h = substr($date,11,2);
  $n = substr($date,14,2);
  $s = substr($date,17,2);
  $t = mktime($h,$n,$s, $m,$d,$y);
  return $t;
}

# 주어진 날짜 및 시각에서 현재 시각까지 지난 시간을 초단위로 구함
# date는 'yyyy-mm-dd hh:mm:ss' 형식의 datetime 문자열
function AgeOfDate($date) {
  $t1 = GetTimeStamp($date);
  $diff = time() - $t1;
  return $diff;
}
function AgeString($date, $title='Edit') {
  $age = AgeOfDate($date);
/*
  if ($age < 60) $html = "<font color='#ff0000'>1m</font>";
  else if ($age < 60*10) $html = "<font color='#FF6060'>10m</font>";
  else if ($age < 60*30) $html = "<font color='#FFA6A6'>30m</font>";
  else if ($age < 60*60) $html = "<font color='#FFD2D2'>1h</font>";
*/
  if ($age < 60*10) $html = "<font color='#ff0000' size='1'>($title)</font>";
  else $html = '';
  //return "<sup>$html</sup>";
  return "$html";
}
function AgeString2($udate, $idate) {
  $uage = AgeOfDate($udate);
  $iage = AgeOfDate($idate);
  if ($uage < $iage) # 입력후 한번이상 업데이트되었을 때
    return AgeString($udate, '*');
  else # 입력되고 업데이트 되지 않았을 때
    return AgeString($idate, '+');
}


function Percent($value, $total) {
  if ($total == 0) return "0%";
  $percent = sprintf("%3.1f%%", $value/$total*100);
  return $percent;
}


# 다운로드를 위한 HTTP 헤더
function DownloadHeader($filename) {
  header("Content-disposition: attachment; filename=\"$filename\"");
  header("Content-type: application/octetstream");
  header("Pragma: no-cache");
  header("Expires: 0");
}

# 레지스트리 관리
function GetRegistry($key) {
  $qry = "SELECT * FROM registry WHERE rkey='$key'";
  $row = DBQueryAndFetchRow($qry);
  $value = $row['rvalue'];
  return $value;
}
function SetRegistry($key, $value) {
  # {
  $qry = "INSERT INTO registry SET rkey='$key',rvalue='$value'"
        ." ON DUPLICATE KEY UPDATE rkey='$key',rvalue='$value'";
  $row = DBQuery($qry);
  # }

/*
  # {
  # 같은 키가 존재하면 insert 되지 않음
  $qry = "INSERT INTO registry SET rvalue='$value', rkey='$key'";
# print($qry);
  $row = DBQuery($qry);
  $qry = "UPDATE registry SET rvalue='$value' WHERE rkey='$key'";
  $row = DBQuery($qry);
  # }
*/

  return null;
}

# 일반적인 라디오 버틈 폼
function FormRadios($len, $name, $values, $disps, $checked, $onclicks="") {
  global $env;
  $html = '';
  for ($i = 0; $i < $len; $i++) {
    $chk = ($values[$i] == $checked) ? " checked" : "";
    $onclick = ($onclicks[$i]) ? " onclick=\"$onclicks[$i]\"" : "";
    if ($i > 0) $html .= $env['separator'];
    $html .=<<<EOS
<nobr><input type='radio' name='$name' id='${name}_$i' value='$values[$i]'$chk$onclick><label
 for='${name}_$i'>$disps[$i]</label></nobr>
EOS;
  }
  return $html;
}

# 정상적인 주민등록번호 인지 검사함
function check_jumin_number($arg1, $arg2) {
/*
검사 원리 (알고리즘)

주민등록번호는 앞자리가 6자리의 숫자로 구성되며, 태어난 날의
연도, 월, 일을 나타내는 숫자이다. 뒷자리는 일련번호로서, 7자리로
구성되며 첫 번째 숫자는 성별을 나타내는 의미를 가지고 있다.
구분자인 '-' 기호를 빼면 총 13자리의 숫자로 구성되며, 정상적인
번호인지를 가려낼 수 있는 자체적인 정보를 담고 있다. 

정상적인 주민등록번호인지를 판별하기 위해서는 먼저 주민등록번호
맨 뒷자리를 제외한 각 자릿수의 숫자들에 각각 지정된 숫자들을
곱해서, 이 결과들을 더해야 한다. 각 자릿수에 지정된 승수들은
다음과 같다. (아래에서 진하게 표시된 행은 주민등록번호를 나타낸다) 

  1 2 3 4 5 6 - 1 2 3 4 5 6 7 
X 2 3 4 5 6 7   8 9 2 3 4 5   
 
  n1 n2 n3 n4 n5 n6   n7 n8 n9 n10 n11 n12   

각 자릿수에 지정된 승수들을 더한 값을 N이라고 하면, 

N = n1 + n2 + n3 + ... + n12

N을 11로 나눈 나머지를 11에서 뺀 수가 주민등록번호 마지막 자릿수와
일치하면 정상적인 주민등록번호이다. 

11 - (N % 11) = 마지막 자릿수

N의 값이 11로 나누어 떨어지거나 나머지가 1이라면 위 식의 값은 10
또는 11이 된다. 마지막 자릿수는 1자리이기 때문에 이런 경우에는
비교할 때 같지 않은 것으로 처리되기 때문에 위 식을 다시 한번
10으로 나누어 그 나머지를 취하여 마지막 자릿수와 비교해야 한다.
따라서, 위 식을 다음과 같이 수정해야 한다. 

(11 - (N % 11)) % 10 = 마지막 자릿수

*/
  $jumin1 = $arg1;
  $jumin2 = $arg2;
  $n1 = substr($jumin1,0,1);
  $n2 = substr($jumin1,1,1);
  $n3 = substr($jumin1,2,1);
  $n4 = substr($jumin1,3,1);
  $n5 = substr($jumin1,4,1);
  $n6 = substr($jumin1,5,1);
  $n7 = substr($jumin2,0,1);
  $n8 = substr($jumin2,1,1);
  $n9 = substr($jumin2,2,1);
  $n10 = substr($jumin2,3,1);
  $n11 = substr($jumin2,4,1);
  $n12 = substr($jumin2,5,1);
  $n13 = substr($jumin2,6,1);
  $sum = $n1*2+$n2*3+$n3*4+$n4*5+$n5*6+$n6*7+$n7*8+$n8*9+$n9*2+$n10*3+$n11*4+$n12*5;
  if ((11-($sum % 11) % 10) == $n13) return false; # 정상
  return true; # 오류
}



function MemberChangeHistory($inputno, $fld, $ctxt) {
  global $_SESSION;

  $m_inputno = $_SESSION['InputNo'];
  $m_name = $_SESSION['Name'];

  $qry = "INSERT INTO member_change_history"
     ." SET inputno='$inputno'"
     .",fld='$fld'"
     .",ctxt='$ctxt'"
     .",m_inputno='$m_inputno'"
     .",m_name='$m_name'"
     .",idate=NOW()";
  $ret = DBQuery($qry);
# $err = DBError();
# if ($err) {
#   print("$err<br>");
#   exit;
# }
}


# $form 변수로 부터 쿼리스트링을 만들어준다.
function Qstr($form) {
  global $env;
# if (!is_array($form)) $form = array();
  $retval = "?d";
  ksort($form);
  while (list($k, $v) = each($form)) {
    if ($v == '') continue;
    $retval .= "&$k=$v";
  # print("$k, $v");
  }
  return $retval;
}

# 사진파일이 존재하는지 여부
function IsPictureUploaded($manno) {
  $prefix = "/www/zion/Photo";

  # 주민번호 형식에 맞지 않으면..
  if ((strlen($manno) != 14) or (substr($manno, 6, 1) != '-')) {
    $path = "$prefix/etc/$manno.jpg";
  } else {
    $year = substr($manno, 0, 2);
    $path = "$prefix/$year/$manno.jpg";
  }

  # 사진파일이 존재하면
  if (file_exists($path)) return true;
  return false;
}


function SqlWhere(&$where, $value, $cond) {
  if ($value != '') {
    if ($where != '') {
      $where .= " AND ";
    }
    $where .= "($cond)";
  }
}


# DB 성능 모니터를 위한 타이밍 측정
function CheckTiming($mode='check', $prefix='') {
  global $env;

  if ($mode == 'check') { 
    if (!is_array($env['_t_'])) {
      $env['_t_'] = array();
    }
    $t = TimeStampUsec();
    array_push($env['_t_'], $t);
    return;
  }
  else if ($mode == 'dump') { 
    $html = '';
    for ($i = 1; $i < count($env['_t_']); $i++) {
      $t2 = $env['_t_'][$i];
      $t1 = $env['_t_'][$i-1];
      $diff = $t2 - $t1;
      $s = sprintf("%f", $diff/1000000);
      if ($html != '') $html .= ", ";
      $html .= "{$s}초";
    }
    return $prefix.$html;
  }
}

# form value: $form 에서 값을 얻어 옴
function FV($key) {
  global $form;
  $v = $form[$key];
  $v = trim($v); # 앞뒤의 공백을 제거
# strlen($v)
  return $v;
}

# form value: $form 에서 값을 얻어 옴
# 값이 null 이면 에러메시지 $msg를 출력한다.
function FV_CheckEmpty($key, $msg, $go_back=1, $win_close=0, $exit=1) {
  global $form;
  $v = $form[$key];

  # 앞뒤의 공백을 제거
  $v = trim($v);

  if ($v == '') iError($msg, $go_back);

  return $v;
}




# InputNoCounter 테이블에서 InputNo 번호를 딴다
function NewInputNo() {

  ##### 테이블을 잠근다.
  $ret = DBQuery("LOCK TABLES InputNoCounter WRITE");

  $qry = "SELECT InputNo FROM InputNoCounter";
  $row = DBQueryAndFetchRow($qry);
# print DBError();

  $InputNo = $row['InputNo'];

  $qry = "UPDATE InputNoCounter SET InputNo=InputNo+1"; # 1을 증가함
  $ret = DBQuery($qry);
# print DBError();

  ##### 잠긴 테이블을 푼다.
  $ret = DBQuery("UNLOCK TABLES");
# print("$InputNo<br>");

  return $InputNo;
}

# 한글 자르기
function cut_str($msg,$cut_size,$tail="") { 
  $msg_size=strlen($msg); 

  for($i=0,$r=0;$i<$cut_size;$i++){ 
    $real_cut_size++; 
    if(ord($msg[$r])>127){//한글일 경우 
      $real_cut_size++; 
      $r++;//한글일 경우 길이가 2여서 다음값으로 가기 위해서는 +1을 한다. 
     } 
     $r++;//다음자료를 가져옴 
  } 

  if($msg_size<=$real_cut_size){## 글길이 이하로 자를 경우 그냥 리턴한다. 
    return $msg; 
  }else{ 
    $str=substr($msg,0,$real_cut_size); 
    $str.=$tail; 
  } 
  return $str; 
}

# 한글 뒤에서 자르기
function back_cut($str,$len,$head="") { 
    if(strlen($str)<=$len) return $str; 

    $str2=substr($str,$len*-1); 
    $size=strlen($str2); 

    for($i=$size,$j=1;$j<=$size;$i--) 
    { 
      $chr=substr($str2,$j*-1,1); 

      if(ord($chr)>127) { 
        $j++; 
        $chr=substr($str,$j*-1,1).$chr; 
      } 

    	$result=$chr.$result; 
    	$j++; 
    } 

    return $head.$result; 
}

// 디버깅을 위한 출력
function dprint(&$var) {
  print("<pre>");
  print_r($var);
  print("</pre>");
}


# format byte data (reused from phpMyAdmin)
function FormatByteDown($value, $limes=6, $comma=0) {
  $dh           = pow(10, $comma);
  $li           = pow(10, $limes);
  $return_value = $value;

  $byteUnits    = array('Bytes', 'KB', 'MB', 'GB');
  $unit         = $byteUnits[0];

  if ($value >= $li*1000000) {
    $value = round($value/(1073741824/$dh))/$dh;
    $unit  = $byteUnits[3];
  } else if ($value >= $li*1000) {
    $value = round($value/(1048576/$dh))/$dh;
    $unit  = $byteUnits[2];
  } else if ($value >= $li) {
    $value = round($value/(1024/$dh))/$dh;
    $unit  = $byteUnits[1];
  }

  if ($unit != $byteUnits[0]) {
    $return_value = number_format($value, $comma, '.', ',');
  } else {
    $return_value = number_format($value, 0, '.', ',');
  }

  return array($return_value, $unit);
}


function ScjDateFormat($date) {
  $y = (int)substr($date, 0, 4);
  $m = (int)substr($date, 5, 2);
  $d = (int)substr($date, 8, 2);
  $year = $y-1983;
  if ($year < 0) return "";
  $ret = sprintf("신%d.%d.%d", $year, $m, $d);
  return $ret;
}


function SaveLog($msg) {
  $t = date("Y-m-d H:i:s");
  print("$t $msg\n");

  $qry = "INSERT INTO log SET memo='$msg',idate=now()";
  db_query($qry);
}


function get_depts_list() {
  $depts = array();
  $qry = "SELECT dept FROM dept ORDER BY dept";
  $ret = db_query($qry);
  while ($row = db_fetch($ret)) {
    $depts[] = $row['dept'];
  }
  //print_r($depts);
  //sort($depts);
  return $depts;
}

// 디버그 함수
function dd($msg) {
       if (is_string($msg)) print($msg);
  else if (is_array($msg)) { print("<pre>"); print_r($msg); print("</pre>"); }
  else print_r($msg);
}



/*
$script=<<<EOS
  // 단축키 정의
  if (ch == '`') { go_dump_mode(); return; }
  if (ch == 'w') { toggle_view_mode(); return; }
  if (ch == 'e') { go_edit_mode(); return; }
  if (ch == 'x') { close_window(); return; }
  if (ch == 'n') { go_new_sheet(); return; }
EOS;
  keypresshandler_javascript($script);
*/
function keypresshandler_javascript($script) {
  print<<<EOS
<script>
var _dom = 0;
function keypresshandler(e) {
  if (document.all) e=window.event; // for IE
  if (_dom == 3) var EventStatus = e.srcElement.tagName;
  else if (_dom == 1) var EventStatus = e.target.nodeName; // for Mozilla
  //alert(EventStatus);

  if (EventStatus == 'INPUT' || EventStatus == 'TEXTAREA' || _dom == 2) return;

  var cc = '';
  var ch = '';

  if (_dom == 3) { // for IE
    if (e.keyCode > 0) {
    ch = String.fromCharCode(e.keyCode);
    cc = e.keyCode;
    }
  } else { // for Mozilla
    cc = (e.keyCode);
    if (e.charCode > 0) {
      ch = String.fromCharCode(e.charCode);
    }
  }

  if (e.altKey || e.ctrlKey) return;
$script
  return;
}
_dom = document.all ? 3 : (document.getElementById ? 1 : (document.layers ? 2 : 0));
document.onkeypress = keypresshandler;
</script>
EOS;
}


// $btn = button_general('', 0, "onclick()", $style='', $class='');
function button_general($title='', $width=0, $onclick='', $style='', $class='') {
  if ($width) $w = " width='$width'";
  if ($style) $s = " style='$style'";
  if ($class) $l = " class='$class'";
  if ($onclick) $c = " onclick=\"$onclick\"";

  $html =<<<EOS
<input type='button' value='$title'$w$c$s$l>
EOS;
  return $html;
}


// $ti = textinput_general('name', $preset='', $size='10', $onkeypress='', $select=true, $maxlength=0, $style='', '');
function textinput_general($fname, $preset='', $size='10', $onkeypress='', $click_select=true, $maxlength=0, $style='', $attr='') {

  if ($click_select) { $onclick = "this.select()"; }
  if ($maxlength) $ml = " maxlength='$maxlength'";

  $s = "IME-MODE:active;";
  if ($style) $s .= $style;
  $sy = " style='$s'";

  $html =<<<EOS
<input type='text' name='$fname' size='$size'
 value='$preset' $sy onkeypress='$onkeypress' onclick='$onclick'$ml$attr>
EOS;
  return $html;
}


function download_head($file_prefix) {

  $filename = $file_prefix.'-'.date('YmdHis').'.xls';
  header("Content-disposition: attachment; filename=\"$filename\"");
  header("Content-type: application/octetstream");
  header("Content-Transfer-Encoding: binary");
  header("Pragma: no-cache");
  header("Expires: 0");

  $now = date("Y-m-d H:i:s");
  print<<<EOS
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<style> .td { color:#000000; font-size:9pt; font-family:굴림체; } </style>

<table width='' border='1' cellspacing='1' cellpadding='4'>
<tr>
<td width='200' class='td' bgcolor='#C8DFF9'>파일명</td>
<td width='600' class='td' colspan=4>{$filename}</td>
</tr>
<tr>
<td class='td' bgcolor='#C8DFF9'>사용자</td>
<td colspan=1 class='td' colspan=4>{$_SESSION['Name']}</td>
</tr>
<tr>
<td width='' class='td' bgcolor='#C8DFF9'>생성시각</td>
<td width='' class='td' colspan=4>{$now}</td>
</tr>
</table>
<br>

<table width='' border='1' cellspacing='1' cellpadding='4'>
EOS;
}
function download_tail() {
  print<<<EOS
</table>
EOS;
}
function download_th($str) {
  print<<<EOS
<td class='td' align='center' bgcolor='#C8DFF9'>$str</td>
EOS;
}

function download_td($str) {
  print<<<EOS
<td class='td' align='center'>$str</td>
EOS;
}

function current_ip() {
  return $_SERVER['REMOTE_ADDR'];
}


#   $list = array('chul_attd:1','chul_attd_yyyymmdd:2');
#   $preset = $form['ttype']; if (!$preset) $preset = '1';
#   print radio_list_general('ttype', $list, $preset);
function radio_list_general($fname, $list, $preset, $onclick='', $add_if_not_in_list=true) {
  $html = '';

  $tlist = $vlist = array();
  foreach ($list as $item) {
    list($t, $v) = preg_split("/:/", $item);
    if ($v == '') $v = $t;
    if ($v == 'null') $v = '';
    $tlist[] = $t;
    $vlist[] = $v;
  }

  if ($add_if_not_in_list) {
    // preset 이 리스트에 없으면 추가
    if (!in_array($preset, $vlist)) { $tlist[] = $preset; $vlist[] = $preset; }
  }

  $len = count($vlist);
  for ($i = 0; $i < $len; $i++) {
    $v = $vlist[$i];
    $t = $tlist[$i];

    if ($onclick) { $onclick_attr = " onclick=\"$onclick\""; }
    if ($preset == $v) $sel = ' checked'; else $sel = '';
    $html .=<<<EOS
<label style='cursor:pointer'><input type='radio' name='$fname' value='$v'$sel$onclick_attr>$t</label>
EOS;
  }
  return $html;
}

// $list = get_form_info($prefix='key');
function get_form_info($prefix='key', $frm='') {
  global $form;
  if (!$frm) $frm = $form;

  $keys = array_keys($frm);
  $count = 0;
  $info = array();
  for ($i = 0; $i < count($keys); $i++) {
    $key = $keys[$i];
    $val = $form[$key];
    list($a, $b) = explode('_', $key);
    if ($a == $prefix) {
      $count++;
      $info[] = array($b, $val);
    }
  }
  return $info;
}


# list($table_form_open, $table_form_close)
#   = table_oc_form('mmdata', 'form2', $env['self'], 'post', 'maindata', $table_attr='', $encmulti=false);
# print $table_form_open; # {{
# print $table_form_close; # }}
# list($table_open, $table_close) = table_oc_form('mmdata', '', '', '', 'maindata');
# print $table_open; # {{
# print $table_close; # }}
function table_oc_form($class='', $fn='', $action='', $method='', $id='', $table_attr='', $encmulti=false) {

  // 다운로드 모드이면 자동으로 border='1' 속성을 넣음
  global $download_mode;
  if ($download_mode) $table_attr .= " border='1'";
  if ($encmulti) $e = " enctype='multipart/form-data'"; else $e = '';

  $open = '';
  $open .= "<table class='$class' id='$id' $table_attr>";
  if ($fn) $open .= "<form name='$fn' action='$action' method='$method'$e>";

  $close = '';
  if ($fn) $close .= "</form>";
  $close .= "</table>";

  return array($open, $close);
}

# list($table_open, $table_close) = table_oc_form('mmdata', '', '', '', 'maindata');
# print $table_open; # {{
# print table_head_general('번호,이름,교회,부서,상태');
# $cls = array(); $dat = array();
# $cls[] = ''; $dat[] = '번호';
# print table_data_general($dat, $cls, $include_tr_tag=true, $th=false);
# print $table_close; # }}

//print table_head_general(array('번호','차량번호','모델','색상','메모'));
//print table_head_general('번호:c1,차량번호:c1,모델,색상,메모');
function table_head_general($titles, $include_tr_tag=true) {
  global $form;

  if (is_array($titles)) {
    $arr = $titles;
  } else {
    $arr = preg_split("/,/", $titles);
  }

  $html = '';
  if ($include_tr_tag) $html .= "<thead><tr>";
  foreach ($arr as $t) {
    list($t1, $t2) = preg_split("/:/", $t);
    if ($t2) $attr = " class='$t2'"; else $attr = '';
    if ($form['download']) { // 다운로드 모드일때..
      $html .= download_th($t1, true);
    } else {
      $html .= "<th nowrap$attr>$t1</th>";
    }
  }
  if ($include_tr_tag) $html .= "</tr></thead>";
  return $html;
}


//print table_data_general(array('번호','차량번호','모델','색상','메모'), $classes);
# $cls = []; $dat = [];
# $cls[] = ''; $dat[] = $cnt;
# print table_data_general($dat, $cls, $include_tr_tag=true, $th=false);
function table_data_general($data, $classes=null, $include_tr_tag=true, $th=false) {
  if ($include_tr_tag) $html = "<tr>"; else $html = "";

  $tag = 'td';
  if ($th) $tag = 'th';

  $idx = 0;
  foreach ($data as $t) {
    $attr = " class='{$classes[$idx]}'";
    $html .= "<$tag nowrap$attr>$t</td>";
    $idx++;
  }
  if ($include_tr_tag) $html .= "</tr>";
  return $html;
}


?>
