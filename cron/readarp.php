#!/usr/local/bin/php
<?php

// readarp.php
// read arp.txt file update `arp` table

  $env['prefix'] = "/www/gate";
  include("$env[prefix]/inc/common.cli.php");
  include("$env[prefix]/cron/func.php");


  $setting = array();
  read_setting($setting);
  //dd($setting);

  $arp = array();
  read_arp_txt($arp);
  //dd($arp);

  $qry = "delete from arp";
  $ret = db_query($qry);

  foreach ($arp['data'] as $item) {
    list($ip, $mac) = $item;
    $qry = "insert into arp set ip='$ip', mac='$mac', udate=NOW()";
    $ret = db_query($qry);
  }

?>
