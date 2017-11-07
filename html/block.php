<?php

  include("path.php");
  include("$env[prefix]/inc/common.php");

### {{{
function _process($cnt, &$row) {
  global $conf;
  global $file;
  global $total;

  $bflag = $row['bflag'];
  $realmac = $row['realmac'];

  //if ($bflag or $bflag2) { 
  if ($bflag) {
    $cmd =
        "/sbin/iptables -t nat -A PREROUTING  -p tcp"
       ." -i $conf[internalNIC]"
       ." -m mac --mac-source $realmac -j DNAT"
       ." --to-destination $conf[internalgate]";
    $file .= "$cmd \n";
    $total++;
  }

}
### }}}


### {{{

if ($mode == 'doblock') {
  AdminPageHead('차단');

  // 보안점검
  $file = '';
  $total = 0;
  blocked_client_list($callback='_process');
//dd($file); exit;

  $bfile = "/www/gate/rule/block.sh";
  file_put_contents($bfile, $file);

  print<<<EOS
Wrote to $bfile<br>
<br>
$total 개 차단<br>
EOS;

  // 방화벽 규칙을 재설정
  $command = "/www/gate/com/surun root \"/root/firewall\"";
  system($command);

  AdminPageTail();
  exit;
}

### }}}


  AdminPageHead('차단');

  print<<<EOS
<style>
table.main { border:0px solid red; border-collapse:collapse; }
table.main th, table.main td { border:1px solid #999; padding:2px 5px 2px 5px; }
table.main th { background-color:#eeeeee; font-weight:bold; text-align:center; }
table.main td.c { text-align:center; }
table.main td.r { text-align:right; }
</style>
EOS;


  print<<<EOS
<p class=desc>아래 차단 설정된 컴퓨터를 인터넷 사용할 수 없도록 차단조치함.(내부 네트워크 사용은 가능)</p>
<p class=desc>아래 차단적용 버튼을 누르면 차단됩니다. 아래 리스트에 없는 것은 차단되지 않음.</p>
<p class=desc>MAC주소를 기준으로 차단되므로, IP를 바꾸어도 계속 차단됨.</p>

<button onclick="_block()">차단 적용</button>
<script>
function _block() {
  location.href='$env[self]?mode=doblock';
}
</script>

<table class='main'>
<tr>
<th>번호</th>
<th colspan='3'>IP</th>
<th>실제MAC주소</th>
<th>부서</th>
<th>사용자</th>
<th>입력일시</th>
<th>차단</th>
<th>수정</th>
<th>삭제</th>
</tr>
EOS;

  function _row($cnt, &$row) {
    //dd($row);

    $id = $row['id'];

    $company = $row['company'];

    $title  = $row['title'];
    if ($title == '') $title = '제목없음';

    $delete=<<<EOS
<span onclick="_delete('$id')" style='cursor:pointer'>삭제</span>
EOS;

    $mac = $row['mac'];
    if ($mac == '') {
      $mac_s=<<<EOS
<a href='$env[sefl]?mode=addmac&id=$id'>-입력-</a>
EOS;
    } else {
      $mac_s = $mac;
      $qry2 = "select count(*) as count from ipdb where mac='$mac'";
      $ret2 = db_query($qry2);
      $row2 = db_fetch($ret2);
      if ($row2['count'] >= 2) {
        $mac_s .= "<font color='red'>(중복)</font>";
        $mac_s .= "<a href='$env[sefl]?mode=addmac&id=$id'>-재입력-</a>";
      }
    }

    $realmac = $row['realmac'];
    $realmac_s = $realmac;
    if ($mac != $realmac) {
      $realmac_s = "<font color=red>$realmac</font>";
    }

    $bflag = $row['bflag'];
    if ($bflag) $bflag_s = '차단'; else $bflag_s = '-';

    print<<<EOS
<tr>
<td class='c'>$cnt</td>
<td class='l'>$row[ip]</td>
<td class='l'>$row[ip3]</td>
<td class='l'>$row[ip4]</td>
<td class='l'>$realmac_s</td>
<td class='c'>$row[dept]</td>
<td class='c'>$row[name]</td>
<td class='c'>$row[idate]</td>
<td class='c'>$bflag_s</td>
<td class='c'><span onclick="_edit('$id')" style='cursor:pointer'>수정</span></td>
<td class='c'><span onclick="_delete('$id')" style='cursor:pointer'>삭제</span></td>
</tr>
EOS;
  }

  // 차단된 리스트
  blocked_client_list($callback='_row');

  print<<<EOS
</table>

<iframe name='hiddenframe' width=0 height=0 style='display:none;'></iframe>

<script>
function _edit(id) {
  var url = "home.php?mode=edit&id="+id;
  open(url);
}
function _delete(id) {
  if (!confirm('삭제할까요?')) return;
  hiddenframe.location = "home.php?mode=delete&id="+id;
}
</script>
EOS;

  AdminPageTail();
  exit;

?>
