<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");


if ($mode == 'save') {

  $security_check_start = $form['security_check_start'];

  $qry = "UPDATE settings SET svalue='$security_check_start' WHERE skey='security_check_start'";
  $ret = db_query($qry);

  print<<<EOS
<script>
alert('저장 되었습니다.');
document.location = "$env[self]";
</script>
EOS;
  exit;
}



  AdminPageHead('설정 변경');
  ParagraphTitle('설정 변경');

  print<<<EOS
<style>
table.main { border:0px solid red; border-collapse:collapse; }
table.main th, table.main td { border:1px solid #999; padding:5 5 5 5 px; }
table.main th { background-color:#eeeeee; font-weight:bold; text-align:center; }
table.main td.c { text-align:center; }
table.main td.r { text-align:right; }
</style>
EOS;


  $setting = array();
  $qry = "SELECT * FROM settings";
  $ret = db_query($qry);
  while ($row = db_fetch($ret)) {
    //print_r($row);
    $skey = $row['skey'];
    $svalue = $row['svalue'];
    $setting[$skey] = $svalue;
  }
  //print_r($setting);


  print<<<EOS
<table class='main'>
<form name='form' method='post' action='$env[self]'>
EOS;

  $chk1 = $chk0 = '';
  if ($setting['security_check_start'] == '1') $chk1 = ' checked';
  else if ($setting['security_check_start'] == '0') $chk0 = ' checked';
  print<<<EOS
<tr>
 <th>보안점검</th>
 <td>
<label><input type='radio' name='security_check_start' value='1' $chk1>시작</label>
<label><input type='radio' name='security_check_start' value='0' $chk0>중지</label>
</td>
</tr>

<tr>
 <td colspan='2' align='center'>
<input type='hidden' name='mode' value='save'>
<button onclick='submit_form()' style="width:100; height:30;">저장</button>
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

?>
