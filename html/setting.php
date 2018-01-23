<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");


### {{{
if ($mode == 'save') {

  $v = $form['block_new_mac_address'];
  $qry = "UPDATE settings SET svalue='$v' WHERE skey='block_new_mac_address'";
  $ret = db_query($qry);

  $v = $form['security_check_start'];
  $qry = "UPDATE settings SET svalue='$v' WHERE skey='security_check_start'";
  $ret = db_query($qry);

  $v = $form['security_check_date'];
  if ($v != '') {
    $qry = "INSERT INTO settings (skey, svalue) VALUES ('security_check_date', '$v') ON DUPLICATE KEY UPDATE svalue='$v'";
    $ret = db_query($qry);
  }

  print<<<EOS
<script>
alert('저장 되었습니다.');
document.location = "$env[self]";
</script>
EOS;
  exit;
}
### }}}


  AdminPageHead('설정 변경');
  ParagraphTitle('설정 변경');

  // 셋팅을 읽음
  $setting = array();
  read_setting($setting);

/*
  print<<<EOS
<a href='setting.newmac.php'>신규 발견 맥주소 차단 설정</a><br>
EOS;
*/

  print<<<EOS
<table border='1' class='mmdata'>
<form name='form' action='$env[self]'>
EOS;

  $chk1 = $chk0 = '';
  if ($setting['block_new_mac_address'] == '1') $chk1 = ' checked';
  else if ($setting['block_new_mac_address'] == '0') $chk0 = ' checked';
  print<<<EOS
<tr>
<th>신규 발견 맥주소 차단</th>
<td class='l'>
<p><input type='radio' name='block_new_mac_address' value='1' id='bnma1'$chk1><label for='bnma1'>차단</label>
<p><input type='radio' name='block_new_mac_address' value='0' id='bnma0'$chk0><label for='bnma0'>허용</label>
</td>
</tr>
EOS;

  $chk1 = $chk0 = '';
  if ($setting['security_check_start'] == '1') $chk1 = ' checked';
  else if ($setting['security_check_start'] == '0') $chk0 = ' checked';
  print<<<EOS
<tr>
<th>보안점검</th>
<td class='l'>
<p><label><input type='radio' name='security_check_start' value='1' $chk1>시작</label>
<p><label><input type='radio' name='security_check_start' value='0' $chk0>중지</label>
</td>
</tr>
EOS;

  if ($setting['security_check_start'] == '1') {
    $dis = ' disabled';
  } else {
    $dis = '';
  }

  $v = $setting['security_check_date'];
  print<<<EOS
<tr>
<th>보안시작날짜</th>
<td class='l'>
<p><label><input type='text' name='security_check_date' value='$v' $dis>예)161107</label>
(보안점검시작 날짜하는 날짜)
</td>
</tr>
EOS;



  print<<<EOS
<tr>
<td colspan='2' align='center'>
<input type='hidden' name='mode' value='save'>
<input type='button' onclick='sf_1()' value='저장'>
</td>
</tr>
EOS;


  print<<<EOS
</form>
</table>
EOS;

  print<<<EOS
<script>
function sf_1() {
  var form = document.form;
  form.submit();
}
</script>
EOS;


  AdminPageTail();
  exit;

?>
