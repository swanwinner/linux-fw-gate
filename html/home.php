<?php

  include_once("path.php");
  include_once("$env[prefix]/inc/common.php");
  include_once("$env[prefix]/inc/class.checkbox_multi2.php");
  include_once("$env[prefix]/inc/class.pager.php");
  include_once("$env[prefix]/inc/class.dao2.php"); 

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

function _dd($msg) {
       if (is_string($msg)) print($msg);
  else if (is_array($msg)) { print("<pre>"); print_r($msg); print("</pre>"); }
  else print_r($msg);
}

function _option_bcode($preset) {
  global $conf;
  $opts = "";
  foreach ($conf['bcode_list'] as $v=>$t) {
    if ($v == $preset) $sel = ' selected'; else $sel = '';
    $opts .= "<option value='$v'$sel>$t</option>";
  }
  return $opts;
}

function _star_check($preset, $id) {
  if ($preset) $src = "/img/star.png";
  else $src = "/img/star2.png";
  $imgid = "star_$id";
  $html = "<img src='$src' id='$imgid' sval='$preset' onclick=\"_setstar('$id','$imgid')\">";
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

  var toset;
  if (sval == '1') toset = 0; else toset = 1;

  var url = "$env[self]?mode=setstar&id="+id+"&toset="+toset;
  $.ajax({type:'GET', url:url, success: function(data) {
    //console.log(data);
    if (toset) { img.src = '$src_on'; } else { img.src = '$src_off'; }
    img.setAttribute('sval', toset);
  }});
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

} else if ($mode == 'doedit') {

dd($form);
  $id = $form['id'];

  $dao = new dao('ipdb', 'ipdb');
  //$dao->boilerplate(); // 
  $set = array();
  $set['idate'] = array('now()', 'noq');

  $staticip = $form['staticip'];
  $secondip = $form['secondip'];
  $set['staticip'] = array($staticip, '');
  if (!($staticip == '1' or $staticip == '0')) $staticip = '0';
  if ($staticip == '1') {
    $ip3 = $form['ip3'];
    $ip4 = $form['ip4'];
    $ip = sprintf("%s.%s.%s", $conf['net_class'], $ip3, $ip4);
    $set['ip3'] = array($ip3, '');
    $set['ip4'] = array($ip4, '');
    $set['ip'] = array($ip, '');
    $set['secondip'] = array($secondip, '');
  }
  $set['ostype'] = array($form['ostype'], '');
  $set['name'] = array($form['name'], '');
  $set['memo'] = array($form['memo'], '');
  $set['dept'] = array($form['dept'], '');

  $bflag = $form['bflag']; if ($bflag != '') $bflag = '1'; else $bflag = '0';
  $set['bflag'] = array($bflag, '');

  $use_the_ip = $form['use_the_ip'];
  if ($use_the_ip) $use_the_ip = '1'; else $use_the_ip = '0';
  $set['use_the_ip'] = array($use_the_ip, '');

  $checkStar = $form['checkStar'];
  $set['checkStar'] = array($checkStar, '');

  $dtype = $form['dtype']; $set['dtype'] = array($dtype, '');

  $set['wireless'] = array($form['wireless'], '');
  $set['sflag'] = array($form['sflag'], '');
  $set['sec_chk_sflag'] = array($form['sec_chk_sflag'], '');
  $set['bcode'] = array($form['bcode'], '');
  $set['natpubip'] = array($form['natpubip'], '');
  $set['ds_usage_check'] = array($form['ds_usage_check'], '');
  $set['ds_no_use_cause'] = array($form['ds_no_use_cause'], '');

  $set['id'] = $id;
  $qry = $dao->get_update_query($set, $keycol='id');
dd($qry);
  $ret = db_query($qry);
 //exit;

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

} else if ($mode == 'edit') {

  $qs = $form['qs'];

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
    $hidden = "<input type='hidden' name='id' value='$id'>";
    $hidden2 = "<input type='hidden' name='qs' value='$qs'>";
    $title = '정보 수정';
    list($a, $b, $row['ip3'], $row['ip4']) = preg_split("/\./", $row['ip']);

  AdminPageHead($title, $menu=false);
  ParagraphTitle($title);

  list($table_form_open, $table_form_close)
    = table_oc_form('main', 'form', $env['self'], 'post', 'maindata', "style='margin-top:30px;'");
  print $table_form_open; # {{
  print<<<EOS
<tr>
 <td colspan='2' align='center'>
<button onclick='sf_1()' style="width:150px; height:30px;">저장</button>
&nbsp;&nbsp;&nbsp;&nbsp;
<input type='button' onclick="window.close()" value='취소' style="width:40; height:20;">
<input type='hidden' name='mode' value='doedit'>
$hidden
$hidden2
 </td>
</tr>
EOS;

//dd($row);
  $list = array('고정IP설정:1','유동IP설정:0');
  $preset = $row['staticip']; if ($preset=='') $preset = '1';
  $html = radio_list_general('staticip', $list, $preset);
  print table_2column('고정/유동 IP', $html);

  $netclass = $conf['net_class'];
  $net_subnets = $conf['subnets'];
  $net_subnet_title = $conf['subnet_title'];
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

  //if ($row['staticip']==0) $dis = " disabled"; else $dis = "";
  $dis = '';
  $html=<<<EOS
$netclass.<select name='ip3' onchange='_sel_ip3()' $dis>$subnet_opts</select>
.<select name='ip4'' $dis></select>
EOS;
  print table_2column('IP', $html);

  $html=<<<EOS
<input type='text' name='secondip' size='60' value='$row[secondip]'>
<p class=desc>프린터공유, 폴더공유 때문에 IP를 2개 할당하는 경우만 기입</p>
EOS;
  print table_2column('IP(secondary)', $html);

  $depts = get_depts_list();
  $opts = "<option value=''>::전체부서::</option>";
  $n = count($depts);
  for ($i = 0; $i < $n; $i++) {
    $dept = $depts[$i];
    if ($row['dept'] == $dept) $sel = " selected"; else $sel = '';
    $opts .= "<option value='$dept'$sel>$dept</option>";
  }
  $html = select_element('dept', $opts);
  print table_2column('부서', $html);

  $html = textinput_general('name', $row['name'], 60);
  print table_2column('사용자', $html);

  $list = array('유선:0','무선:1');
  $preset = $row['wireless']; if (!$preset) $preset = '0';
  $html = radio_list_general('wireless', $list, $preset);
  print table_2column('유선/무선', $html);

  $html = textinput_general('memo', $row['memo'], 60);
  print table_2column('메모', $html);

  $html = textinput_general('ostype', $row['ostype'], 60);
  print table_2column('OS종류', $html);

  $list = array('사용:1','미사용:2');
  $preset = $row['ds_usage_check']; if (!$preset) $preset = '2';
  $html = radio_list_general('ds_usage_check', $list, $preset);
  $html .= "사유:";
  $html .= textinput_general('ds_no_use_cause', $row['ds_no_use_cause'], 40);
  print table_2column('문서보안사용', $html);

  $list = $conf['dtype'];
  $preset = $row['dtype'];
  $opt = option_general($list, $preset);
  $html = select_element('dtype', $opt);
  print table_2column('장비분류', $html);

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
  $opts = _option_bcode($bcode);
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
  print<<<EOS
<tr>
<th>점수</th>
<td>
{$t1}점
<a href="secchk2.php?mac={$mac}" class='link'>[[점수변경]]</a>
</td>
</tr>
EOS;

  $s = "$row[mac] (제조사:$row[company])";
  print table_2column('ARP-MAC', $s);
  print table_2column('ARP-MAC', $row['realmac']);
  print table_2column('마지막 발견', $row['udate']);
  print table_2column('DRM 사용', $row['drm_time']);
  print table_2column('입력변경 일시', $row['idate']);

  print $table_form_close; # }}
  print<<<EOS
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

  $.ajax({type:'GET', url:url, success: function(data) {
    //console.log(data);
    eval(data);
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
  }});
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

  xkey_close_window();
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

  ##{{
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

  $ti = textinput_general('name', $form['name'], 10, 'keypress_text()');
  print label_fe('사용자', $ti, '', 0);

  $ti = textinput_general('ostype', $form['ostype'], 10, 'keypress_text()');
  print label_fe('OS종류', $ti, '', 0);

  $ti = textinput_general('memo', $form['memo'], 10, 'keypress_text()');
  print label_fe('메모', $ti, '', 1);

  $ti = textinput_general('ip', $form['ip'], 10, 'keypress_text()');
  print label_fe('IP', $ti, '', 0);

  $ti = textinput_general('mac', $form['mac'], 10, 'keypress_text()');
  $cb = checkbox_general('macempty', $form['macempty'], '미입력');
  print label_fe('MAC', $ti.$cb, '', 0);

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
  print<<<EOS
차단:<select name='bflag'>$bflag_opts</select>
<br>
EOS;

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
  $html = select_element('staticip', $opts);
  print label_fe('고정/유동', $html);

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
  $html = select_element('sflag', $opts);
  print label_fe('보안점검', $html);

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
  $html = select_element('sflag2', $opts);
  print label_fe('보안점검시작', $html);

  $html = sort_select($sort_info, $fn='sort');
  print label_fe('정렬', $html);

  $list = array('=전체=:all','유선:1','무선:2');
  $preset = $form['wireless']; if ($preset=='') $preset = 'all';
  $opt = option_general($list, $preset);
  $html = select_element('wireless', $opt);
  print label_fe('유무선', $html, '',1);

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
  $html = select_element('dtype', $dtype_opts);
  print label_fe('장비분류', $html);

  $ipp = get_ipp(20,$min=10,$max=1000);
  $opts = option_ipp($ipp, array(10,20,50,100,200,500,1000));
  $html = select_element('ipp', $opts).'건';
  print label_fe('출력', $html);

  $fck = array(); // field check '' or ' checked'
  fck_init($fck, $defaults='2,6,7,9,11', $max=50);
  $html = '';
  $html .= checkbox_general('fd1', $fck[1], 'ARP-MAC');
  $html .= checkbox_general('fd2', $fck[2], '마지막 발견');
  $html .= checkbox_general('fd3', $fck[3], '실제MAC');
  $html .= checkbox_general('fd4', $fck[4], '제조사');
  $html .= checkbox_general('fd5', $fck[5], '입력/변경일시');
  $html .= checkbox_general('fd6', $fck[6], '부서');
  $html .= checkbox_general('fd7', $fck[7], '사용자');
  $html .= checkbox_general('fd9', $fck[9], '메모');
  $html .= checkbox_general('fd10', $fck[10], '유무선');
  $html .= checkbox_general('fd11', $fck[11], '장비분류');
  $html .= "<br>".str_repeat('&nbsp;',8);
  $html .= checkbox_general('fd13', $fck[13], 'OS종류');
  $html .= checkbox_general('fd12', $fck[12], '차단사유');
  $html .= checkbox_general('fd15', $fck[15], '점수');
  $html .= checkbox_general('fd16', $fck[16], 'DRM사용');
  $html .= checkbox_general('fd17', $fck[17], '수정,삭제');
  print label_fe('표시', $html, '', 1);

  $cb = checkbox_general('debug', $form['debug'], '디버깅');
  print label_fe('옵션', $cb, '', 1);

  print<<<EOS
 </td>
</tr>
</form>
</table>
EOS;
  ##}}
  print<<<EOS
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

  print($conf['html_notice1']);

  $b1 = button_general('선택항목 삭제', 0, "deleteBtn()", 'background-color:yellow;', $class='');
  $b4 = button_general('전체선택', 0, "select_all()", $style='', $class='');
  $b2 = button_general('보안점검 시작', 0, "setsflagBtn()", $style='', $class='');
  $b3 = button_general('보안점검 미시작', 0, "setsflagBtn(0)", $style='', $class='');
  $b5 = button_general('점수초기화', 0, "resetPoint()", $style='', $class='');
  print button_box($b1, $b4, $b2, $b3, $b5);

  $w = array('1');
  sql_where_match($w, 'subnet', 'i.ip3');
  sql_where_match($w, 'dept', 'i.dept');
  sql_where_like($w, 'name', 'i.name');
  sql_where_like($w, 'memo', 'i.memo');
  sql_where_like($w, 'ostype', 'i.ostype');

  $v = $form['dtype'];
  if ($v != '') {
    if ($v == 'null') $w[] = "(i.dtype='' or i.dtype is null)"; // 미입력
    else sql_where_match($w, 'dtype', 'i.dtype');
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

  $ipp = get_ipp(20,$min=10,$max=1000);
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
  if ($form['fd17']) { $hdr .= ',수정,삭제'; }

  list($table_form_open, $table_form_close) = table_oc_form('mmdata', 'form2', $env['self'], 'post', 'maindata');
  print $table_form_open; # {{
  print table_head_general($hdr);
  print("<input type='hidden' name='mode' value=''>");
  print("<input type='hidden' name='mode2' value=''>");

  $cnt = 0;
  while ($row = db_fetch($ret)) {
    $cnt++;
    //dd($row);
    $no = $id = $row['id'];

    $dat = array(); $cls = array();

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
      $s = $udate;
      $ds = how_many_days_before($udate);
      if ($ds == 0) {
        $ds = how_many_seconds_before($udate, true);
      } else $ds = "{$ds}일";
      $s .= "({$ds}전)";
      $cls[] = ''; $dat[] = $s;
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

    if ($form['fd7']) {
      $s = span_link2($row['name'], "_edit('$id', this)");
      $cls[] = 'l'; $dat[] = $s;
    } // 사용자

    if ($form['fd9']) { $cls[] = 'l w200'; $dat[] = $row['memo']; } // 메모
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

    if ($form['fd17']) {
      $s = span_link2('수정', "_edit('$id', this)");
      $cls[] = ''; $dat[] = $s;
      $s = span_link2('삭제', "_delete('$id', this)");
      $cls[] = ''; $dat[] = $s;
    }

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

  print<<<EOS
<script>
var  page_loaded = false;
</script>
EOS;

  print<<<EOS
<script>
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
  if (c == 0) { alert('선택한 것이 없습니다.'); return; }
  var msg = "선택항목 "+c+"건을 삭제할까요?";
  if (!confirm(msg)) return;
  form2.target = 'hiddenframe';
  form2.mode.value = 'dodelete';
  form2.submit();
}

function _edit(id,span) {
  spanmark(span);
  var url = "$env[self]?mode=edit&id="+id;
  wopen3(url, '', 700,700);
}
function _delete(id,span) {
  spanmark(span);
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
