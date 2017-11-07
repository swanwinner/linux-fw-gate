<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");


  AdminPageHead('Health');

  print<<<EOS
<a href='/health/' target='_blank'>System Health Monitor</a>
<br>

<style>
table.main { border:0px solid red; border-collapse:collapse; }
table.main th, table.main td { border:1px solid #999; padding:2px 5px 2px 5px; }
table.main th { background-color:#eeeeee; font-weight:bold; text-align:center; }
table.main td.c { text-align:center; }
table.main td.r { text-align:right; }
</style>
EOS;


  print<<<EOS
<table border='0'>
<tr>
<td>
EOS;

  $inic = $conf['internalNIC'];
  $enic = $conf['externalNIC'];
  print<<<EOS
<table class='main'>
<tr><td>내부망 NIC: {$inic}<br></tr>
<tr><td><img src='/health/{$inic}-3hours.png'></td></tr>
<tr><td><img src='/health/{$inic}-32hours.png'></td></tr>
<tr><td><img src='/health/{$inic}-8days.png'></td></tr>
</table>

</td><td>

<table class='main'>
<tr><td>외부망 NIC: {$enic}eth1<br></tr>
<tr><td><img src='/health/{$enic}-3hours.png'></td></tr>
<tr><td><img src='/health/{$enic}-32hours.png'></td></tr>
<tr><td><img src='/health/{$enic}-8days.png'></td></tr>
</table>

</td>
</tr>

<tr>
<td>

<table class='main'>
<tr><td>loadavg<br></tr>
<tr><td><img src='/health/loadavg-3hours.png'></td></tr>
<tr><td><img src='/health/loadavg-32hours.png'></td></tr>
<tr><td><img src='/health/loadavg-8days.png'></td></tr>
</table>

</td><td>

<table class='main'>
<tr><td>uptime<br></tr>
<tr><td><img src='/health/uptime-3hours.png'></td></tr>
<tr><td><img src='/health/uptime-32hours.png'></td></tr>
<tr><td><img src='/health/uptime-8days.png'></td></tr>
</table>


</td></tr></table>
EOS;


  AdminPageTail();
  exit;

?>
