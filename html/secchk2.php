<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");

### {{{
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

function _find_record($mac) {
  $qry = "SELECT i.*, p.*"
     ." FROM ipdb i"
     ." LEFT JOIN points p ON i.mac=p.mac"
     ." WHERE i.mac='$mac'";
  $ret = db_query($qry);
  $row = db_fetch($ret);
  return $row;
}
### }}}

### {{{
// 점수 저장
if ($mode == 'save_point') {

  // 점수저장 sql_set
  //dd($form); exit;

  $a = array();
  $list = get_form_info($prefix='q');
  //dd($list);
  $sum = 0;
  foreach ($list as $row) {
    $q = $row[0];
    $v = $row[1];
    $sum += $v;
    $a[] = "p$q='$v'";
  }
  //dd($a);
  $a[] = "point='$sum'";
  $sql_set = " SET ".join(",", $a);

  $mac = $form['mac'];

  $qry = "select * from points where mac='$mac'";
  $row = db_fetchone($qry);
  if ($row) {
    $qry = "UPDATE points $sql_set where mac='$mac'";
    $ret = db_query($qry);
  } else {
    $qry = "insert into points $sql_set,mac='$mac'";
    $ret = db_query($qry);
  }

  $url = "$env[self]?mac=$mac";
  InformRedir('저장하였습니다.', $url);
  exit;
}
### }}}

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



  $f_mac = $form['mac'];
  if ($f_mac == '') {
    $ip = current_ip();
    $command = "/sbin/arp -n | grep '$ip '";
    $lastline = exec($command, $out, $retval);
    list($a,$b,$c,$d,$e) = preg_split("/ +/", $lastline);
    $mac = $c;
  } else {
    $mac = $f_mac;
  }

  $row = _find_record($mac);
  $register_ip = $row['ip'];
  //dd($row);
  $ipnow = $row['ipnow'];

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
<p>현재 IP: <span class='strong'>$ip</span></p>
<p>등록된 IP: <span class='strong'>$register_ip ($ipnow)</span></p>

</div>
EOS;

  //_summary();


 $data = array(
 array('no' => 1,
 'title' => '1. DRM 설치여부',
 'desc' => '센터 사무실 내 시험지 취급하는 PC 1대를 제외한 모든 PC는 DRM을 설치 (개인PC도 예외없음)',
 'select' => array('설치'=>1, '미설치'=>0),
 ),
 array('no'=> 2,
 'title' => "2. 비보안 문서 작성 및 보유 여부",
 'desc' => "센터내 DRM 설치 PC 사용시 반드시 DRM을 켜놓고 문서 작업을 할 것 (비보안문서는 모두 삭제하여 PC안에 없어야함, 비보안문서에 비번설정이나 압축 등의 방법으로 보관하는 것도 허용되지 않음) 보안마크가 없는 신천지 문서는 파쇄해야 함. (예외: 교적부, 신앙관리카드, 입교다짐서, 입학원서, 면접질문지, 센터시험지)",
 'select' => array('양호'=>1, '불량'=>0),
 ),
 array('no'=> 3,
 'title' => "3. 비보안 문서 출력여부",
 'desc' => "비보안문서 작업 PC 1대를 제외한 모든 PC는 프린터와 연결이 되어있으면 안됨",
 'select' => array('양호'=>1, '불량'=>0),
 ),
 array('no'=> 4,
 'title' => "4. 신천지 문서 방치 여부",
 'desc' => "보안마크가 찍힌 출력문서는 반드시 잠금장치가 되어 있는 캐비닛이나 서랍에 보관해야 함",
 'select' => array('양호'=>1, '불량'=>0),
 ),
 array('no'=> 5,
 'title' => "5. 주요문서 파쇄 여부",
 'desc' => "보안마크가 찍힌 신천지 문서는 반드시 파쇄기를 사용하여 파기",
 'select' => array('양호'=>1, '불량'=>0),
 ),
 array('no'=> 6,
 'title' => "6. PC 로그인 암호 설정 여부",
 'desc' => "윈도우 로그인 암호 설정시, 12000, 144000, 신천지 같은 단순 암호 사용금지",
 'select' => array('양호'=>1, '불량'=>0),
 ),
 array('no'=> 7,
 'title' => "7. 화면보호기 암호",
 'desc' => "화면보호기는 최대 10분으로 설정",
 'select' => array('양호'=>1, '불량'=>0),
 ),
 array('no'=> 8,
 'title' => "8. 백신 프로그램 설치 여부",
 'desc' => "알약 설치",
 'select' => array('설치'=>1, '미설치'=>0),
 ),
 array('no'=> 9,
 'title' => "9. 바이러스 및 악성코드 감염 여부",
 'desc' => "백신 전체검사 하여 바이러스가 검출되면 안됨",
 'select' => array('양호'=>1, '불량'=>0),
 ),
 array('no'=> 10,
 'title' => "10. 랜섬웨어 예방",
 'desc' => "알약 내 환경설정 (랜섬웨어 차단항목 ON설정)",
 'select' => array('설정'=>1, '미설정'=>0),
 ),
 array('no'=> 11,
 'title' => "11. 원격제어 프로그램설치 여부",
 'desc' => "원격프로그램은 제어판에서 반드시 삭제할 것 (팀뷰어, 크레이지 리모트 등)",
 'select' => array('미설치'=>1, '설치'=>0),
 ),
 array('no'=> 12,
 'title' => "12. 원격제어 차단설정 여부",
 'desc' => "내컴퓨터 마우스 오른쪽 버튼 클릭후 -＞ 속성 마지막텝인 원격으로 들어가서 원격설정 해제",
 'select' => array('설정해제'=>1, '설정'=>0),
 ),
 array('no'=> 13,
 'title' => "13. P2P 프로그램 및 게임 설치 여부",
 'desc' => "제어판에서 토렌트, P2P프로그램 삭제, 게임도 삭제",
 'select' => array('미설치'=>1, '설치'=>0),
 ),
 array('no'=> 14,
 'title' => "14. 비인가 파일 공유프로그램 설치 여부",
 'desc' => "네이버 N 드라이브, 다음 클라우드 BOX 스토리지onedrive, 드롭박스, 웹하드 사용금지",
 'select' => array('미사용'=>1, '사용'=>0),
 ),
 array('no'=> 15,
 'title' => "15. 비인가 이동식 저장매체 사용 여부",
 'desc' => "점검기간내에 외장하드 및 USB사용금지",
 'select' => array('미사용'=>1, '사용'=>0),
 ),
 array('no'=> 16,
 'title' => "16. 가상 윈도우 설치 여부",
 'desc' => "VMWare 사용금지",
 'select' => array('미사용'=>1, '사용'=>0),
 ),
 array('no'=> 17,
 'title' => "17. 출입문 보안 상태 여부",
 'desc' => "사무실 시건장치 확인",
 'select' => array('양호'=>1, '불량'=>0),
 ),
 array('no'=> 18,
 'title' => "18. 문서 보관함 잠금장치 및 관리 상태 여부",
 'desc' => "담당자가 없을 시에 문서 및 자료 보관용 캐비넷은 잠겨 있어야함",
 'select' => array('양호'=>1, '불량'=>0),
 ),
 array('no'=> 19,
 'title' => "19. 인가 컴퓨터 확인 여부",
 'desc' => "PC사용 비품라벨이 부착되어 있어야함",
 'select' => array('양호'=>1, '불량'=>0),
 ),
 array('no'=> 20,
 'title' => "20. 자료방치여부",
 'desc' => "담당자가 없을 시에 서류가 방치되어 있으면 안됨",
 'select' => array('양호'=>1, '불량'=>0),
 ),
 );

function _question($item, $preset) {

  $list = array();
  foreach ($item['select'] as $t=>$v) {
    $list[] = "$t:$v";
  }

  $no = $item['no'];
  $fn = "q_$no";

  //$preset = $form[$fn]; if (!$preset) $preset = '';
  $html = radio_list_general($fn, $list, $preset, '', false);

  print<<<EOS
<table border='1' class='question'>
<tr>
<td class='title'>{$item['title']}</td>
</tr>
<tr>
<td class='desc'>{$item['desc']}</td>
</tr>
<tr>
<td class='select'>{$html}</td>
</tr>
</table>
EOS;
}

  print<<<EOS
<style>
div.question { border:5px solid green; width:800px; }
table.question { border-collapse: collapse; width:800px; border:0px; margin-bottom:30px; }
table.question td { width:800px; word-break: break-all; border:0px solid #eee; }
table.question td.title { font-size:15pt; }
table.question td.desc { font-size:12pt; padding-left:20px; color:#999; }
table.question td.select { font-size:15pt; padding-left:20px; color:#33f; }
table.question td.select label { margin-left:30px; margin-right:30px;  }
</style>
EOS;

  print<<<EOS
<div class='question'>
<form action='$env[self]' metho='post'>
<p>점수: 
<input type='text' name='point' value="{$row['point']}" size='10' onclick='this.select()' readonly style="color:#888; font-size:15pt;">점
<input type='hidden' name='mac' value='$mac'>
<input type='hidden' name='mode' value='save_point'>
<input type='submit' value='저장'>
</p>
EOS;
 
  foreach ($data as $item) {
    $no = $item['no'];
    $preset = $row["p$no"];
    _question($item, $preset);
  }

  print<<<EOS
</form>
</div>
EOS;

  AdminPageTail();
  exit;

?>
