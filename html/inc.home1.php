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


<!--
<table class='main'>
<tr>
 <td>$n[0]</td><td>$s[정통부],$s[무선공유기]</td>
 <td>$n[100]</td><td>업무망(기존)</td>
 <td>$n[111]</td><td>DHCP</td>
 <td></td><td>{$s['???']}</td>
</tr>
<tr>
 <td>$n[101]</td><td>$s[장년회],$s[부녀회],$s[청년회]</td>
 <td>$n[106]</td><td>$s[섭외부],$s[법무부],$s[국선부],$s[신문고부](x)</td>
 <td>$n[121]</td><td>$s[건설부],$s[봉교부],$s[사업부],$s[보건후생부],$s[체육부]</td>
 <td>$n[126]</td><td>-미사용-</td>
</tr>
<tr>
 <td>$n[102]</td><td>$s[대흥지역],$s[가장지역](x)</td>
 <td>$n[107]</td><td>$s[문화부],$s[목양실]</td>
 <td>$n[122]</td><td>$s[찬양부](x)</td>
 <td>$n[127]</td><td>-미사용-</td>
</tr>
<tr>
 <td>$n[103]</td><td>$s[탄방지역],$s[대덕지역](x)</td>
 <td>$n[108]</td><td>-미사용-</td>
 <td>$n[123]</td><td>-사용금지-</td>
 <td>$n[128]</td><td>-미사용-</td>
</tr>
<tr>
 <td>$n[104]</td><td>$s[유성지역],$s[특화부](x)</td>
 <td>$n[109]</td><td>$s[유년회],$s[학생회],$s[자문회](x)</td>
 <td>$n[124]</td><td>-미사용-</td>
 <td>$n[129]</td><td>임시</td>
</tr>
<tr>
 <td>$n[105]</td><td>$s[전도부],$s[교육부],$s[교방부],$s[기획부]</td>
 <td>$n[110]</td><td>DHCP</td>
 <td>$n[125]</td><td>$s[행정실],$s[신학부]</td>
 <td></td><td></td>
</tr>
</table>
-->
EOS;

?>
