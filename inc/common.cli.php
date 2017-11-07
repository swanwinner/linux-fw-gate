<?php

  include_once("$env[prefix]/config/config.php");
  include_once("$env[prefix]/inc/func.php");

  error_reporting(E_ALL ^ E_NOTICE);

  date_default_timezone_set('Asia/Seoul');

  db_connect();

?>
