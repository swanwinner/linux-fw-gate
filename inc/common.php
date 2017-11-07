<?php

  include("$env[prefix]/config/config.php");
  include("$env[prefix]/inc/func.php");


  session_start();
# print_r($_SESSION); exit;

  date_default_timezone_set('Asia/Seoul');

  error_reporting(E_ALL ^ E_NOTICE);


  if (!$_SESSION['adminlogin']) {
    Redirect("/");
    exit;
  }

  include("$env[prefix]/inc/common.base.php");

?>
