<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");

### {{{

if ($mode == 'doadd') {
  //print_r($form); exit;

  $dept = $form['dept'];

  $qry = "INSERT INTO dept SET"
     ." dept='$dept',idate=now()"
     ;
  $ret = db_query($qry);

  $url = "$env[self]";
  Redirect($url);
  exit;
}

// 새창에서 로딩됨
if ($mode == 'doedit') {

  $dept = $form['dept'];
  $id = $form['id'];

  $qry = "UPDATE dept SET dept='$dept' WHERE id='$id'";
  $ret = db_query($qry);

  print<<<EOS
<script>
document.location = "$env[self]";
</script>
EOS;
  exit;
}


// iframe 안에 로딩됨
if ($mode == 'delete') {

  $id = $form['id'];

  $qry = "DELETE FROM dept WHERE id='$id'";
  $ret = db_query($qry);

  print<<<EOS
<script>
parent.document.location.reload();
</script>
EOS;
  exit;
}



if ($mode == 'edit') {

  $qs = $form['qs'];

  $id = $form['id'];
  $qry = "SELECT * FROM dept WHERE id='$id'";
  $ret = db_query($qry);
  $row = db_fetch($ret);

  $nextmode = 'doedit';

  $title = '정보 수정';
  list($a, $b, $c, $row['ip4']) = preg_split("/\./", $row['ip']);

  AdminPageHead('IP관리');
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

<tr>
 <th>부서명</th>
 <td>
<input type='text' name='dept' size='20' value='$row[dept]'>
 </td>
</tr>

<tr>
 <td colspan='2' align='center'>
<input type='hidden' name='mode' value='$nextmode'>
<input type='hidden' name='id' value='$id'>
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


### }}} modes


  AdminPageHead('부서등록');

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

  print<<<EOS
<table class='main'>
<form name='form' action='$env[self]' method='post'>

<tr>
 <th>부서명</th>
 <td><input type='text' name='dept' size='30'></td>
 <td>
<input type='hidden' name='mode' value='doadd'>
<button onclick='submit_form()' style="border:#ccc 1px solid; background:#fff; width:100; height:30;">입력</button>
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


  print<<<EOS
<table class='main'>
<tr>
<th>번호</th>
<th>부서</th>
<th>입력일시</th>
<th>수정</th>
<th>삭제</th>
</tr>
EOS;

  $qry = "SELECT * FROM dept ORDER BY dept";

  $ret = db_query($qry);

  $cnt = 0;
  while ($row = db_fetch($ret)) {
    $cnt++;
    //print_r($row);

    $id = $row['id'];

    print<<<EOS
<tr>
<td class='c'>$cnt</td>
<td class='c'>$row[dept]</td>
<td class='c'>$row[idate]</td>
<td class='c'><span onclick="_edit('$id')" style='cursor:pointer'>수정</span></td>
<td class='c'><span onclick="_delete('$id')" style='cursor:pointer'>삭제</span></td>
</tr>
EOS;
  }
  print<<<EOS
</table>
<iframe name='hiddenframe' width=0 height=0 style='display:none;'></iframe>

<script>
function _edit(id) {
  var url = "$env[self]?mode=edit&id="+id;
  document.location = url;
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
