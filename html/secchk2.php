<?php

  include_once("path.php");
  include_once("$env[prefix]/inc/common.php");

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
  //dd($list); exit;

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
  } else {
    $qry = "insert into points $sql_set,mac='$mac'";
  }
    $ret = db_query($qry);

  $url = "$env[self]?mac=$mac";
  Redirect($url);
  //InformRedir('저장하였습니다.', $url);
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


</style>
EOS;


  $f_mac = $form['mac'];
  if ($f_mac == '') {
    $mac = my_mac_address();
  } else {
    $mac = $f_mac;
  }

  $row = _find_record($mac);
  $register_ip = $row['ip'];
  //dd($row);
  $ipnow = $row['ipnow'];

  $mac_u = urlencode($mac);
  $url = "/home.php?mode=search&mac=$mac_u";

  print<<<EOS
<div class='info'>
<p>현재 컴퓨터 정보:</p>
<p>MAC: <span class='strong'>$mac</span>

<input type='button' onclick="urlGo('$url')"
 value='검색' style='width:100px; height:50px; background-color:#ff4;'>

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
 'title' => 'DRM 설치 여부(10점)',
 'desc' => '합당한 사유로 총회와 사전 협의 된 경우 사유를 기록하고 감점하지 않음',
 'select' => array('양호'=>10, '불량'=>0),
 ),

 array('no'=> 2,
 'title' => "비보안 문서 작성·보유 여부(5점)",
 'desc' => "(전도지, 외부전송용, 영상자막용, 센터 시험지 파일 등 합당한 사유가 있을시 사유를 기록하고 감점하지 않음)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 3,
 'title' => "비보안 문서 출력여부(5점)",
 'desc' => "(전도지, 센터시험지, 외부 배포용 등 합당한 사유가 있을시 사유를 기록하고 감점하지 않음)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 4,
 'title' => "신천지 문서 방치 여부(5점)",
 'desc' => "(사용 중이지 않은 문서는 잠금장치가 된 곳에 보관)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 5,
 'title' => "주요문서 파쇄 여부(5점)",
 'desc' => "(모든 보안문서는 파쇄기를 이용해 파쇄 해야 함)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 6,
 'title' => "텔레그램 잠금 설정 여부(5점)",
 'desc' => "(텔레그램에 비밀번호 설정이 되어 있어야 함, 2단계 비밀번호 설정 권장)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 7,
 'title' => "로그인 암호 설정여부(10점)",
 'desc' => "(부팅, 윈도우 로그인 암호 둘 중 하나면 설정 해도 됨 144000, 0314, 19840314, 1200, 1234 등 의 비밀번호가 걸려있는 경우 감점. 영문+숫자+특수문자 조합으로 구성 할 것)",
 'select' => array('양호'=>10, '불량'=>0),
 ),

 array('no'=> 8,
 'title' => "화면보호기 암호(5점)",
 'desc' => "(최대 10분까지 설정가능. 144000, 0314, 19840314, 1200, 1234 등 의 비밀번호가 걸려있는 경우 감점. 영문+숫자+특수문자 조합으로 구성 할 것)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 9,
 'title' => "백신 프로그램 설치 여부(5점)",
 'desc' => "(바이러스, 악성코드 감염을 방지하는 백신프로그램 설치 여부, MSE, V3, 기타)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 10,
 'title' => "바이러스 및 악성코드 감염 여부(5점)",
 'desc' => "(컴퓨터 시스템을 망가뜨리고 성능을 저하시키는 바이러스 및 악성코드 감염 여부)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 11,
 'title' => "랜섬웨어 예방(5점)",
 'desc' => "(알약에서 설정, AppCheck 및 그에 상응하는 프로그램 설치 여부)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 12,
 'title' => "원격제어 프로그램 설치 여부(5점)",
 'desc' => "(팀뷰어, 크레이지 리모트 등/단 허용 부서는 가능)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 13,
 'title' => "원격제어 차단설정 여부(5점)",
 'desc' => "(원격지원 연결 비허용 설정 여부/단 허용 부서는 가능)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 14,
 'title' => "P2P 프로그램 및 게임 설치 여부(5점)",
 'desc' => "(1:1 파일 교환 프로그램(토렌트 등) 및 게임프로그램의 설치 여부/단 허용 부서는 가능)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 15,
 'title' => "비인가 파일 공유프로그램 설치 여부(5점)",
 'desc' => "(PC에 파일 공유 프로그램(N드라이브, 기타) 사용 금지. 단 총회에서 내려온 FTP는 사용 가능함)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 16,
 'title' => "비인가 이동식 저장매체 사용 여부(5점)",
 'desc' => "(인가되지 않은 외장하드, USB메모리, 기타 사용 및 보관 방법, 외부 반출 여부)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 17,
 'title' => "비인가 노트북, 태블릿피씨 등 사용 여부(5점)",
 'desc' => "",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 array('no'=> 18,
 'title' => "가상 윈도우 설치 여부(5점)",
 'desc' => "(VMWare 등)",
 'select' => array('양호'=>5, '불량'=>0),
 ),

 );

function _question($no, $item, $preset) {

  $list = array();
  foreach ($item['select'] as $t=>$v) {
    $list[] = "$t:$v";
  }

  $no = $item['no'];
  $fn = "q_$no";

  $html = radio_list_general($fn, $list, $preset, '', false);

  print<<<EOS
<table class='question'>
<tr>
<td class='title'>$no. {$item['title']}</td>
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

table.question { border-collapse: collapse; width:780px; border:1px solid #eee; margin-bottom:3px; }
table.question td { width:800px; word-break: break-all; border:0px solid #eee; }
table.question td.title { font-size:15pt; }
table.question td.desc { font-size:12pt; padding-left:20px; color:#999; }
table.question td.select { font-size:15pt; padding-left:20px; color:#33f; }
table.question td.select label { margin-left:30px; margin-right:30px;  }
</style>
EOS;

  print<<<EOS
<div class='question'>
<form action='$env[self]' method='post'>
<p>점수: <b style='font-size:20pt;'>{$row['point']}</b> 점</b>
<input type='hidden' name='mac' value='$mac'>
<input type='hidden' name='mode' value='save_point'>
<input type='submit' value='저장'>
</p>
EOS;
 
  foreach ($data as $item) {
    $no = $item['no'];
    $preset = $row["p$no"];
    _question($no, $item, $preset);
  }

  print<<<EOS
</form>
</div>
EOS;

  AdminPageTail();
  exit;

?>
