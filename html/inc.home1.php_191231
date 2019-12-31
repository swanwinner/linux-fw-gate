<?php

  print<<<EOS
<table class='main'>
<tr>
<td>
Subnet:
{$n[0]}(정통부),
{$n[100]},
{$n[101]}(통합사무실),
{$n[102]},
{$n[103]},
{$n[104]},
{$n[105]},
{$n[106]},
{$n[107]},
{$n[109]},
{$n[110]}(DHCP),
{$n[111]}(DHCP),
{$n[121]},
{$n[122]},
{$n[125]}
<br>
EOS;

  print("부서: ");

  $qry = "SELECT * FROM dept ORDER BY dept";
  $ret = db_query($qry);

  $cnt = 0;
  $print = 0;
  while ($row = db_fetch($ret)) {
    $dept = $row['dept'];
    $str = $s[$dept];

    $print++;
    if ($cnt > 0 && $print > 1) print(", ");
    print($str);

    if ($print == 10) { print("<br>"); $print = 0; }

    $cnt++;
  }

  print<<<EOS
</td>
</tr>
</table>
EOS;

?>
