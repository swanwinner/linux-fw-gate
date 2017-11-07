<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");


### {{{ modes
if ($mode == 'start') {
  AdminPageHead('보안점검');
  //print_r($form);

  // 보안점검 대상 건수
  $qry = "SELECT COUNT(*) AS count FROM ipdb WHERE sflag=1";
  $ret = db_query($qry);
  $row = db_fetch($ret);
  $total = $row['count'];

  // 차단 플래그를 설정하고, 차단 이유를 보안 점검으로 설정
  // 보안점검한 시간을 null 로 설정
  $qry = "UPDATE ipdb SET bflag='1', bcode='SECURITY_CHECK', sec_chk_time=null WHERE sflag=1";
  $ret = db_query($qry);

  print<<<EOS
<p>
보안 점검 대상 $total 개 MAC 주소에 대하여 인터넷을 차단하고 보안 점검을 시작하였습니다.

<p>
점검 대상 PC에서는 잠시후 인터넷이 접속이 차단되고, 보안점검 안내 페이지로 연결됩니다.

<p>
qry : $qry

<p>
err: $err


EOS;

  AdminPageTail();
  exit;
}
### }}} modes


  AdminPageHead('보안점검');

  print<<<EOS
<style>
table.main { border:0px solid red; border-collapse:collapse; }
table.main th, table.main td { border:1px solid #999; padding:2px 2px 2px 2px; }
table.main th { background-color:#eeeeee; font-weight:bold; text-align:center; }
table.main td.c { text-align:center; }
table.main td.r { text-align:right; }
table.main td.l { text-align:left; }
table.main td.ls { text-align:left; font-size:5pt; }
</style>
EOS;


 $qry = "SELECT *"
     ." FROM ipdb"
     ." WHERE sflag=1"
     ." ORDER BY bflag, sec_chk_time DESC" ;

  $ret = db_query($qry);


 $b1 = MyButton($width=150,'보안 점검 시작', '', "executeBtn()");

  print<<<EOS
$b1
<script>
function executeBtn() {
  var msg = "아래 점검대상 컴퓨터에 대하여 보안 점검을 시작할까요?";
  if (!confirm(msg)) return;
  document.location = "$env[self]?mode=start";
}
</script>

<table class='main'>
<tr>
 <th>번호</th>
 <th>IP</th>
 <th>MAC</th>
 <th>부서</th>
 <th>이름</th>
 <th>종류</th>
 <th>검검대상</th>
 <th>차단상태</th>
 <th>점검완료시간</th>
</tr>
EOS;


  $cnt = 0;
  $c1 = $c2 = 0;
  while ($row = db_fetch($ret)) {
    $cnt++;
    //print_r($row);

    $id = $row['id'];

    $sflag = $row['sflag'];
    if ($sflag) $sflag_s = "점검대상";
    else $sflag_s = "제외";

    $bflag = $row['bflag'];
    if ($bflag) {
      $c1++;
      $ibs = '*차단*';
    } else {
      $c2++;
      $ibs = '허용';
    }

#   $bflag = $row['bflag'];
#   if ($bflag) { $bs = '미점검'; $c1++; }
#   else { $bs = '점검완료'; $c2++; }

    print<<<EOS
<tr>
 <td class='c'>$cnt</td>
 <td class='c'>$row[ip]</td>
 <td class='c'>$row[mac]</td>
 <td class='c'>$row[dept]</td>
 <td class='c'>$row[name]</td>
 <td class='c'>$row[dtype]</td>
 <td class='c'>$sflag_s</td>
 <td class='c'>$ibs</td>
 <td class='c'>$row[sec_chk_time]</td>
</tr>
EOS;
  }
  print<<<EOS
<tr>
<th colspan='8'>
점검대상 총{$cnt}개, 차단:{$c1}개, 허용:{$c2}개
</th>
</tr>
</table>

<iframe name='hiddenframe' width=0 height=0 style='display:none;'></iframe>


EOS;

  AdminPageTail();
  exit;

?>
