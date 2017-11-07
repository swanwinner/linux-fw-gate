<?php

  include("$env[prefix]/config/config.php");
  include("$env[prefix]/inc/func.php");

  session_start();

  date_default_timezone_set('Asia/Seoul');

  error_reporting(E_ALL ^ E_NOTICE);

  include("$env[prefix]/inc/common.base.php");

?>
