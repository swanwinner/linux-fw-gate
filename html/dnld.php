<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");

  if (is_file("$env[prefix]/config/dnld.php")) {
    AdminPageHead('다운로드');
    include_once("$env[prefix]/config/dnld.php");
    AdminPageTail();
    exit;
  }

  print<<<EOS
<style>
table.main { border:0px solid red; border-collapse:collapse; }
table.main th, table.main td { border:1px solid #999; padding:2px 2px 2px 2px; }
table.main th { background-color:#eeeeee; font-weight:bold; text-align:center; }
table.main td.c { text-align:center; }
table.main td.r { text-align:right; }
table.main td.l { text-align:left; }
table.main td.ls { text-align:left; font-size:5pt; }

div.info { }
div.info p { margin:3 3 3 3px; font-size:12pt; font-weight:normal; line-height:150%; }
div.info p a { margin:3 3 3 3px; font-size:12pt; font-weight:normal; }
div.info p span.strong { font-size:15pt; font-weight:bold; }

</style>
EOS;

$files = array(
);


  print<<<EOS
다운로드
<table border='1'>
<tr>
<th>파일</th>
<th>설명</th>
</tr>
EOS;
  foreach ($files as $item) {
    //print_r($item);
    list($file, $title) = $item;
    print<<<EOS
<tr>
<td><a href='download/$file'>$file</a></td>
<td><a href='download/$file'>$title</a></td>
</tr>
EOS;
  }
  print<<<EOS
</table>
EOS;

  AdminPageTail();
  exit;

?>
