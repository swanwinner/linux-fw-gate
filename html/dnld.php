<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");

  AdminPageHead('다운로드');

  $url = $conf['download_site'];
  print<<<EOS
<a href='$url' target='_blank'>다운로드 사이트 바로가기</a>
EOS;

  AdminPageTail();
  exit;

?>
