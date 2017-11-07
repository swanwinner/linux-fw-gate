<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");


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

div.info { }
div.info p { margin:3 3 3 3px; font-size:12pt; font-weight:normal; line-height:150%; }
div.info p a { margin:3 3 3 3px; font-size:12pt; font-weight:normal; }
div.info p span.strong { font-size:15pt; font-weight:bold; }

</style>
EOS;


$files = array(
array( "doumi.exe",'인터넷연결 도우미'),
array('Everything-1.2.1.371.exe','파일 검색'),

array( "3DP_Chip_v1503.exe", "3DP_Chip_v1503.exe",),

array( "IE11_BlockerToolkit.EXE", "IE11_BlockerToolkit.EXE",),
array( "IE10_BlockerToolkit.EXE", "IE10_BlockerToolkit.EXE",),
array( "IE9_BlockerToolkit.EXE", "IE9_BlockerToolkit.EXE",),

array( "DRM.exe", "문서보안 설치",),
array( "사용자변경.exe", "문서보안 사용자 변경",),

array( "정품인증키.exe", "정품인증키.exe",),

array( "GMHDDSCAN.EXE", "GM HDD SCAN 하드디스크검사",),
array( "CrystalDiskInfo6_2_1.zip", "크리스탈 디스크 HDD S.M.A.R.T 정보",),

array( "Unlocker1.9.2.exe", "Unlocker1.9.2.exe",),
array( "revouninstaller194.zip", "revouninstaller194.zip",),

array( "OJ8600_64비트 IP_192_168_121_31.zip", "OJ8600_64비트 IP_192_168_121_31.zip",),
array( "OJ8600_1315.zip", "OJ8600_1315.zip",),

array( "mzk.zip", "mzk.zip",),
array( "mzk2.zip", "mzk2.zip 최신",),
array( "aio210.zip", "aio210.zip",),
array( "CrystalMark2004R3.zip", "CrystalMark2004R3.zip (32.2.20)",),
array( "capsa_ent_demo_8.2.1.8191_x64.exe", "capsa_ent_demo_8.2.1.8191_x64.exe",),
array( "Windows Update 사전설치가이드.zip", "Windows Update 사전설치가이드.zip",),
array( "Microsoft Toolkit 2.6 BETA 4 Official.zip", "Microsoft Toolkit 2.6 BETA 4 Official.zip",),
array( "R-Studio.zip", "R-Studio",),

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
