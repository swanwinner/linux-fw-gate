<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");

  $_TABLE = 'ipinput';

### {{{ modes

if ($mode == 'doadd') {

  $ip = $form['ip'];
  $mask = $form['mask'];
  $port = $form['port'];
  $memo = $form['memo'];

  $qry = "INSERT INTO $_TABLE SET"
     ." ip='$ip',mask='$mask',port='$port',memo='$memo'"
     .",idate=NOW()"
     ;
  $ret = db_query($qry);

  $url = "$env[self]";
  Redirect($url);
  exit;
}

// 새창에서 로딩됨
if ($mode == 'doedit') {

  $ip = $form['ip'];
  $mask = $form['mask'];
  $port = $form['port'];
  $memo = $form['memo'];

  $id = $form['id'];

  $qry = "UPDATE $_TABLE SET"
     ." ip='$ip',mask='$mask',port='$port',memo='$memo'"
     .",idate=NOW()"
     ." WHERE id='$id'"
     ;

  $ret = db_query($qry);

  print<<<EOS
<script>
opener.document.location.reload();
window.close();
</script>
EOS;
  exit;
}

// iframe 안에 로딩됨
if ($mode == 'delete') {

  $id = $form['id'];

  $qry = "DELETE FROM $_TABLE WHERE id='$id'";
  $ret = db_query($qry);

  print<<<EOS
<script>
parent.document.location.reload();
</script>
EOS;
  exit;
}



if ($mode == 'add' or $mode == 'edit') {

  $qs = $form['qs'];

  if ($mode == 'add') {
    $nextmode = 'doadd';
    $hidden = "";
    $hidden2 = "<input type='hidden' name='qs' value='$qs'>";
    $title = '신규 등록';
  } else if ($mode == 'edit') {
    $id = $form['id'];
    $qry = "SELECT * FROM $_TABLE WHERE id='$id'";

    $ret = db_query($qry);
    $row = db_fetch($ret);
    $nextmode = 'doedit';
    $hidden = "<input type='hidden' name='id' value='$id'>";
    $hidden2 = "<input type='hidden' name='qs' value='$qs'>";
    $title = '정보 수정';
  }

  AdminPageHead($title);
  ParagraphTitle($title);

  print<<<EOS
<style>
table.main { border:0px solid red; border-collapse:collapse; }
table.main th, table.main td { border:1px solid #999; padding:5 5 5 5 px; }
table.main th { background-color:#eeeeee; font-weight:bold; text-align:center; }
table.main td.c { text-align:center; }
table.main td.r { text-align:right; }
</style>

<table class='main' width='400'>
<form name='form' action='$env[self]' method='post'>
EOS;

  print<<<EOS
<tr>
 <th>IP</th>
 <td>
<input type='text' name='ip' size='20' value='$row[ip]'>
/
<input type='text' name='mask' size='4' value='$row[mask]'>
</td>
</tr>
<tr>
 <th>Port</th>
 <td><input type='text' name='port' size='30' value='$row[port]'></td>
</tr>

<tr>
 <th>메모</th>
 <td><input type='text' name='memo' size='60' value='$row[memo]'></td>
</tr>
EOS;

  print<<<EOS
<tr>
 <td colspan='2' align='center'>
<input type='hidden' name='mode' value='$nextmode'>
$hidden
$hidden2
<button onclick='submit_form()'
  style="border:#ccc 1px solid; background:#fff; width:100; height:30;">저장</button>
 </td>
</tr>

</form>
</table>

<script>
function submit_form() {
  var form = document.form;
  form.submit();
}
</script>
EOS;

  AdminPageTail();
  exit;
}

if ($mode == 'apply') {

  $qry = "SELECT i.* FROM $_TABLE i";
  $ret = db_query($qry);

  $cnt = 0;
  while ($row = db_fetch($ret)) {
    $cnt++;
    //print_r($row);

    $id = $row['id'];

    $ip = $row['ip'];
    $mask = $row['mask'];
    $ip_s = "$ip/$mask";
    $port = $row['port'];

    print<<<EOS
iptables -A INPUT -p tcp -s $ip_s --dport $port -j ACCEPT
<br>
EOS;
  }

  exit;
}

### }}} modes


  AdminPageHead('접근 정책');

  print<<<EOS
<style>
table.main { border:0px solid red; border-collapse:collapse; }
table.main th, table.main td { border:1px solid #999; padding:2px 2px 2px 2px; }
table.main th { background-color:#eeeeee; font-weight:bold; text-align:center; }
table.main td.c { text-align:center; }
table.main td.r { text-align:right; }
table.main td.l { text-align:left; }
table.main td.ls { text-align:left; font-size:5pt; }
</style>
EOS;


  $f_sort = $form['sort'];
  $vs = array('', '1','2','5');
  $ds = array('::기본::','IP','용도','최근변경');
  $n = count($vs);
  $opts = "";
  for ($i = 0; $i < $n; $i++) {
    $v = $vs[$i]; $d = $ds[$i];
    if ($f_sort == $v) $sel = " selected"; else $sel = '';
    $opts .= "<option value='$v'$sel>$d</option>";
  }
  $sort_opts = $opts;

  $f_title = $form['title'];

  $f_ip = $form['ip'];

  print<<<EOS
<table border=0>
<form name='form' action='$env[self]' method='get'>
<tr>
<td><input type='button' value='입력' style='width:100; height:30;' onclick='_add()'></td>
<td><input type='button' value='적용' style='width:100; height:30;' onclick='_apply()'></td>
</tr>
</form>
</table>

<script>
function _add() {
  url = "$env[self]?mode=add";
  document.location = url;
}
function _apply() {
  url = "$env[self]?mode=apply";
  document.location = url;
}

</script>
EOS;



  print<<<EOS
<table class='main'>
<tr>
 <th>번호</th>
 <th>IP</th>
 <th>Port</th>
 <th>메모</th>
 <th>수정</th>
 <th>삭제</th>
 <th>입력일시</th>
</tr>
EOS;

  $sql_where = " WHERE 1";

  $sql_order = "ORDER BY i.idate DESC";

  $qry = "SELECT i.* FROM $_TABLE i $sql_where $sql_order";

  $ret = db_query($qry);

  $qs = rawurlencode($_SERVER['QUERY_STRING']);

  $cnt = 0;
  while ($row = db_fetch($ret)) {
    $cnt++;

    $id = $row['id'];

    $delete=<<<EOS
<span onclick="_delete('$id')" style='cursor:pointer'>삭제</span>
EOS;

    $ip = $row['ip'];
    $mask = $row['mask'];

    $ip_s = "$ip/$mask";

    $port = $row['port'];

    print<<<EOS
<tr onmouseover="_mover(this,1)" onmouseout="_mover(this,0)">
 <td class='c'>$cnt</td>
 <td class='l'>$ip_s</td>
 <td class='c'>$port</td>
 <td class='c'>$row[memo]</td>
 <td class='c'><span onclick="_edit('$id')" style='cursor:pointer'>수정</span></td>
 <td class='c'><span onclick="_delete('$id')" style='cursor:pointer'>삭제</span></td>
 <td class='c'>$row[idate]</td>
</tr>
EOS;
  }
  print<<<EOS
</table>
<iframe name='hiddenframe' width=0 height=0 style='display:none;'></iframe>

<script>
function _mover(tr, mouseover) {
  if (mouseover) {
    tr.style.background = "#ffeeee";
  } else {
    tr.style.background = "#ffffff";
  }
}

function _edit(id) {
  var url = "$env[self]?mode=edit&id="+id;
  open(url);
}
function _delete(id) {
  if (!confirm('삭제할까요?')) return;
  hiddenframe.location = "$env[self]?mode=delete&id="+id;
}

</script>

EOS;

  AdminPageTail();
  exit;

?>
