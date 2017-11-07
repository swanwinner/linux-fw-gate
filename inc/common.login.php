<?php

  include("$env[prefix]/config/config.php");
  include("$env[prefix]/inc/func.php");

  session_start();

  error_reporting(E_ALL ^ E_NOTICE);

  include("$env[prefix]/inc/common.base.php");

?>
