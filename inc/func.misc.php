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

/*
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
*/


/*
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
*/

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


/*
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
*/

/*
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
*/

function SqlWhere(&$where, $value, $cond) {
  if ($value != '') {
    if ($where != '') {
      $where .= " AND ";
    }
    $where .= "($cond)";
  }
}


/*
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
*/

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


/*
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
*/

/*
// 디버깅을 위한 출력
function dprint(&$var) {
  print("<pre>");
  print_r($var);
  print("</pre>");
}
*/


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

function button_box() {
  $len = func_num_args();
  $args = func_get_args();
  $html = "<table border='0'><tr>";
  for ($i = 0; $i < $len; $i++) {
    $btn = $args[$i];
    $html.="<td>";
    $html.=$btn;
    $html.="</td>";
  }
  $html.="</tr></table>";
  return $html;
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


// $cb = checkbox_general('XXXX', $form['XXXX'], '제목', $onclick='', $value='', $cls='', $id='');
function checkbox_general($fn, $preset, $title='', $onclick='', $value='', $cls='', $id='') {
  if ($onclick) { $oc = " onclick=\"$onclick\""; }
  if ($preset) $chk = ' checked'; else $chk = '';
  if ($value) $v = " value='$value'";
  if ($cls) $c = " class='$cls'";
  if ($id) $i = " id='$id'";
  $html = "<label$c><input type='checkbox' name='$fn' $chk $oc $v$c$i>$title</label>";
  return $html;
}


// label form element
function label_fe($title, $html, $cls='', $br=false) {
  $s = '';
  if ($cls == '') $cls = 'label';
  if ($br) $s .= "<br>";
  if ($title) $s .= "<span class='$cls'>$title</span>$html";
  else $s .= $html;
  return $s;
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


# list($table_form_open, $table_form_close) = table_oc_form('mmdata', 'form2', $env['self'], 'post', 'maindata');
# print $table_form_open; # {{
# $hdr = '번호,이름,고유번호,지파,교회,부서,등록상태';
# print table_head_general($hdr);
# $dat = array(); $cls = array();
# $dat[] = '번호'; $cls[] = '';
# print table_data_general($dat, $cls, $include_tr_tag=true, $th=false);
# print input_hidden_general('mode', '');
# print $table_form_close; # }}

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

// sql_where_match($w, 'realm', 'table.realm');
function sql_where_match(&$w, $fn, $col) {
  global $form;
  $v = $form[$fn];
  if ($v != '' && $v != 'all') { $w[] = "$col='$v'"; }
}


// sql_where_like($w, 'search', 'm.Name,m.NewNo');
// sql_where_like($w, '', 'm.Name,m.NewNo', '키워드');
//if ($v != '') $w[] = "((m.Name LIKE '%$v%') OR (m.NewNo LIKE '%$v%'))";
// sv 값이 주어지면 $form 을 참조하지 않음
function sql_where_like(&$w, $fn, $columns, $sv='', $match='center') {
  global $form;
  if ($sv == '') {
    $v = $form[$fn]; if (!$v) return;
  } else $v = $sv;
  $v = trim($v);

  $cols = preg_split("/,/", $columns);
  $b = array();
  foreach ($cols as $col) {
    if ($match == 'center') $b[] = "($col LIKE '%$v%')";
    else if ($match == 'left') $b[] = "($col LIKE '$v%')";
    else if ($match == 'right') $b[] = "($col LIKE '%$v')";
    else $b[] = "($col LIKE '%$v%')";
  }
  $str = join(" OR ", $b);
  $w[] = "($str)";
}



#$sort_info = array(
#  1 => array('m.Name','이름',1),
#  2 => array('m.ManNo DESC','고유번호',0),
#);
#$html = sort_select($sort_info, $fn='sort');
#$sql_order = sort_order($sort_info, $fn='sort');
function sort_select($info, $fn='sort') {
  global $form;
  $preset = $form[$fn];

  $html="<select name='$fn'>";
  foreach ($info as $s => $item) {
    list($o, $t, $d) = $item;
    $chk = '';
    if ($preset != '' && $s == $preset) $chk = ' selected';
    if ($preset == '' && $d == 1) $chk = ' selected';
    $html.="<option value='$s'$chk>$t</option>";
  }
  $html.="</select>";
  return $html;
}
function sort_order($info, $fn='sort') {
  global $form;
  $preset = $form[$fn];

  $ord = '';
  foreach ($info as $s => $item) {
    list($o, $t, $d) = $item;
    if ($d) $default_order = $o;
    if ($preset == $s) { $ord = $o; break; }
  }
  if ($ord == '') $ord = $default_order;
  $sql_order = " ORDER BY $ord";
  return $sql_order;
}

// $html = select_element('iflag', $opts, $id='', $onchange='');
// $html = select_element('iflag', $opts);
// print label_fe('출력', $html);
function select_element($fn, $opts, $id='', $onchange='') {
  if ($id) $ida = " id='$id'"; else $ida = '';
  if ($onchange) $oc = " onchange='$onchange'"; else $oc = '';
  $html=<<<EOS
<select name='$fn' $ida$oc>$opts</select>
EOS;
  return $html;
}

// select option
# $list = array('=선택=:null','전체요청:all','개명신청:name');
# $preset = $form['rtype']; if ($preset=='') $preset = 'name';
# $opt = option_general($list, $preset);
# $html = select_element('rtyp', $opts, $id='');
function option_general($list, $preset, $flag_add=true) {
  $opts = "";
  $tlist = $vlist = array();
  foreach ($list as $item) {
    list($t, $v) = preg_split("/:/", $item);
    if ($v == '') $v = $t;
    if ($v == 'null') $v = '';
    $tlist[] = $t;
    $vlist[] = $v;
  }
  //dd($tlist); dd($vlist);
  // preset 이 리스트에 없으면 추가
  if ($flag_add) {
    if (!in_array($preset, $vlist)) { $tlist[] = $preset; $vlist[] = $preset; }
  }

  $len = count($vlist);
  for ($i = 0; $i < $len; $i++) {
    $v = $vlist[$i];
    $t = $tlist[$i];
    if ($v == $preset) $s = ' selected'; else $s = '';
    $opts .= "<option value='$v'$s>$t</option>";
  }
  return $opts;
}


// form['fd01'] ,form['fd02'], .. 값을 검사하여 $fck 를 설정
//  $fck = array(); // field check '' or ' checked'
//   fck_init($fck, $defaults='1,4,5,6', $max=10);
function fck_init(&$fck, $defaults='', $max=100) {
  global $form;

  $flag = false;
  for ($i = 1; $i <= $max; $i++) {
    $key = "fd$i";
    $v = $form[$key];
    if ($v != '') $flag = true;
  }
  if ($flag == false) {
    $a = preg_split("/,/", $defaults);
    foreach ($a as $idx) {
      $key = "fd$idx";
      $form[$key] = 'on';
    }
  }

  $fck = array(); // field check '' or ' checked'
  $flag = false;
  for ($i = 1; $i <= $max; $i++) {
    $key = "fd$i";
    $v = $form[$key];
    if ($v != '') $fck[$i] = ' checked';
    else $fck[$i] = '';
    if ($v != '') $flag = true;
  }
}


// hiddenframe($debug, 'hiddenframe', 600, 600);
//  hiddenframe.document.location = url;
function hiddenframe($debug, $name='hiddenframe', $w=600, $h=600) {
  if ($debug) {
    print("<iframe name='$name' width='$w' height='$h' style='display:block'></iframe>");
  } else {
    print("<iframe name='$name' width='0' height='0' style='display:none'></iframe>");
  }
}

// $html = span_link2('수정', "_edit('$id')");
function span_link2($title, $onclick) {
  $html =<<<EOS
<span class=link onclick="$onclick">{$title}</span>
EOS;
  return $html;
}


function getHumanTime($s) {
  //$unit = array('D'=>' days','H'=>' hours','M'=>' mins','S'=>' secs');
  $unit = array('D'=>'일','H'=>'시간','M'=>'분','S'=>'초');

  $m = $s / 60;
  $h = $s / 3600;
  $d = $s / 86400;
  if ($m > 1) {
    if ($h > 1) {
      if ($d > 1) {
        return (int)$d.$unit['D'];
      } else {
        return (int)$h.$unit['H'];
      }
    } else {
      return (int)$m.$unit['M'];
    }
  } else {
    return (int)$s.$unit['S'];
  }
}


// $list = get_checked_list($prefix='cb');
function get_checked_list($prefix='cb', $frm='') {
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
      $info[] = $b;
    }
  }
  return $info;
}

// 페이지당 아이템 수
// $ipp = get_ipp(20,$min=10,$max=500);
//<span class='label'>출력</span><select name='ipp'>$opts</select>명/페이지
function get_ipp($default=20, $min=10, $max=500) {
  global $form;
  $ipp = $form['ipp'];
  if ($ipp == '') $ipp = $default;
  if ($ipp < $min) $ipp = $min;
  else if ($ipp > $max) $ipp = $max;
  return $ipp;
}


//$opts = option_ipp($ipp, array(10,20,50,200,500));
function option_ipp($preset='', $values='') {
  $opts = '';
  if (!$values) $values = array(10,20,50,100,200,500);
  foreach ($values as $v) {
     if ($preset == $v) $sel = ' selected'; else $sel = '';
     $opts .= <<<EOS
<option value='$v'$sel>$v</option>
EOS;
  }
  return $opts;
}


function mktime_date_string($str) {
  $y = (int)substr($str, 0, 4);
  $m = (int)substr($str, 5, 2);
  $d = (int)substr($str, 8, 2);
  $h = (int)substr($str, 11, 2);
  $i = (int)substr($str, 14, 2);
  $s = (int)substr($str, 17, 2);
  return mktime($h,$i,$s,$m,$d,$y);
}

function how_many_days_before($date) {
  $s = mktime_date_string($date);
  $now = time();
  $days = floor(($now - $s)/3600/24);
  return $days;
}


function how_many_seconds_before($udate, $for_human=false) {
  $s = time() - mktime_date_string($udate);
  if (!$for_human) return $s;

  $m = floor($s / 60);
  $s = $s % 60;
  //dd("m=$m s=$s<br>");

  $r = "{$m}분{$s}초";
  return $r;
}

// 'x' 또는 ESC 를 누르면 팝업창을 닫게 한다.
function xkey_close_window() {
  global $env;

  if (@$env['_xkey_']) return;
  $env['_xkey_'] = true;

  print<<<EOS
<script>
// single keys
Mousetrap.bind('x', function() {
  window.close();
});
Mousetrap.bind('esc', function() {
  window.close();
});
Mousetrap.bind('1', function() {
  window.moveTo(0,0);
});
Mousetrap.bind('2', function() {
  window.moveBy(100,0);
});
Mousetrap.bind('3', function() {
  window.moveBy(100000,0);
});
</script>
EOS;

}


# list($table_open, $table_close) = table_oc_form('mmdata', '', '', '', 'maindata');
# print $table_open; # {{
# print $table_close; # }}
# print $html = table_2column('제목', '내용', $c1='', $c2='', $a1='', $a2='', $cspan1='', $cspan2='', $include_tr_tag=true);
function table_2column($data1, $data2, $c1='', $c2='', $a1='', $a2='', $cspan1='', $cspan2='', $include_tr_tag=true) {
  if ($c1) $cls1 = " class='$c1'";
  if ($c2) $cls2 = " class='$c2'";
  if ($cspan1) $cspan1 = " colspan='$cspan1'";
  if ($cspan2) $cspan2 = " colspan='$cspan2'";
  $html = "";
  if ($include_tr_tag) $html .= "<tr>";
  $html .=<<<EOS
<th$cls1 $a1$cspan1>$data1</th>
<td$cls2 $a2$cspan2>$data2</td>
EOS;
  if ($include_tr_tag) $html .= "</tr>";
  return $html;
}



// print notice_msg1("메시지입니다.");
function notice_msg1($msg) {
  $html =<<<EOS
<p class='desc'>$msg</p>
EOS;
  return $html;
}

// $mac = my_mac_address();
function my_mac_address() {
  $ip = current_ip();
  $command = "/sbin/arp -n | grep '$ip '";
  $lastline = exec($command, $out, $retval);
  list($a,$b,$c,$d,$e) = preg_split("/ +/", $lastline);
  $mac = $c;
  return $mac;
}


?>
