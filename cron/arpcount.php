#!/usr/local/bin/php
<?php

  $env['prefix'] = "/www/gate";
  include("$env[prefix]/inc/common.cli.php");

### {{{ function
function _graph($rrd_time, $rrdfile, $title, $pngpath) {
  $opts = array(
    "--imgformat", "PNG",
    "--start","$rrd_time",
    "--end","now",
    "--width","400",
    "--height","100",
    "DEF:running=$rrdfile:running:AVERAGE",
    "LINE2:running#0000FF:$title",
    "GPRINT:running:MIN:$title\:%6.2lf %Sbps"
  );
  $ret = rrd_graph($pngpath, $opts, count($opts));
  if ( $ret == 0 ) {
    return false; // fail
  }
}

function _arp_rrd_update($cnt) {
  global $conf;
return;

  $rrdfile = $conf['rrd_dir_path']."/arp.rrd";

  $ret = rrd_update($rrdfile, "N:$cnt"); // 업데이트
  if ( $ret == 0 ) {
    printf("rrd update error: %s\n", rrd_error());
    return false; // fail
  }

  // 최근 3시간
  $pngpath = $conf['rrd_img_path']."/arp_3hours.png";
  $rrd_time='-3hours';
  $title = '3hours';
  _graph($rrd_time, $rrdfile, $title, $pngpath);

  // 최근 8일
  $pngpath = $conf['rrd_img_path']."/arp_8days.png";
  $rrd_time='-8days';
  $title = '8days';
  _graph($rrd_time, $rrdfile, $title, $pngpath);

  // 최근 5주
  $pngpath = $conf['rrd_img_path']."/arp_5weeks.png";
  $rrd_time='-5weeks';
  $title = '5weeks';
  _graph($rrd_time, $rrdfile, $title, $pngpath);

  return true; // success
}

### }}} function


  // arp.txt 파일을 읽는다.
  $file = "/www/gate/cron/arp.txt";
  $content = file_get_contents($file);
  //print $content;
  $list = preg_split("/\n/", $content);
  //print_r($list);

  // 파일이 생성된 시간
  list($a, $b) = preg_split("/@/", $list[0]);
  $lastupdate = $b;
  //print("lastupdate=$lastupdate<br>");

  $msgs = array();
  $mac_list = array();
  $n = count($list);
  $cnt = 0;
  for ($i = 0; $i < $n; $i++) { // arp.txt 모든 라인에 대해서
    $line = $list[$i];

    if (preg_match("/[0-9A-F][0-9A-F]:[0-9A-F][0-9A-F]:[0-9A-F][0-9A-F]:/", $line)) {
      $cnt++;
    }
  }

  // arp 캐시에서 발견된 MAC 주소 개수를 rrd db에 기록
  SaveLog("arp_rrd_update $cnt 개");
  _arp_rrd_update($cnt);

  exit;

?>
