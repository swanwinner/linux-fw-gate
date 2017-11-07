<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");

if ($mode == 'download') {

  // 파일명
  $name = 'dbdump.sql';
  $file = $name;

  // 파일이 실제로 있는지 채크
  $filepath = "/tmp/$name";
  if (!file_exists($filepath)) {
    die("파일이 존재하지 않습니다.");
  }

  // 파일 크기
  $size = filesize($filepath);
  $bytes = (string)$size;

  // 파일명에 ; 가 들어 있으면 안됨
  $file = preg_replace("/;/", "%3B", $file);

  header("Content-type:application/octet-stream");
  header("Content-Disposition:attachment; filename=\"$name\"");
  header("Content-Transfer-Encoding:binary");
  header("Content-Length:$bytes");
  header("Cache-Control:Cache,must-revalidate");
  header("Pragma:No-Cache");
  header("Expires:0");

  $fp = fopen($filepath, "rb");
  if (!$fp) Error("download error 2");

  while (!feof($fp)) {
    echo fread($fp, 1048576*2);
    flush();
  }
  fclose($fp);
  exit;
}

  AdminPageHead('IP등록현황');

  //print_r($conf);

  $command = "/usr/local/mysql/bin/mysqldump -u $conf[dbuser] --password=$conf[dbpasswd] --extended-insert=false $conf[dbname] ipdb users dept login > /tmp/dbdump.sql";
  exec($command);

  print<<<EOS
$command
<br>
<br>
<a href='$env[self]?mode=download'>download dbdump.sql</a>
EOS;

  AdminPageTail();
  exit;

?>
