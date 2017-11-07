<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");


if ($mode == 'change') {
  $pw1 = $form['pw1'];
  $pw2 = $form['pw2'];

  if ($pw1 != $pw2) {
    iError('비밀번호가 일치하지 않습니다.'); exit;
  }

  $hash = md5($pw1);

  $qry = "UPDATE login SET password='$hash' WHERE username='user'";
  $ret = db_query($qry);

  print<<<EOS
<script>
alert('사용자 비밀번호가 변경되었습니다.');
document.location = "$env[self]";
</script>
EOS;
  exit;
}

if ($mode == 'change2') {
  $pw1 = $form['pw1'];
  $pw2 = $form['pw2'];

  if ($pw1 != $pw2) {
    iError('비밀번호가 일치하지 않습니다.'); exit;
  }

  $hash = md5($pw1);

  $qry = "UPDATE login SET password='$hash' WHERE username='admin'";
  $ret = db_query($qry);

  print<<<EOS
<script>
alert('관리자 비밀번호가 변경되었습니다.');
document.location = "$env[self]";
</script>
EOS;
  exit;
}


  AdminPageHead('비밀번호 변경');

  print<<<EOS
<style>
table.main { border:0px solid red; border-collapse:collapse; }
table.main th, table.main td { border:1px solid #999; padding:5 5 5 5 px; }
table.main th { background-color:#eeeeee; font-weight:bold; text-align:center; }
table.main td.c { text-align:center; }
table.main td.r { text-align:right; }
</style>
EOS;


  ParagraphTitle('관리자 비밀번호 변경');

  print<<<EOS
<style>
table.main { border:0px solid red; border-collapse:collapse; }
table.main th, table.main td { border:1px solid #999; padding:5 5 5 5 px; }
table.main th { background-color:#eeeeee; font-weight:bold; text-align:center; }
table.main td.c { text-align:center; }
table.main td.r { text-align:right; }
</style>
EOS;


  print<<<EOS
<table class='main'>
<form name='form2' method='post' action='$env[self]'>
<tr>
 <th>새 비밀번호</th>
 <td><input type='password' name='pw1' size='30'></td>
</tr>

<tr>
 <th>한번 더 입력</th>
 <td><input type='password' name='pw2' size='30'></td>
</tr>

<tr>
 <td colspan='2' align='center'>
<input type='hidden' name='mode' value='change2'>
<button onclick='submit_form2()'
  style="border:#ccc 1px solid; background:#fff; width:100; height:30;">확인</button>
 </td>
</tr>

</form>
</table>

<script>
function submit_form2() {
  var form = document.form2;

  if (form.pw1.value == '') {
    alert("비밀번호를 입력하세요"); form.pw1.focus(); return;
  }
  if (form.pw2.value == '') {
    alert("비밀번호를 입력하세요"); form.pw2.focus(); return;
  }
  if (form.pw1.value != form.pw2.value) {
    alert("비밀번호가 서로 같지 않습니다."); form.pw1.focus(); return;
  }

  form.submit();
}
</script>
EOS;

  AdminPageTail();
  exit;

?>
