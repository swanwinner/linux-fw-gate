<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");


  AdminPageHead('접속로그');
  ParagraphTitle('접속로그');

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

  $qry = "SELECT count(*) as total FROM log";
  $row = db_fetchone($qry);
  $pager_total = $row['total'];
  $tot_s = number_format($pager_total);

  $ipp = 50;

  $page = $form['page'];
  if ($page == '') $page = 1;
  $last = ceil($pager_total/$ipp);
  if ($last == 0) $last = 1;
  if ($page > $last) $page = $last;
  $start = ($page-1) * $ipp;

  
  $ret = db_query($qry);
  $qry = "SELECT * FROM log"
    ." ORDER BY id DESC"
    ." LIMIT $start,$ipp"
     ;
  $ret = db_query($qry);


  unset($form['page']);
  $qs = Qstr($form);
  $url = "$env[self]$qs";
  $pager_html = Pager_s($url, $page, $pager_total, $ipp);


  print<<<EOS
<table border='0' width='600'>
<tr><td>
$pager_html
</td></tr></table>

<table class='main'>
<tr>
 <th>번호</th>
 <th>메시지</th>
 <th>시간</th>
</tr>
EOS;


  $cnt = 0;
  while ($row = db_fetch($ret)) {
    $cnt++;

    $id = $row['id'];

    print<<<EOS
<tr>
 <td class='c'>$cnt</td>
 <td class='l'>$row[memo]</td>
 <td class='c'>$row[idate]</td>
</tr>
EOS;
  }
  print<<<EOS
</table>

EOS;

  AdminPageTail();
  exit;

?>
