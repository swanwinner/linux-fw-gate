#!/usr/local/bin/php
<?php

  $env['prefix'] = "/www/gate";
  include("$env[prefix]/inc/common.cli.php");
  include("$env[prefix]/cron/func.php");



### {{{
function _get_row($ip) {
  $qry = "SELECT * FROM ipdb WHERE ipnow='$ip'";
  $ret = db_query($qry);
  $row = db_fetch($ret);
  return $row;
}
function _geoip_lookup($ip) {

  list($a, $b, $c, $d) = preg_split("/\./", $ip);
  $n = $a*256*256*256 + $b*256*256 + $c*256 + $d;

  $qry = "SELECT * FROM GeoIP WHERE ipnfrom<='$n' AND '$n'<=ipnto";
  $ret = db_query($qry);
  $row = db_fetch($ret);
  $nation = $row['nation'];
  return $nation;
}

function _convert_kb($comulative) {
  print<<<EOS
$comulative

EOS;
}
### }}}


  $path = "/www/gate/cron/iftop.txt";
  $content = file_get_contents($path);


  $list = preg_split("/\n/", $content);

  $n = count($list);
  $ln = 0;
  for ($i = 0; $i < $n; $i++) {
    $ln++;
    $line = $list[$i];
    if ($ln < 6) continue; // start from line
    if ($ln > 25) continue; // end to line 

    $num = substr($line, 0,4); // column 0~4
    $num = trim($num);

    $line = substr($line, 5); // start from column 5
    
    $item = preg_split("/ +/", $line);
    //dd($item);

    $a = $item[0];
    $b = $item[1]; // <=, =>
    $c = $item[2]; // 2s
    $d = $item[3]; // 10s
    $e = $item[4]; // 40s
    $f = $item[5]; // comulative
    $comulative = $f;

    list($ip, $port) = preg_split("/:/", $a);

    //$nation = _geoip_lookup($ip);
    $upload = $download = false;
    if ($b == '=>') $upload = true;
    else if ($b == '<=') $download = true;

    $row = _get_row($ip);
    //dd($row);
    $id = $row['id'];

    _convert_kb($comulative);

    print("$id	$ip:$port 	$b	$f\n");

  } 

  exit;

?>
