<?php

function AdminPageHead($title='', $menu=true) {
  global $conf;

  if ($title == '') {
    $title = $conf['head_title'];
  } else {
    $title = sprintf("%s - %s", $conf['head_title'], $title);
  }

  global $env;
  include("{$env['prefix']}/html/header.php");

/*
  print<<<EOS
<html>
<head>
<title>$title</title>

<link rel="stylesheet" type="text/css" href="/style.css">

<style type='text/css'> 
body,input {  font-size: 12px; font-family: 굴림,돋움,verdana;
  font-style: normal; line-height: 12pt;
  text-decoration: none; color: #333333;
}
</style>

<!--
<script src="/js/script.js"></script>
-->

</head>

<body>
EOS;
*/

  if ($menu) {
    print $conf['menu_html'];
  }
}

function AdminPageTail() {
  print<<<EOS
<div style="border:0px solid red; width:100%; height:20px;">
</div>
<div style="position:fixed; bottom:0; width:100%; background-color:#eee; text-align:center; color:#ccc; font-size:x-small;">
linux-gate-fw v2.0 (2018.1.24)
</div>
</body>
</html>
EOS;
}


function ParagraphTitle($title, $level=0, $tostring=false) {
  global $conf, $env;
  $html = '';
  if ($level == 1) {
    $html=<<<EOS
<div style="padding:10 0 5 5px;" class="print_off">
<table border='0'><tr>
<td><img src='$conf[urlprefix]/img/menu/lv2.gif' width=3 height=6></td>
<td><strong>$title</strong></td>
</tr></table>
</div>
EOS;
  } else { // level 0
    $html=<<<EOS
<div style="padding:10 0 5 5px;" class="print_off">
<table border='0'><tr>
<td><img src='$conf[urlprefix]/img/menu/lv4.gif' width=11 height=11></td>
<td><strong>$title</strong></td>
</tr></table>
</div>
EOS;
  }
  if ($tostring) { return $html; }
  else { print $html; return null; }
}

function MyButton($width=60, $text='수정', $url='about:blank', $onclick='') {
  $w = $width - 10;
  if ($onclick != '') {
    $onclick_attr = $onclick;
  } else {
    $onclick_attr = "document.location='$url'";
  }

  $html =<<<EOS
<table border='0' cellpadding='0' cellspacing='0' width='$width'>
<tr style='cursor:pointer' _nclick="$onclick_attr">
<td width='5' height='21'><img src='/img/btn/1/btn_l.gif' width='5' height='21'></td>
<td width='$w' height='21' background='/img/btn/1/btn_c.gif'
 align='center' valign='middle' style='cursor:pointer;'
 onclick="$onclick_attr"><div style='margin:4 0 0 0;'>$text</div></td>
<td width='5' height='21'><img src='/img/btn/1/btn_r.gif' width='5' height='21'></td>
</tr>
</table>
EOS;
  return $html;
}

function MyButton2($width=60, $text='', $url='about:blank', $onclick='') {
  $w = $width - 10;
  if ($onclick != '') {
    $onclick_attr = $onclick;
  } else {
    $onclick_attr = "document.location='$url'";
  }

  $html =<<<EOS
<table border='0' cellpadding='0' cellspacing='0' width='$width'>
<tr style='cursor:pointer' _nclick="$onclick_attr">
<td width='5' height='21'><img src='/img/btn/2/btn_l.gif' width='5' height='21'></td>
<td width='$w' height='21' background='/img/btn/2/btn_c.gif'
 align='center' valign='middle' style='cursor:pointer;'
 onclick="$onclick_attr"><div style='margin:4 0 0 0;'>$text</div></td>
<td width='5' height='21'><img src='/img/btn/2/btn_r.gif' width='5' height='21'></td>
</tr>
</table>
EOS;
  return $html;
}


function FileExtension($filename) {
  $filename = strtolower($filename);
  $exts = split("[/\\.]", $filename);
  $n = count($exts)-1;
  $exts = $exts[$n];
  return $exts;
}

function _select_category($preset) {
  $qry = "SELECT * FROM category";
  $ret = DBQuery($qry);

  $opt = "<option>::선택::</option>";
  while ($row = DBFetchRow($ret)) {
    $v = $row['category'];
    $t = $row['title'];
    if ($v == $preset) $sel = ' selected'; else $sel = '';
    $opt .= "<option value='$v'$sel>$t</option>";
  }
  $html=<<<EOS
<select name='category'>
$opt
</select>
EOS;
  return $html;
}

?>
