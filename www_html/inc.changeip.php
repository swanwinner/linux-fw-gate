<?php

// include 되어 사용됨
// $msg 변수를 설정해 주는 것이 목적

    $msg =<<<EOS
현재 사용하시는 IP($ip)가 정보통신부에 등록된 IP($regip)와 다름니다.<br>
(성전에서는 지정된 IP만 사용하셔야 합니다.)<br>
<font color=red>네트워크 설정에서 IP주소를 $regip 로 바꾸어 주십시오.</font><br>
위와 같이 IP를 변경하기 전까지 인터넷 접속은 제한됩니다.<br>
- IP를 변경하신 후 5분정도 기다리시면 차단 상태가 해제됩니다.<br>
- 의문사항은 정보통신부에 문의해 주세요.<br>
<br>
<div style='margin-top:5px; margin-bottom:5px;'>
<table border='2' style='border-collapse:collapse'>
<tr>
 <td>IP</td>
 <td>$ip 주소<br></td>
</tr>
<tr>
 <td>MAC</td>
 <td>$mac 주소<br></td>
</tr>
<tr>
 <td>부서</td>
 <td>$row[dept]<br></td>
</tr>
<tr>
 <td>사용자</td>
 <td>$row[name]<br></td>
</tr>
<tr>
 <td>차단상태</td>
 <td>$bflag_s<br>
</td>
</tr>
<tr>
 <td>차단사유</td>
 <td>등록된 IP와 틀림<br></td>
</tr>
</table>
</div>
EOS;

?>
