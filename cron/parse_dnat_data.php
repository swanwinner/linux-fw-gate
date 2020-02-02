#!/usr/local/bin/php
<?php

  $env['prefix'] = "/www/gate";
  include("$env[prefix]/inc/common.cli.php");
  //include("$env[prefix]/cron/func.php");

# /www/gate/var/dnat_data 내용을 파싱한다.

  $file = $argv[1];
  if (!$file) {
    print("Usage : \n");
    printf("%s  dnat_data_file \n", $argv[0]);
    exit;
  }

  $content = file_get_contents($file);
  print $content;

?> 
