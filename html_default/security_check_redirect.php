<?php

  // 보안점검을 위한 리다이렉션
  include_once("./path.php");
  include_once("$env[prefix]/inc/common.login.php");

  $mac = $form['mac'];

  // 보안점검 33년 11월
  $url = "sub/index.php?mac=$mac";

  Redirect($url);
  exit;

?>
