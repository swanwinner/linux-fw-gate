<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");


  $_SESSION['logined'] = false;
  $_SESSION['adminlogin'] = false;

  Redirect("/");

  exit;

?>
