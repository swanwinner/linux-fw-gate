<?php

  include_once("path.php");
  include_once("$env[prefix]/inc/common.php");
  include_once("$env[prefix]/inc/class.checkbox_multi2.php");
  include_once("$env[prefix]/inc/class.pager.php");

  $net_subnets = $conf['subnets'];
  $net_subnet_title = $conf['subnet_title'];

  $netclass = $conf['net_class'];

$sort_info = array(
  1 => array('i.ip3, i.ip4           ', '::기본::',1),
  2 => array('i.ip3, i.ip4           ', 'IP주소',0),
  3 => array('i.realmac              ', 'MAC주소',0),
  4 => array('i.name, ip3, ip4       ', '사용자',0),
  5 => array('i.udate DESC, ip3, ip4 ', '마지막발견(최근)',0),
  6 => array('i.udate, ip3, ip4      ', '마지막발견(오래됨)',0),
  7 => array('i.idate DESC           ', '최근변경',0),
  8 => array('i.checkStar DESC       ', '별표 표시',0),
  9 => array('i.point DESC           ', '점수',0),
);


### {{{
function _debug($o) {
  global $form;
  if ($form['debug']) dd($o);
}


function _mktime_date_string($str) {
  $y = (int)substr($str, 0, 4);
  $m = (int)substr($str, 5, 2);
  $d = (int)substr($str, 8, 2);
  $h = (int)substr($str, 11, 2);
  $i = (int)substr($str, 14, 2);
  $s = (int)substr($str, 17, 2);
  return mktime($h,$i,$s,$m,$d,$y);
}

function _dd($msg) {
       if (is_string($msg)) print($msg);
  else if (is_array($msg)) { print("<pre>"); print_r($msg); print("</pre>"); }
  else print_r($msg);
}

function option_bcode($preset) {
  global $conf;

  $opts = "";
  foreach ($conf['bcode_list'] as $v=>$t) {
    if ($v == $preset) $sel = ' selected'; else $sel = '';
    $opts .= "<option value='$v'$sel>$t</option>";
  }
  return $opts;
}

function _star_check($preset, $id) {
  if ($preset) {
    $src = "/img/star.png";
  } else {
    $src = "/img/star2.png";
  }

  $imgid = "star_$id";
  $html =<<<EOS
<img src='$src' id='$imgid' sval='$preset' onclick="_setstar('$id','$imgid')">
EOS;
  return $html;
}
function _star_script() {
  global $env;
  $src_on = "/img/star.png";
  $src_off = "/img/star2.png";

  $html = <<<EOS
<script>
function _setstar(id,imgid) {
  var img = document.getElementById(imgid);
  var sval = img.getAttribute('sval');
  //alert(img);
  //alert(img.getAttribute);
  //alert(sval);

  var toset;
  if (sval == '1') toset = 0; else toset = 1;

  var xmlHttp = new XMLHttpRequest();
  var url = "$env[self]?mode=setstar&id="+id+"&toset="+toset;
  xmlHttp.open("GET", url, true);
  xmlHttp.onreadystatechange = function() {
    if (xmlHttp.readyState == 4) {
      var response = xmlHttp.responseText;
      //alert(response);
      //eval(response);
      if (toset) {
        img.src = '$src_on';
      } else {
        img.src = '$src_off';
      }
      img.setAttribute('sval', toset);

    }
  };
  xmlHttp.send(null);
}
</script>
EOS;
  return $html;
}

function _save_points($id, $points, &$sum) {
  $s = array();

  $sum = 0;
  $list = preg_split("/,/", $points);
  $i = 1;
  foreach ($list as $p) {
    $sum += $p;
    $s[] = "p{$i}='$p'";
    $i++;
    if ($i > 10) break;
  }
  $sql_set = " SET ".join(",", $s);

  $qry = "select * FROM points where id='$id'";
  $row = db_fetchone($qry);
  if ($row) {
    $qry = "UPDATE points"
        .$sql_set
        ." WHERE id='$id'";
  } else {
    $qry = "INSERT INTO points"
        .$sql_set
        .", id='$id'";
  }
  $ret = db_query($qry);
// dd($qry); exit;

}
### }}}


### {{{
// ajax 호출
if ($mode == 'getip4') {

  $subnet = $form['subnet'];

  // secondary ip로 사용되고 있으면 제외
  $qry = "select secondip from ipdb where secondip is not null and secondip != ''";
  $ret = db_query($qry);
  $slist = array();
  while ($row = db_fetch($ret)) {
    $slist[] = $row['secondip'];
  }
  //_dd($slist);


  $qry = "SELECT * FROM ipdb WHERE ip3='$subnet' ORDER BY ip4";
  $ret = db_query($qry);

  $info = array();
  $ip4_list = array();
  while ($row = db_fetch($ret)) {
    $ip = $row['ip'];
    $ip4 = $row['ip4'];

    $info[$ip4] = $row;
    $ip4_list[] = $ip4;
  }

  $netclass = $conf['net_class'];

  $list1 = ''; // ip4
  $list2 = ''; // dept
  $list3 = ''; // name
  for ($ip4 = 1; $ip4 <= 253; $ip4++) {

    // secondary ip로 사용되고 있으면 제외
    $ip = "$netclass.$subnet.$ip4";
    if (in_array($ip, $slist)) continue;

    if (in_array($ip4, $ip4_list)) {
      $name = $info[$ip4]['name'];
      $dept = $info[$ip4]['dept'];
    } else {
      $name = '';
      $dept = '';
    }

    if ($list1 != '') $list1 .= ',';
    if ($list2 != '') $list2 .= ',';
    if ($list3 != '') $list3 .= ',';
    $list1 .= "'$ip4'";
    $list2 .= "'$dept'";
    $list3 .= "'$name'";
  }

  print<<<EOS
var option1 = [$list1];
var option2 = [$list2];
var option3 = [$list3];
EOS;
  exit;

} else if ($mode == 'doadd') {
  //print_r($form); exit;

  $ip3 = $form['ip3'];
  $ip4 = $form['ip4'];
  $ip = "$netclass.$ip3.$ip4";

  $name = $form['name'];
  $dept = $form['dept'];
  $mac = $form['mac'];
  $secondip = $form['secondip'];
  $memo = $form['memo'];
  $ostype = $form['ostype'];
  $wireless = $form['wireless'];

  $bflag = $form['bflag'];
  if ($bflag != '') $bflag = '1';

  $use_the_ip = $form['use_the_ip'];
  if ($use_the_ip) $use_the_ip = '1'; else $use_the_ip = '0';

  $checkStar = $form['checkStar'];

  $s = array();
  $s[] = "idate=now()";
  $s[] = "name='$name'";
  $s[] = "dept='$dept'";
  $s[] = "memo='$memo'";
  $s[] = "ostype='$ostype'";
  $s[] = "bflag='$bflag'";
  $s[] = "use_the_ip='$use_the_ip'";
  $s[] = "bcode='$bcode'";
  $s[] = "sflag='$sflag'";
  $s[] = "dtype='$dtype'";
  $s[] = "wireless='$wireless'";
  $s[] = "staticip='$staticip'";
  $s[] = "mac='$mac'";
  $s[] = "realmac='$realmac'";
  $s[] = "checkStar='$checkStar'";
  $sql_set = " SET ".join(",", $s);

  $qry = "INSERT INTO ipdb $sql_set";
  $ret = db_query($qry);

  $qs = "mode=search&ip=$ip";
  $url = "$env[self]?$qs";
  Redirect($url);
  exit;

// 새창에서 로딩됨
} else if ($mode == 'doedit') {

  $ip3 = $form['ip3'];
  $ip4 = $form['ip4'];
  $ip = "$netclass.$ip3.$ip4";

  $secondip = $form['secondip'];

  $name = $form['name'];
  $dept = $form['dept'];
  $memo = $form['memo'];
  $ostype = $form['ostype'];
  $dtype = $form['dtype'];
  $wireless = $form['wireless'];
  $natpubip = $form['natpubip'];

  $staticip = $form['staticip'];
  if (!($staticip == '1' or $staticip == '0')) $staticip = '0';
  if ($staticip == '1') {
    $ip3 = $form['ip3'];
    $ip4 = $form['ip4'];
    $ip = "$netclass.$ip3.$ip4";
    $secondip = $form['secondip'];
  }

  $bflag = $form['bflag'];
  if ($bflag != '') $bflag = '1'; else $bflag = '0';

  $use_the_ip = $form['use_the_ip'];
  if ($use_the_ip) $use_the_ip = '1'; else $use_the_ip = '0';

  $bcode = $form['bcode'];
  $sflag = $form['sflag'];
  $sec_chk_sflag = $form['sec_chk_sflag'];
  $checkStar = $form['checkStar'];

  $id = $form['id'];

  $points = $form['points'];
  _save_points($id, $points, $sum);

  $s = array();

  if ($staticip == '1') {
    $s[] = "ip='$ip'";
    $s[] = "ip3='$ip3'";
    $s[] = "ip4='$ip4'";
    $s[] = "secondip='$secondip'";
  }

  $s[] = "ds_no_use_cause='{$form['ds_no_use_cause']}'";
  $s[] = "ds_usage_check='{$form['ds_usage_check']}'";

  $s[] = "name='$name'";
  $s[] = "dept='$dept'";
  $s[] = "memo='$memo'";
  $s[] = "ostype='$ostype'";
  $s[] = "bflag='$bflag'";
  $s[] = "use_the_ip='$use_the_ip'";
  $s[] = "bcode='$bcode'";
  $s[] = "sflag='$sflag'";
  $s[] = "sec_chk_sflag='$sec_chk_sflag'";
  $s[] = "dtype='$dtype'";
  $s[] = "wireless='$wireless'";
  $s[] = "staticip='$staticip'";
  $s[] = "natpubip='$natpubip'";
  $s[] = "checkStar='$checkStar'";
  $s[] = "point='$sum'";
  $s[] = "idate=NOW()";
  $sql_set = " SET ".join(",", $s);

  $qry = "UPDATE ipdb $sql_set WHERE id='$id'";
  $ret = db_query($qry);

  print<<<EOS
<script>
opener.document.location.reload();
window.close();
</script>
EOS;
  exit;

// iframe 안에 로딩됨
} else if ($mode == 'dodelete') {

  //print_r($form);
  $f = $form;
  $keys = array_keys($f);
  $l = count($keys);
  $ids = array();
  for ($i = 0; $i < $l; $i++) {
    $key = $keys[$i];
    list($a, $b) = explode('_', $key, 2);
    if ($a != 'cb') continue;
    if (!$f[$key]) continue;
    $id = $b;
    $ids[] = $id;
  }
  //print_r($ids);

  $len = count($ids);
  for ($i = 0; $i < $len; $i++) {
    $id = $ids[$i];
    $qry = "DELETE FROM ipdb WHERE id='$id'";
    $ret = db_query($qry);
  }

  print<<<EOS
<script>
parent.document.location.reload();
</script>
EOS;
  exit;

} else if ($mode == 'doset') {

  $mode2 = $form['mode2'];
  if ($mode2 == '0') $sflag = '0';
  else $sflag = '1';

  $ids = get_checked_list($prefix='cb');
  //$ids = _get_cb_list();
  _dd($ids);

  $len = count($ids);
  for ($i = 0; $i < $len; $i++) {
    $id = $ids[$i];

    $qry = "UPDATE ipdb SET sflag='1', sec_chk_sflag='$sflag' WHERE id='$id'";
    $ret = db_query($qry);
  }

  print<<<EOS
<script>
parent.document.location.reload();
</script>
EOS;
  exit;

} else if ($mode == 'resetpoint') {

  $ids = get_checked_list($prefix='cb');
  //$ids = _get_cb_list();
  //_dd($ids);

  $len = count($ids);
  for ($i = 0; $i < $len; $i++) {
    $id = $ids[$i];

    $qry = "UPDATE ipdb SET point='' WHERE id='$id'";
    $ret = db_query($qry);

    $qry = "DELETE FROM points WHERE id='$id'";
    $ret = db_query($qry);
  }

  print<<<EOS
<script>
parent.document.location.reload();
</script>
EOS;
  exit;

// iframe 안에 로딩됨
} else if ($mode == 'delete') {

  $id = $form['id'];

  $qry = "DELETE FROM ipdb WHERE id='$id'";
  $ret = db_query($qry);

  print<<<EOS
<script>
parent.document.location.reload();
</script>
EOS;
  exit;

} else if ($mode == 'add' or $mode == 'edit') {

  $qs = $form['qs'];

  if ($mode == 'add') {
    $nextmode = 'doadd';
    $hidden = "";
    $hidden2 = "<input type='hidden' name='qs' value='$qs'>";
    $title = '신규 등록';

  } else if ($mode == 'edit') {
    $id = $form['id'];

    $qry = "SELECT oui.company, a.*, p.*"
   ." FROM (SELECT i.*, CONCAT(SUBSTRING(mac,1,2),'-',SUBSTRING(mac,4,2),'-',SUBSTRING(mac,7,2)) AS mac6 "
      ."  , UNIX_TIMESTAMP(sec_installed_time) ts1"
      ."  , UNIX_TIMESTAMP(sec_installed_time) ts2"
      ." FROM ipdb i WHERE i.id='$id') a"
   ." LEFT JOIN oui ON a.mac6=oui.mac6"
   ." LEFT JOIN points p on a.mac=p.mac"
   ;

    $ret = db_query($qry);
    $row = db_fetch($ret);
    $nextmode = 'doedit';
    $hidden = "<input type='hidden' name='id' value='$id'>";
    $hidden2 = "<input type='hidden' name='qs' value='$qs'>";
    $title = '정보 수정';
    list($a, $b, $row['ip3'], $row['ip4']) = preg_split("/\./", $row['ip']);
  }

  AdminPageHead($title, $menu=false);
  ParagraphTitle($title);

  print<<<EOS
<table class='main' width='400' style='margin-top:30px;'>
<form name='form' action='$env[self]' method='post'>
<tr>
 <td colspan='2' align='center'>
<button onclick='sf_1()' style="width:150px; height:30px;">저장</button>
&nbsp;&nbsp;&nbsp;&nbsp;
<input type='button' onclick="window.close()" value='취소' style="width:40; height:20;">

<input type='hidden' name='mode' value='$nextmode'>
$hidden
$hidden2
 </td>
</tr>
EOS;

   $chk1 = $chk2 = '';
  if ($row['staticip']) $chk1 = ' checked';
  else $chk2 = ' checked';
  print<<<EOS
<tr>
<th>고정/유동 IP</th>
<td>
<input type='radio' name='staticip' value='1'$chk1 id='si1' onclick='clk_staticip()'><label for=si1>고정IP설정</label>
<input type='radio' name='staticip' value='0'$chk2 id='si2' onclick='clk_staticip()'><label for=si2>유동IP설정(DHCP)</label>
</td>
</tr>
EOS;

  $preset = $row['ip3'];
  $nets = $net_subnets;
  $n = count($nets);
  $opts = "<option value=''>::선택::</option>";
  for ($i = 0; $i < $n; $i++) {
    $net = $nets[$i];
    if ($preset == $net) $sel = " selected"; else $sel = '';
    $title = $net_subnet_title[$net];
    $opts .= "<option value='$net'$sel>$net ($title)</option>";
  }
  $subnet_opts = $opts;

  if ($row['staticip']==0) {
    $dis = " disabled";
  } else $dis = "";

  print<<<EOS
<tr>
 <th>IP</th>
 <td>
$netclass.
<select name='ip3' onchange='_sel_ip3()' $dis>$subnet_opts</select>
.
<select name='ip4'' $dis></select>
 </td>
</tr>

<tr>
 <th>IP(secondary)</th>
 <td><input type='text' name='secondip' size='60' value='$row[secondip]'>
<p class=desc>프린터공유, 폴더공유 때문에 IP를 2개 할당하는 경우만 기입</p>
</td>
</tr>
EOS;

  $depts = get_depts_list();
  $opts = "<option value=''>::전체부서::</option>";
  $n = count($depts);
  for ($i = 0; $i < $n; $i++) {
    $dept = $depts[$i];
    if ($row['dept'] == $dept) $sel = " selected"; else $sel = '';
    $opts .= "<option value='$dept'$sel>$dept</option>";
  }
  $dept_opts = $opts;

  print<<<EOS
<tr>
 <th>부서</th>
 <td>
<select name='dept'>$dept_opts</select>
</td>
</tr>
EOS;

  print<<<EOS
<tr>
 <th>사용자</th>
 <td><input type='text' name='name' size='60' value='$row[name]' onclick="this.select()"></td>
</tr>
EOS;

  if ($row['wireless']) $chk2 = ' checked';
  else $chk1 = ' checked';
  print<<<EOS
<tr>
 <th>유선/무선</th>
 <td>
<label><input type='radio' name='wireless' value='0'$chk1>유선</label>
<label><input type='radio' name='wireless' value='1'$chk2>무선</label>
</td>
</tr>
EOS;


  print<<<EOS
<tr>
 <th>메모</th>
 <td><input type='text' name='memo' size='60' value='$row[memo]'></td>
</tr>
EOS;

  print<<<EOS
<tr>
<th>OS종류</th>
<td><input type='text' name='ostype' size='60' value='$row[ostype]'></td>
</tr>
EOS;

  $chk1 = $chk2 = '';
  if ($row['ds_usage_check']=='1') $chk1 = ' checked';
  if ($row['ds_usage_check']=='2') $chk2 = ' checked';
  print<<<EOS
<tr>
<th>문서보안사용</th>
<td>
<label><input type='radio' name='ds_usage_check' value='1'$chk1>사용</label>
<label><input type='radio' name='ds_usage_check' value='2'$chk2>미사용</label>
사유: <input type='text' name='ds_no_use_cause' size='40' value='{$row['ds_no_use_cause']}' onclick="this.select()">
</td>
</tr>
EOS;


  $devlist = $conf['dtype'];
  $opts = "<option value=''></option>";
  $n = count($devlist);
  for ($i = 0; $i < $n; $i++) {
    $dev = $devlist[$i];
    if ($row['dtype'] == $dev) $sel = " selected"; else $sel = '';
    $opts .= "<option value='$dev'$sel>$dev</option>";
  }
  $dtype_opts = $opts;

  print<<<EOS
<tr>
 <th>장비분류</th>
 <td>
<select name='dtype'>$dtype_opts</select>
</td>
</tr>
EOS;

  $chk1 = $chk2 = ''; if ($row['sflag']) $chk1 = ' checked'; else $chk2 = ' checked';
  print<<<EOS
<tr>
<th>보안점검</th>
<td>
<label><input type='radio' name='sflag' value='1' $chk1 >보안점검포함</label>
<label><input type='radio' name='sflag' value='0' $chk2 >보안점검제외</label>
</td>
</tr>
EOS;

  $chk1 = $chk2 = ''; if ($row['sec_chk_sflag']) $chk1 = ' checked'; else $chk2 = ' checked';
  print<<<EOS
<tr>
<th>보안점검</th>
<td>
<label><input type='radio' name='sec_chk_sflag' value='1'$chk1 >보안점검 시작</label>
<label><input type='radio' name='sec_chk_sflag' value='0'$chk2 >보안점검 미시작</label>
</td>
</tr>
EOS;


  if ($row['bflag']) $chk = ' checked'; else $chk = '';
  print<<<EOS
<tr>
 <th>차단</th>
 <td>
 <label><input type='checkbox' name='bflag' $chk>MAC 주소로 인터넷 차단</label>
 </td>
</tr>
EOS;

  if ($row['use_the_ip']) $chk = ' checked'; else $chk = '';
  print<<<EOS
<tr>
 <th>보안규칙적용</th>
 <td>
 <label><input type='checkbox' name='use_the_ip' $chk>지정된 IP 주소가 아니면 차단 정책 적용</label>
 </td>
</tr>
EOS;

  $bcode = $row['bcode'];
  $opts = option_bcode($bcode);
  print<<<EOS
<tr>
<th>차단사유</th>
<td><select name='bcode'>$opts</select></td>
</tr>
EOS;

  $chk1 = $chk2 = ''; if ($row['checkStar']) $chk1 = ' checked'; else $chk2 = ' checked';
  print<<<EOS
<tr>
<th>표시</th>
<td>
<label><input type='radio' name='checkStar' value='1'$chk1 ><img src='/img/star.png'></label>
<label><input type='radio' name='checkStar' value='0'$chk2 ><img src='/img/star2.png'></label>
</td>
</tr>
EOS;

# $points = sprintf("%s,%s,%s,%s,%s,%s,%s,%s,%s,%s"
#    , $row['p1'] , $row['p2'] , $row['p3'] , $row['p4'] , $row['p5']
#    , $row['p6'] , $row['p7'] , $row['p8'] , $row['p9'] , $row['p10']);
  $t1 = textinput_general('point', $row['point'], $size='10', '', true, 0, 'background-color:lightgray;', ' readonly');
# $t2 = textinput_general('points', $points, $size='40', '', true, 0);
//세부점수: {$t2}점

  $mac = $row['realmac'];
  print<<<EOS
<tr>
<th>점수</th>
<td>
{$t1}점
<a href="secchk2.php?mac={$mac}" class='link'>[[점수변경]]</a>
</td>
</tr>
EOS;

  print<<<EOS
<tr>
<th>ARP-MAC</th>
<td>$row[mac] (제조사:$row[company])<br>
<input type='hidden' name='mac' size='60' value='$row[mac]'>
</td>
</tr>

<tr>
<th>실제 MAC</th>
<td>$row[realmac]<br>
<input type='hidden' name='realmac' size='60' value='$row[realmac]'>
</td>
</tr>
EOS;

  print<<<EOS
<tr>
<th>마지막 발견</th>
<td>$row[udate]<br>
</td>
</tr>
EOS;

  print<<<EOS
<tr>
<th>DRM 사용</th>
<td>$row[drm_time]<br>
</td>
</tr>
EOS;

/*
  if ($row['sec_installed_flag']) $s1 = '설치됨'; else $s1 = '설치안됨';
  if ($row['sec_running_flag'])   $s2 = '실행됨'; else $s2 = '실행안됨';
  $now = time();
  $diff1 = $now - $row['ts1'];
  $diff2 = $now - $row['ts2'];
  $h1 = getHumanTime($diff1).'전';
  $h2 = getHumanTime($diff2).'전';
  print<<<EOS
<tr>
<th>보안체크 결과</th>
<td>
설치여부:{$s1} ({$row['sec_installed_time']})({$h1})<br>
실행여부:{$s2} ({$row['sec_running_time']})({$h2})<br>
</td>
</tr>
EOS;
*/

  print<<<EOS
<tr>
<th>입력변경 일시</th>
<td>{$row['idate']}</td>
</tr>
EOS;

  print<<<EOS
</form>
</table>

<script src="/js/ajax_xmlhttp.js"></script>

<script>
function sf_1() {
  var form = document.form;
  form.submit();
}

var ip4_preset = 0;
function _sel_ip3(ip3,ip4) {

  if (ip4) ip4_preset = ip4;

  var form = document.form;
  var idx = form.ip3.selectedIndex;

  var v = form.ip3.options[idx].value;
  //alert(v);
  var url = "$env[self]?mode=getip4&subnet="+v;
  xmlHttp.open("GET", url, true);
  xmlHttp.onreadystatechange = _sel_ip3_cb;
  xmlHttp.send(null);
}

function _sel_ip3_cb() {
  var form = document.form;
  if (xmlHttp.readyState == 4) {
    var response = xmlHttp.responseText;
    //alert(response);
    eval(response);
    //alert(option1);
    //alert(option2);
    //alert(option3);
    form.ip4.length = 0;
    for (var i = 0; i < option1.length; i++) {
      v = option1[i];
      if (option2[i] == "" && option3[i] == "") {
        d = "" + v;
      } else {
        d = "" + v + " (" + option2[i] + " " + option3[i] + ")";
      }
      form.ip4.options.add(new Option(d, v));
    }

    if (ip4_preset) {
      _preset_ip4(ip4_preset);
      ip4_preset = 0;
    }

  }
}

function _preset_ip4(v) {
  //alert(v);
  var form = document.form;
  var ip4 = form.ip4;
  var l = form.ip4.options.length;
  for (i = 0; i < l; i++) {
    if (ip4.options[i].value == v) {
      //alert(i);
      break;
    }
  }
  //alert(i);
  ip4.selectedIndex = i; 
}

// 고정IP, 유동IP 라디오 버튼
function clk_staticip() {
  var form = document.form;
  if (form.staticip[0].value==1 && form.staticip[0].checked) {
    document.all['ip3'].disabled = false;
    document.all['ip4'].disabled = false;
    //form.ip3.diabled = false;
    //form.ip4.diabled = false;
  } else if (form.staticip[1].value==0 && form.staticip[1].checked) {
    //form.ip3.diabled = true;
    //form.ip4.diabled = true;
    document.all['ip3'].disabled = true;
    document.all['ip4'].disabled = true;
  }
}

</script>
EOS;

  if ($row['ip3']!='' and $row['ip4']!='') {
    print<<<EOS
<script>
_sel_ip3($row[ip3],$row[ip4]);
</script>
EOS;
  }

  if ($mode == 'add') {
    AdminPageTail();
    exit;
  }

  ParagraphTitle('보안점검 기록', 1);
  $mac = $row['mac'];
  //dd($mac);
  $info = security_check_log_info($mac);
  //dd($info);

  print<<<EOS
<table class='main'>
<tr>
<th>보안점검시작날짜</th>
<th>확인란</th>
<th>확인시각</th>
<th>MAC</th>
</tr>
EOS;
  foreach ($info as $row) {
    $cfm = $row['confirm1'];
    if ($cfm == '1') $cfm = '확인'; else $cfm = '';
    print<<<EOS
<tr>
<td class='c'>{$row['start_date']}</td>
<td class='c'>{$cfm}</td>
<td class='c'>{$row['time1']}</td>
<td class='c'>{$row['mac']}</td>
</tr>
EOS;
  }
  print<<<EOS
</table>
EOS;



  AdminPageTail();
  exit;
}

// 별표 표시 ajax 처리
if ($mode == 'setstar') {

  $id = $form['id'];
  $toset = $form['toset'];

  $qry = "UPDATE ipdb SET checkStar='$toset' WHERE id='$id'";
  $ret = db_query($qry);
  exit;
}
### }}}

  AdminPageHead();

  $f_name = $form['name'];
  $f_memo = $form['memo'];
  $f_ostype = $form['ostype'];

  $f_ip = $form['ip'];

  $f_mac = $form['mac'];

  $f_macempty = $form['macempty'];
  if ($f_macempty) $chk1 = " checked"; else $chk1 = '';

  $f_bflag = $form['bflag'];
  $vs = array('', '1','0');
  $ds = array('::전체::','차단된 것만','차단 안된 것만');
  $n = count($vs);
  $opts = "";
  for ($i = 0; $i < $n; $i++) {
    $v = $vs[$i]; $d = $ds[$i];
    if ($f_bflag == $v) $sel = " selected"; else $sel = '';
    $opts .= "<option value='$v'$sel>$d</option>";
  }
  $bflag_opts = $opts;

  $f_staticip = $form['staticip'];
  $vs = array('', '1','0');
  $ds = array('::전체::','고정IP','유동IP');
  $n = count($vs);
  $opts = "";
  for ($i = 0; $i < $n; $i++) {
    $v = $vs[$i]; $d = $ds[$i];
    if ($f_staticip == $v) $sel = " selected"; else $sel = '';
    $opts .= "<option value='$v'$sel>$d</option>";
  }
  $staticip_opts = $opts;


  print<<<EOS
<table class='main'>
<form name='search_form' action='$env[self]' method='post'>
<tr>
<td>
<input type='button' value='확인' style='width:50px;height:50px;' onclick='sf_1()'>
<input type='hidden' name='mode' value='search'>
<input type='hidden' name='page' value='{$form['page']}'>
 </td>
 <td>
EOS;

  $depts = get_depts_list();
  $list = array('=전체=:all');
  foreach ($depts as $d) $list[] = $d;
  $preset = $form['dept']; if ($preset=='') $preset = 'all';
  $opt = option_general($list, $preset);
  $html = select_element('dept', $opt);
  print label_fe('부서', $html);

  $net_subnets = $conf['subnets'];
  $netclass = $conf['net_class'];
  $list = array('=전체=:all');
  foreach ($net_subnets as $n) {
    $title = $conf['subnet_title'][$n];
    $s = "$netclass.$n - $title:$n";
    $list[] = $s;
  }
  $preset = $form['subnet']; if ($preset=='') $preset = 'all';
  $opt = option_general($list, $preset);
  $html = select_element('subnet', $opt);
  print label_fe('서브넷', $html);

  print<<<EOS
사용자:<input type='text' name='name' size='10' value='{$form['name']}'
 onkeypress='keypress_text()'
 onclick='this.select()'>
EOS;

  print<<<EOS
OS종류:<input type='text' name='ostype' size='10' value='{$form['ostype']}'
 onkeypress='keypress_text()'
 onclick='this.select()'>
EOS;

  print<<<EOS
메모:<input type='text' name='memo' size='20' value='{$form['memo']}'
 onkeypress='keypress_text()'
 onclick='this.select()'>
<br>
EOS;

  $f_ip = $form['ip'];
  print<<<EOS
IP:<input type='text' name='ip' size='20' value='$f_ip'
 onkeypress='keypress_text()'
 onclick='this.select()'>
EOS;

  $f_mac = $form['mac'];
  print<<<EOS
MAC:<input type='text' name='mac' size='20' value='$f_mac'
 onkeypress='keypress_text()'
 onclick='this.select()'>
(<input type='checkbox' name='macempty'$chk1>미입력)
EOS;

  print<<<EOS
차단:<select name='bflag'>$bflag_opts</select>
<br>
EOS;

  print<<<EOS
고정/유동:<select name='staticip'>$staticip_opts</select>
EOS;

  $f_sflag = $form['sflag'];
  $vs = array('', '1','0');
  $ds = array('::전체::','보안점검대상','보안점검제외');
  $n = count($vs);
  $opts = "";
  for ($i = 0; $i < $n; $i++) {
    $v = $vs[$i]; $d = $ds[$i];
    if ($f_sflag == $v) $sel = " selected"; else $sel = '';
    $opts .= "<option value='$v'$sel>$d</option>";
  }
  $sflag_opts = $opts;


  print<<<EOS
보안점검:<select name='sflag'>$sflag_opts</select>
EOS;

  $f_sflag = $form['sflag2'];
  $vs = array('', '1','0');
  $ds = array('::전체::','보안점검 시작','보안점검 미시작');
  $n = count($vs);
  $opts = "";
  for ($i = 0; $i < $n; $i++) {
    $v = $vs[$i]; $d = $ds[$i];
    if ($f_sflag == $v) $sel = " selected"; else $sel = '';
    $opts .= "<option value='$v'$sel>$d</option>";
  }
  $sflag2_opts = $opts;
  print<<<EOS
보안점검 시작:<select name='sflag2'>$sflag2_opts</select>
EOS;

  $html = sort_select($sort_info, $fn='sort');
  print label_fe('정렬', $html);

  $list = array('=전체=:all','유선:1','무선:2');
  $preset = $form['wireless']; if ($preset=='') $preset = 'all';
  $opt = option_general($list, $preset);
  $html = select_element('wireless', $opt);
  print label_fe('유무선', $html);

  $f_dtype = $form['dtype'];
  $devlist = $conf['dtype'];
  $opts = "<option value=''>::전체::</option>";
  $opts .= "<option value='null'>::미입력::</option>";
  $n = count($devlist);
  for ($i = 0; $i < $n; $i++) {
    $dev = $devlist[$i];
    if ($f_dtype == $dev) $sel = " selected"; else $sel = '';
    $opts .= "<option value='$dev'$sel>$dev</option>";
  }
  $dtype_opts = $opts;
  print<<<EOS
장비분류:<select name='dtype'>$dtype_opts</select>
EOS;

  $ipp = get_ipp(20,$min=10,$max=500);
  $opts = option_ipp($ipp, array(10,20,50,100,200,500));
  $html = select_element('ipp', $opts).'건';
  print label_fe('출력', $html);

  $fck = array(); // field check '' or ' checked'
  fck_init($fck, $defaults='2,6,7,9,11', $max=50);
  print<<<EOS
<div>
<label><input type='checkbox' name='fd1' {$fck[1]}>ARP-MAC</label>
<label><input type='checkbox' name='fd2' {$fck[2]}>마지막 발견</label>
<label><input type='checkbox' name='fd3' {$fck[3]}>실제MAC</label>
<label><input type='checkbox' name='fd4' {$fck[4]}>제조사</label>
<label><input type='checkbox' name='fd5' {$fck[5]}>입력/변경일시</label>
<label><input type='checkbox' name='fd6' {$fck[6]}>부서</label>
<label><input type='checkbox' name='fd7' {$fck[7]}>사용자</label>
<label><input type='checkbox' name='fd9' {$fck[9]}>메모</label>
<label><input type='checkbox' name='fd10' {$fck[10]}>유무선</label>
<label><input type='checkbox' name='fd11' {$fck[11]}>장비분류</label>
<label><input type='checkbox' name='fd13' {$fck[13]}>OS종류</label>
<label><input type='checkbox' name='fd12' {$fck[12]}>차단사유</label>
<label><input type='checkbox' name='fd15' {$fck[15]}>점수</label>
<label><input type='checkbox' name='fd16' {$fck[16]}>DRM사용</label>
</div>
EOS;

  $cb = checkbox_general('debug', $form['debug'], '디버깅');
  print label_fe('옵션', $cb);

  print<<<EOS
 </td>
</tr>
</form>
</table>

<script>
function keypress_text() {
  if (event.keyCode != 13) return;
  sf_1();
}
function sf_1() {
  var form = document.search_form;
  form.submit();
}
</script>
EOS;

if ($mode != 'search') { AdminPageTail(); exit; }
  _debug($form);

  $b1 = button_general('선택항목 삭제', 0, "deleteBtn()", 'background-color:yellow;', $class='');
  $b4 = button_general('전체선택', 0, "select_all()", $style='', $class='');
  $b2 = button_general('보안점검 시작', 0, "setsflagBtn()", $style='', $class='');
  $b3 = button_general('보안점검 미시작', 0, "setsflagBtn(0)", $style='', $class='');
  $b5 = button_general('점수초기화', 0, "resetPoint()", $style='', $class='');
  $btns = button_box($b1, $b4, $b2, $b3, $b5);

  print<<<EOS
{$conf['html_notice1']}

$btns

<script>
var  page_loaded = false;
</script>
EOS;

  $hdr = "번호,선택";
  $hdr .= ",표시,IP,IP구분";
  if ($form['fd1']) $hdr .= ',ARP-MAC';
  if ($form['fd2']) $hdr .= ',마지막 발견';
  if ($form['fd3']) $hdr .= ',실제MAC';
  if ($form['fd4']) $hdr .= ',제조사';
  if ($form['fd5']) $hdr .= ',입력/변경일시';
  if ($form['fd6']) $hdr .= ',부서';
  if ($form['fd7']) $hdr .= ',사용자';
  if ($form['fd9']) $hdr .= ',메모';
  if ($form['fd10']) $hdr .= ',유무선';
  if ($form['fd11']) $hdr .= ',장비분류';
  if ($form['fd13']) $hdr .= ',OS종류';
  $hdr .= ',차단';
  if ($form['fd12']) $hdr .= ',차단사유';
  if ($form['fd15']) $hdr .= ',점수';
  if ($form['fd16']) { $hdr .= ',DRM사용,DRM사유'; }
  $hdr .= ',보안<br>점검<br>대상';
  $hdr .= ',보안<br>점검<br>시작';
  $hdr .= ',수정,삭제';

  $w = array('1');
  sql_where_match($w, 'subnet', 'i.ip3');
  sql_where_match($w, 'dept', 'i.dept');
  sql_where_like($w, 'name', 'i.name');
  sql_where_like($w, 'memo', 'i.memo');
  sql_where_like($w, 'ostype', 'i.ostype');

  $v = $form['dtype'];
  if ($v != '') {
    if ($v == 'null') $w[] = "(i.dtype='' or i.dtype is null)"; // 미입력
    else sql_where_match($w, 'dtype', 'i.dept');
  }

  $f_ip = $form['ip'];
  $f_ipmatch = $form['ipmatch'];
  if ($f_ip != '') {
    if ($f_ipmatch == '1') {
      $w[] = "(i.ip='$f_ip')";
    } else {
      $w[] = "(i.ip LIKE '%$f_ip%')";
    }
  }

  sql_where_like($w, 'mac', 'i.mac');
  if ($form['macempty']) $w[] = "(i.mac is null OR i.mac='')";
  if ($form['bflag']) $w[] = "(i.bflag='1')";

  sql_where_match($w, 'staticip', 'i.staticip');
  if ($form['sflag']) $w[] = "(i.sflag='1')";
  if ($form['sflag2']) $w[] = "(i.sec_chk_sflag='1')";

  _debug($w);
  $sql_where = " WHERE ".join(" AND ", $w);

  $sql_order = sort_order($sort_info, $fn='sort');

  $sql_select = "SELECT i.*, p.point _point"
   .", CONCAT(SUBSTRING(realmac,1,2),'-',SUBSTRING(realmac,4,2),'-',SUBSTRING(realmac,7,2)) AS mac6"
   .", if(i.ds_usage_check=1, '사용', if(i.ds_usage_check=2,'미사용','')) _drm_use"
   .", i.ds_no_use_cause _drm_cause";

  $sql_from = " FROM ipdb i";
  $sql_join = " LEFT JOIN points p ON i.mac=p.mac";

  $ipp = get_ipp(20,$min=10,$max=500);
  $cpgr = new pager();
  $start = $cpgr->calc($sql_from, $sql_join, $sql_where, $ipp);
  $html = $cpgr->get($formname='search_form', $innerhtml='');
  print $html;

if ($form['fd4']) {
  $sql_join .= " LEFT JOIN oui ON i.mac6=oui.mac6";
  $sql_select .= ", oui.company";
# $qry = "SELECT oui.company, A.*"
#  ." FROM ($qry1) A"
#  ." LEFT JOIN oui ON A.mac6=oui.mac6";
}

  $qry = $sql_select.$sql_from.$sql_join.$sql_where.$sql_order;
  if ($mode != 'download') $qry .= " LIMIT $start,$ipp";

  _debug($qry);
  $ret = db_query($qry);

  $qs = rawurlencode($_SERVER['QUERY_STRING']);

  $cbm = new checkbox_multi();
  $cbm->setoption('화살표숨김');
  $cbm->option('form', 'form2');
  $cbm->option('select_count', 'select_count');
  $cbm->option('selected_list', 'selected_list');

  list($table_form_open, $table_form_close) = table_oc_form('mmdata', 'form2', $env['self'], 'post', 'maindata');
  print $table_form_open; # {{
  print table_head_general($hdr);
  print("<input type='hidden' name='mode' value=''>");
  print("<input type='hidden' name='mode2' value=''>");

  $cbids = array();
  $cnt = 0;
  while ($row = db_fetch($ret)) {
    $cnt++;
    //dd($row);
    $no = $id = $row['id'];

    $dat = array(); $cls = array();

    $cbids[] = $id;

    $html = _star_check($row['checkStar'], $id);
    $cls[] = ''; $dat[] = $html;

    $ip = $row['ip'];
    $ipnow = $row['ipnow'];
    $ip_s = "$ip";
    if ($row['secondip']) { $ip_s .= "<br>($row[secondip])"; }
    if ($ipnow) { if ($ip != $ipnow) $ip_s .= "<br>*$ipnow"; }
    $cls[] = ''; $dat[] = $ip_s;

         if ($row['staticip']=='1') $str = "고정";
    else if ($row['staticip']=='0') $str = "유동";
    else $str = '';
    $cls[] = ''; $dat[] = $str;

    $mac = $row['mac'];
    #if ($mac == '') {
    #  $mac_s="";
    #} else {
    #  $mac_s = $mac;
    #  $qry2 = "select count(*) as count from ipdb where mac='$mac'";
    #  $ret2 = db_query($qry2);
    #  $row2 = db_fetch($ret2);
    #  if ($row2['count'] >= 2) {
    #    $mac_s .= "<font color='red'>(중복)</font>";
    #  }
    #}
    if ($form['fd1']) {
      $cls[] = ''; $dat[] = $mac;
    }

    if ($form['fd2']) { // 마지막 발견
      $udate = $row['udate'];
      $us = _mktime_date_string($udate);
      $now = time();
      $d = $now - $us;
      $ds = getHumanTime($d);
      $str = "{$ds}전";
      $cls[] = ''; $dat[] = $str;
    }

    if ($form['fd3']) { // 실제MAC
      $realmac = $row['realmac'];
      $realmac_s = $realmac;
      if ($mac != $realmac) { $realmac_s = "<font color=red>$realmac</font>"; }
      $cls[] = ''; $dat[] = $realmac_s;
    }
    if ($form['fd4']) { $cls[] = ''; $dat[] = $row['company']; } // 제조사
    if ($form['fd5']) { $cls[] = ''; $dat[] = $row['idate']; } // 입력/변경일시
    if ($form['fd6']) { $cls[] = ''; $dat[] = $row['dept']; } // 부서
    if ($form['fd7']) { $cls[] = 'l'; $dat[] = $row['name']; } // 사용자
    if ($form['fd9']) { $cls[] = 'l'; $dat[] = $row['memo']; } // 메모
    if ($form['fd10']) {
      if ($row['wireless']) $w = '무선'; else $w = '*유선';
      $cls[] = ''; $dat[] = $w;
    }
    if ($form['fd11']) { $cls[] = ''; $dat[] = $row['dtype']; } // 장비분류
    if ($form['fd13']) { $cls[] = ''; $dat[] = $row['ostype']; } // OS종료

    $bflag = $row['bflag'];
    if ($bflag) $bflag_s = '차단'; else $bflag_s = '-';
    $cls[] = ''; $dat[] = $bflag_s;

    if ($form['fd12']) { // 차단사유
      $bflag = $row['bflag'];
      if ($bflag) $bcode = $row['bcode']; else $bcode = '';
      $cls[] = ''; $dat[] = $bcode;
    }

    if ($form['fd15']) { $cls[] = ''; $dat[] = $row['_point']; } // 점수
    if ($form['fd16']) { // 문서보안 사용
    $cls[] = ''; $dat[] = $row['_drm_use'];
    $cls[] = ''; $dat[] = $row['_drm_cause'];
    }

    $sflag = $row['sflag'];
    if ($sflag) $sflag_s = '<font color=blue>포함</font>';
    else $sflag_s = '<font color=red>제외</font>';
    $cls[] = ''; $dat[] = $sflag_s;

    $sflag2 = $row['sec_chk_sflag'];
    if ($sflag2) $sflag2_s = '<font color=blue>시작</font>';
    else $sflag2_s = '<font color=red>미시작</font>';
    $cls[] = ''; $dat[] = $sflag2_s;

    $s = span_link2('수정', "_edit('$id', this)");
    $cls[] = ''; $dat[] = $s;

    $s = span_link2('삭제', "_delete('$id', this)");
    $cls[] = ''; $dat[] = $s;

    $tr = $cbm->get_tr($no);
    $attr = $cbm->get_td_drag_attr($no);
    $checkbox = $cbm->checkbox($no);
    print("$tr");
    print("<td $attr>$cnt</td>");
    print("<td>$checkbox</td>");
    print table_data_general($dat, $cls, false, false);
    print("</tr>");
  }
  print $table_form_close; # }}
  print $cbm->get_script();
  print $cbm->get_style();

  if ($form['debug']) hiddenframe(1, 'hiddenframe', 600, 600);
  else hiddenframe(0, 'hiddenframe', 600, 600);

  $cbids2 = join(',',$cbids);
  print<<<EOS
<script>
var cbids = [$cbids2];
function _copy(str) {
  clipboardData.setData("Text", str);
  alert('클립보드에 복사되었습니다.');
}

function _click_cb(id) {
  ignore_tr_event = true;
  var cbid = "cb_"+id;
  var trid = "tr_"+id;
  var tr = document.getElementById(trid);
  var cb = document.getElementById(cbid);
  if (cb.checked) tr.style.backgroundColor='#ddffdd'; //green
  else tr.style.backgroundColor='#ffffff'; //white
}

function setsflagBtn(sflag) {
  var c = select_count();
  if (c == 0) { alert('선택한 것이 없습니다.'); return; }
  var msg = "선택항목 "+c+"건을 적용할까요?";
  if (!confirm(msg)) return;

  form2.target = 'hiddenframe';
  form2.mode.value = 'doset';
  form2.mode2.value = sflag;
  form2.submit();
}

function resetPoint() {
  var c = select_count();
  if (c == 0) { alert('선택한 것이 없습니다.'); return; }
  var msg = "선택항목 "+c+"건을 적용할까요?";
  if (!confirm(msg)) return;

  form2.target = 'hiddenframe';
  form2.mode.value = 'resetpoint';
  form2.submit();
}

function deleteBtn() {
  var c = select_count();
  if (c == 0) {
    alert('선택한 것이 없습니다.'); return;
  }
  var msg = "선택항목 "+c+"건을 삭제할까요?";
  if (!confirm(msg)) return;

  form2.target = 'hiddenframe';
  form2.mode.value = 'dodelete';
  form2.submit();
}

function _edit(id,span) {
  var url = "$env[self]?mode=edit&id="+id;
  span.style.backgroundColor = '#ff8888';
  var option = "width=700,height=600,scrollbars=1,resizable=1";
  open(url, '', option);
}
function _delete(id,span) {
  span.style.backgroundColor = '#ff8888';
  if (!confirm('삭제할까요?')) return;
  hiddenframe.location = "$env[self]?mode=delete&id="+id;
}

function _onload() {
  page_loaded = true;
  console.log("page loaded");
}

if (window.addEventListener) {
  window.addEventListener("load", _onload, false);
} else if (document.attachEvent) {
  window.attachEvent("onload", _onload);
}
</script>
EOS;

  print _star_script();

  AdminPageTail();
  exit;

?>
