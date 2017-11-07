<?php


// 웹페이지가 불려질때 그려진다.
function make_rrd_graph($rrdpath, $pngpath, $key, $rrd_time='-3hours') {
  //print("$rrdpath  $pngpath\n");
  # rrd_time = [ "-3hours", "-32hours", "-8days", "-5weeks", "-13months" ]
  return;

  $opts = array(
    "--imgformat", "PNG",
    "--start","$rrd_time",
    "--end","now",
    "--width","400",
    "--height","100",
    "DEF:running=$rrdpath:running:AVERAGE",
    "LINE2:running#0000FF:$key",
    "GPRINT:running:MIN:$key\:%6.2lf %Sbps"
  );
  $ret = rrd_graph($pngpath, $opts, count($opts));
  if ( $ret == 0 ) {
    return false; // fail
    //printf("rrd Create error: %s\n", rrd_error());
    //return;
  }
  return true; // success
}

?>
