<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");

  AdminPageHead('iftop');
  ParagraphTitle('iftop');

  print<<<EOS
<style>
table.main { border:0px solid red; border-collapse:collapse; }
table.main th, table.main td { border:1px solid #999; padding:2 9 2 9px; }
table.main th { background-color:#eeeeee; font-weight:bold; text-align:center; }
table.main td.c { text-align:center; }
table.main td.r { text-align:right; }

span.link { cursor:pointer; color:#880000; }
</style>
EOS;
  $path = "/www/gate/cron/iftoprc";
  $iftoprc = file_get_contents($path);

  $path = "/www/gate/cron/iftop.txt";
  $content = file_get_contents($path);
  print<<<EOS
<pre>
updated every 1 min:

# /usr/local/sbin/iftop  -t  -c /www/gate/cron/iftoprc  -s 10 
# cat /www/gate/cron/iftoprc
---------------------------
$iftoprc
---------------------------

$content
</pre>
EOS;

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

    print<<<EOS
<table border='1' class='main'>
<tr>
<th></th>
<th></th>
<th>국가</th>
<th>부서</th>
<th>이름</th>
<th></th>
<th>2s</th>
<th>10s</th>
<th>40s</th>
<th></th>
</tr>
EOS;
  $list = preg_split("/\n/", $content);

  $n = count($list);
  $ln = 0;
  for ($i = 0; $i < $n; $i++) {
    $ln++;
    $line = $list[$i];
    if ($ln < 6) continue; // start from line
    if ($ln > 25) continue; // end to line 
    //print("$ln $line<br>");

    $num = substr($line, 0,4); // column 0~4
    $num = trim($num);

    $line = substr($line, 5); // start from column 5
    
    $item = preg_split("/ +/", $line);
    //dd($item);
    $a = $item[0];
    $b = $item[1];
    $c = $item[2];
    $d = $item[3];
    $e = $item[4];
    $f = $item[5];
    list($ip, $port) = preg_split("/:/", $a);

    $nation = _geoip_lookup($ip);

    $row = _get_row($ip);
    //dd($row);
    if ($i % 2 == 0) $style = " style='background-color:#eeeeff'";
    else $style = " style='background-color:#ffeeee'";

    print<<<EOS
<tr $style>
<td class='c'>$num</td>
<td>$ip:$port</td>
<td>{$nation}</td>
<td>{$row['dept']}</td>
<td>{$row['name']}</td>
<td>$b</td>
<td class='r'>$c</td>
<td class='r'>$d</td>
<td class='r'>$e</td>
<td class='r'>$f</td>
</tr>
EOS;
  } 
  print<<<EOS
</table>
EOS;

  print<<<EOS
<script type='text/javascript'>
// 60 sec
setTimeout("location.reload();",60000);
</script>
EOS;

  AdminPageTail();
  exit;

?>
