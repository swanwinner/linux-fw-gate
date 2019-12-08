<?php

  print<<<EOS
<html>
<head>
<title>$title</title>

<!--
<style type='text/css'> 
body,input {  font-size: 12px; font-family: 굴림,돋움,verdana;
  font-style: normal; line-height: 12pt;
  text-decoration: none; color: #333333;
}
</style>
-->

<!--
<script src="/js/jquery-3.3.1.min.js"></script>
-->
<script src="/js/jquery-1.12.4.min.js"></script>
<script src="/utl/bootstrap-3.3.7/dist/js/bootstrap.min.js"></script>

<link href="/utl/bootstrap-3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Custom styles for this template -->
<!--
<link href="/css/navbar-fixed-top.css" rel="stylesheet">
-->
<!-- Bootstrap Dropdown Hover CSS -->
<link href="/utl/bootstrap_dropdown_hover/css/bootstrap-dropdownhover.min.css" rel="stylesheet">
<!-- Bootstrap Dropdown Hover JS -->
<script src="/utl/bootstrap_dropdown_hover/js/bootstrap-dropdownhover.min.js"></script>

<link rel="stylesheet" type="text/css" href="/style.css">

</head>

<body>


    <!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/home.php">HOME</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">

<li class="dropdown">
  <a href='#' class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" data-hover="dropdown">동작</a>
  <ul class="dropdown-menu">
    <li><a href="/block.php">차단적용</a></li>
    <li><a href="/arp.php">ARP 정보</a></li>
    <li><a href='/dept.php'>부서관리</a></li>
  </ul>
</li>

<li class="dropdown">
  <a href='#' class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" data-hover="dropdown">기타</a>
  <ul class="dropdown-menu">
    <li><a href="/changepw.php">비밀번호변경</a></li>
    <li><a href="/setting.php">설정</a></li>
    <li><a href="/dnld.php">다운로드</a></li>
    <li><a href="/iftop.php">실시간트래픽(iftop)</a></li>
    <li><a href="/health.php">모니터링</a></li>
    <li><a href="/log.php">로그</a></li>
    <li><a href="/backup.php">백업</a></li>
    <li><a href="/secchk2.php">보안점검</a></li>
  </ul>
</li>
<li><a class="navbar-brand" href="/home.php" target='_blank'>&nbsp;[+]</a></li></ul>

<ul class="nav navbar-nav navbar-right">
<li><a href='/help.php'>도움말</a></li>
<li><a href='/logout.php'>로그아웃</a></li>
</ul>

    </div><!--/.nav-collapse -->
  </div>
</nav>
EOS;

?>
