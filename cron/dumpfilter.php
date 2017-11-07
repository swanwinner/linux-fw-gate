#!/usr/local/bin/php
<?php

  $env['prefix'] = "/www/gate";
  include("$env[prefix]/inc/common.cli.php");
  include("$env[prefix]/cron/func.php");

  $info = array();

  $conf['dumpfilter_internal_address'] = '192.168';
  $conf['dumpfilter_port_range_min'] = 62000;
  $conf['dumpfilter_port_range_max'] = 62099;
  $conf['dumpfilter_time_window'] = 30;

function _ip_and_port($addr) {
  list($a, $b, $c, $d, $e) = preg_split("/[\.:]/", $addr);
  $ip = "$a.$b.$c.$d";
  $port = $e;
  return array($ip, $port);
}

function _print_info($time, $srcip, $srcport, $dstip, $dstport) {
  $qry = "select * from ipdb where ip='$srcip'";
  $ret = db_query($qry);
  $row = db_fetch($ret);
  $dept = $row['dept'];
  $name = $row['name'];
  print("$time $srcip:$srcport  $dstip:$dstport ........");
  print("$dept $name \n");
}

function _update_db($srcip, $srcport, $dstip, $dstport) {
  global $conf;

  $min = $conf['dumpfilter_port_range_min'];
  $max = $conf['dumpfilter_port_range_max'];
  if ($dstport < $min) return;
  if ($dstport > $max) return;

  $time = date('Y-m-d H:i:s');
  //_print_info($time, $srcip, $srcport, $dstip, $dstport);

  $qry = "select * from arp where ip='$srcip' and udate > date_sub(now(), interval 5 minute)";
  $ret = db_query($qry);
  $row = db_fetch($ret);
  if (!$row) return;

  $mac = $row['mac'];
  if (!$mac) return;

  $qry = "update ipdb set drm_time='$time', drm_server='$dstip', drm_port='$dstport' where mac='$mac'";
  $ret = db_query($qry);
}

function _process_list($line) {
  global $info;
  global $conf;

  $a = preg_split("/ /", $line);
  //print_r($a);
  if ($a[1] != 'IP') continue;
  $src = $a[2];
  $dst = $a[4];
  list($srcip, $srcport) = _ip_and_port($src);
  list($dstip, $dstport) = _ip_and_port($dst);
  //print("$srcip $srcport $dstip $dstport \n");

  $tw = $conf['dumpfilter_time_window'];

  //$key = "$srcip.$srcport.$dstip.$dstport";
  $key = "$srcip.$dstip.$dstport";

  @$t1 = $info[$key];
  $t2 = time();
  if ($t1 > 0) {
    $diff = $t2 - $t1;
    if ($diff > $tw) {
      $info[$key] = $t2;
      _update_db($srcip, $srcport, $dstip, $dstport);
    } else {
    }
  } else {
    $info[$key] = $t2;
    _update_db($srcip, $srcport, $dstip, $dstport);
  }
}

  $stdin = fopen('php://stdin', 'r');
  $pattern = $conf['dumpfilter_internal_address'];
  while ($line = fgets($stdin)) {
    if (preg_match("/$pattern/", $line)) {
      _process_list($line);
    }
  }

?>
