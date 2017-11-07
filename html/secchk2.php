<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");

function _summary() {
  $qry = "select count(*) count from ipdb where sflag=1"; // 점검 대상
  $ret = db_query($qry);
  $row = db_fetch($ret);
  $total = $row['count'];

  $qry = "select count(*) count from ipdb where sflag=1 and (point is null or point='')"; // 미점검
  $ret = db_query($qry);
  $row = db_fetch($ret);
  $c1 = $row['count'];

  $c2 = $total - $c1; // 점검 완료

  $percent = sprintf("%3.1f", ($c2)/$total * 100);

  $qry = "select point,dept from ipdb where sflag=1 and (point is not null and point != '')";
  $qry = "select point,dept from ipdb where sflag=1";
  $ret = db_query($qry);

  $info = array();
  while ($row = db_fetch($ret)) {
    $point = $row['point'];
    $dept = $row['dept'];
    if ($point == '') {
      $info[$dept]['미점검']++;
    } else {
      $level = floor($point / 10) * 10;
      $info[$dept][$level]++;
    }
  }
  //dd($info);

  $sum = array();
  $depts = array_keys($info);
  $html = '';
  $html.="<table border='1' class='mmdata'>";
  $html.="<tr>";
  $html.="<th>부서/점수</th>";
  for ($i = 100; $i > 0; $i -=10) {
    $html.="<th>{$i}</th>";
  }
  $html.="<th>합계</th>";
  $html.="<th>미점검</th>";
  $html.="</tr>";
  foreach ($depts as $d) {
    $html.="<tr>";
    $html.="<th>{$d}</th>";
    for ($i = 100; $i > 0; $i -=10) {
      $c = floor($info[$d][$i]);
      $sum[$i]+=$c;
      if ($c == 0) $c = '';
      $html.="<td>{$c}</td>";
      $sum[$d]+=$c;
    }
    $html.="<th>{$sum[$d]}</th>";
    $sum['total']+=$sum[$d];
    $html.="<th>{$info[$d]['미점검']}</th>";
    $sum['미점검']+=$info[$d]['미점검'];
    $html.="</tr>";
  }
  $html.="<tr>";
  $html.="<th>합계</th>";
  for ($i = 100; $i > 0; $i -=10) {
    $html.="<th>{$sum[$i]}</th>";
  }
  $html.="<th>{$sum['total']}</th>";
  $html.="<th>{$sum['미점검']}</th>";
  $html.="</tr>";
  $html.="</table>";

  print<<<EOS
<div class='summary'>
보안점검 대상 총:{$total}대, 완료:{$c2}대, 미점검 {$c1}대 ($percent% 완료)
$html
</div>
EOS;

}


// 점수 저장
if ($mode == 'save_point') {
  //dd($form);

  $mac = $form['mac'];
  $point = $form['point'];

  $qry = "UPDATE ipdb SET point='$point' where mac='$mac'";
  $ret = db_query($qry);

  InformRedir('저장하였습니다.', "$env[self]");
  exit;
}


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

div.info { background-color:#fff; border:5px solid #3333ff; width:600px; margin:3 3 3 3px;  padding:3 3 3 3px;}
div.info p { margin:3 3 3 3px; font-size:12pt; font-weight:normal; line-height:150%; }
div.info p a { margin:3 3 3 3px; font-size:12pt; font-weight:normal; }
div.info p span.strong { font-size:15pt; font-weight:bold; }

div.summary { background-color:#fff; border:5px solid #339933; width:600px; margin:3 3 3 3px; padding:3 3 3 3px; }
div.summary p { margin:3 3 3 3px; font-size:12pt; font-weight:normal; line-height:150%; }


.myButton {
	-moz-box-shadow: 0px 1px 0px 0px #fff6af;
	-webkit-box-shadow: 0px 1px 0px 0px #fff6af;
	box-shadow: 0px 1px 0px 0px #fff6af;
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #ffec64), color-stop(1, #ffab23));
	background:-moz-linear-gradient(top, #ffec64 5%, #ffab23 100%);
	background:-webkit-linear-gradient(top, #ffec64 5%, #ffab23 100%);
	background:-o-linear-gradient(top, #ffec64 5%, #ffab23 100%);
	background:-ms-linear-gradient(top, #ffec64 5%, #ffab23 100%);
	background:linear-gradient(to bottom, #ffec64 5%, #ffab23 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffec64', endColorstr='#ffab23',GradientType=0);
	background-color:#ffec64;
	-moz-border-radius:6px;
	-webkit-border-radius:6px;
	border-radius:6px;
	border:1px solid #ffaa22;
	display:inline-block;
	cursor:pointer;
	color:#333333;
	font-family:Arial;
	font-size:15px;
	font-weight:bold;
	padding:6px 24px;
	text-decoration:none;
	text-shadow:0px 1px 0px #ffee66;
}
.myButton:hover {
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #ffab23), color-stop(1, #ffec64));
	background:-moz-linear-gradient(top, #ffab23 5%, #ffec64 100%);
	background:-webkit-linear-gradient(top, #ffab23 5%, #ffec64 100%);
	background:-o-linear-gradient(top, #ffab23 5%, #ffec64 100%);
	background:-ms-linear-gradient(top, #ffab23 5%, #ffec64 100%);
	background:linear-gradient(to bottom, #ffab23 5%, #ffec64 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffab23', endColorstr='#ffec64',GradientType=0);
	background-color:#ffab23;
}
.myButton:active {
	position:relative;
	top:1px;
}

</style>
EOS;


function _find_record($mac) {
  $qry = "SELECT * FROM ipdb WHERE mac='$mac'";
  $ret = db_query($qry);
  $row = db_fetch($ret);
  return $row;
}

  $ip = $_SERVER['REMOTE_ADDR'];

  $command = "/sbin/arp -n | grep '$ip '";
  $lastline = exec($command, $out, $retval);
  list($a,$b,$c,$d,$e) = preg_split("/ +/", $lastline);
  $mac = $c;
  $row = _find_record($mac);
  $register_ip = $row['ip'];
  //dd($row);

  $mac_u = urlencode($mac);
  $url = "/home.php?mode=search&mac=$mac_u&fd02=on&fd06=on&fd07=on&fd09=on&fd11=on";

  print<<<EOS
<div class='info'>
<p>현재 컴퓨터 정보:</p>
<p>MAC: <span class='strong'>$mac</span>

<a href="$url" class="myButton">검색</a>

<p>부서: <span class='strong'>{$row['dept']}</span></p>
<p>사용자: <span class='strong'>{$row['name']}</span></p>
<p>메모: <span class='strong'>{$row['memo']}</span></p>
<p>종류: <span class='strong'>{$row['dtype']}</span></p>
<p>사용중인 IP: <span class='strong'>$ip</span></p>
<p>등록된 IP: <span class='strong'>$register_ip</span></p>

<form action='$env[self]' metho='post'>
<p>점수: 
<input type='text' name='point' value="{$row['point']}" size='10' onclick='this.select()'>점
<input type='hidden' name='mac' value='$mac'>
<input type='hidden' name='mode' value='save_point'>
<input type='submit' value='저장'>
</p>
</form>

</div>
EOS;

  _summary();

  print<<<EOS
<pre>
[[보안 점검 방법]]

* 실사용자 정보 확인
- 관리자 사이트에 로그인
- 보안점검 메뉴 클릭 (현재 컴퓨터의 IP와 맥주소가 나옵니다)
- 검색을 눌러서 등록된 사용자 정보를 확인하고 실제 사용자정보가 아니면 수정합니다.

* IP 설정을 확인
네트워크 환경 - 어댑터 설정 - 로컬네트워크속성 - IPv4 속성

1) DRM 미사용자이면
  고정IP로 130 서브넷 대역으로 설정
 '지정된 IP주소가 아니면 차단 정책 적용' 옵션을 체크. 그외 다른 옵션들은 안 건드려도 됨

2) DRM이 설치되어있으면 유동IP 또는 0,101,107,110,111,125 서브넷 중 하나로 설정
  <strike>(가능하면 유동IP로 설정하기 보다는 고정IP로 설정)</strike>
  모두 고정 아이피로 설정할 것, '지정된 IP주소가 아니면 차단 정책 적용' 옵션을 체크할것

내부 IP주소 192.168.N.xx

N=0 정통부 서버, IP카메라, 지문인증기 등
N=101 대전교회업무망 (고정IP할당)
N=110 IP자동할당용
N=111 IP자동할당용
N=125 행정실,지파장님
N=107 문화부전용 (신천지사이트 접속불가)
N=130 문서보안 미설치 컴퓨터용 (신천지사이트 접속불가)

그외 100,102,103,104,105,106,109,121,122 등으로 고정IP로 쓰고 있는 경우는 위 기준으로 subnet을 변경
* 신천지사이트: 그룹웨어, 행정시스템, 인포맛디아

</pre>
EOS;


  AdminPageTail();
  exit;

?>
