<?php

  include("path.php");
  include("$env[prefix]/inc/common.login.php");


### {{{ modes
if ($mode == 'login') {

  //print_r($form); exit;

  $time = $form['time']; 

# // time 을 체크
# $now = time();
# $d  = $now - $time;
# if ($d > 0 && $d < 60) { 
#   // 로그인 폼으 로딩한 다음 60초안에 로그인을 해야함
# } else {
#   iError('로그인 오류입니다.'); exit;
# }

  // 비밀번호는 md5 hash 되서 넘어온다.
  $f_password = $form['pass'];

  $qry = "SELECT * FROM login WHERE username='admin'";
  $ret = db_query($qry);
  $lrow = db_fetch($ret);
  $pw = $lrow['password'];

  // $time 과 db 의 password 를 해쉬하여 비교
  //$hash = md5($pw);
  $hash = $pw;

  if ($f_password != $hash) {
    //print("$f_password $hash"); exit;
    iError('비밀번호가 틀렸습니다.'); exit;
  }

  // 세션 설정
  $_SESSION['adminlogin'] = true;
  $_SESSION['logined'] = true;

  $url = "/home.php";
  Redirect($url);
  exit;
}
### }}} modes


  $now = time();
  print<<<EOS
<html>
<head>
<title>IP등록</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<link rel="icon" href="/favicon.ico" type="image/x-icon">
<style type='text/css'> 
body {  font-size: 12px; font-family: 굴림,돋움,verdana;
  font-style: normal; line-height: 12pt;
  text-decoration: none; color: #333333;
}
table,td,th { font-size: 12px; font-family: 돋움,verdana; white-space: nowrap; }
</style>

<script src='/js/script.md5.js' type='text/javascript'></script>
 
</head>
<body>

<center>

<form action='$env[self]' method='post' name='form' onsubmit="return false">
관리자 로그인:<input type='password' name='pass' size='20' onkeypress='keypress_text()'><input
 type='button' value='확인' onclick="sf_1()" style='width:60;height:25;'>
<input type='hidden' name='mode' value='login'>
<input type='hidden' name='time' value='$now'>
</form>
접속IP: $_SERVER[REMOTE_ADDR]

</center>

<script>
function keypress_text() {
  if (event.keyCode != 13) return;
  sf_1();
}

function sf_1() {
  var form = document.form;

  // password hash
  form.pass.value = MD5(form.pass.value);
  form.submit();
}

function _onload() {
  document.form.pass.focus();
}

if (window.addEventListener) {
  window.addEventListener("load", _onload, false);
} else if (document.attachEvent) {
  window.attachEvent("onload", _onload);
}
</script>

</body>
</html>
EOS;
  exit;

?>
