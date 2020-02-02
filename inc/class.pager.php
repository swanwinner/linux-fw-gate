<?php

class pager {
  var $total;
  var $ipp;
  var $last;

###
# include_once("$env[prefix]/inc/class.pager.php");
# $ipp = get_ipp(20,$min=10,$max=500);
# $opts = option_ipp($ipp, array(10,20,50,100,200,500));
# $html = select_element('ipp', $opts).'건';
# print label_fe('출력', $html);
#
# $ipp = get_ipp(20,$min=10,$max=500);
# $cpgr = new pager();
# $start = $cpgr->calc($sql_from, $sql_join, $sql_where, $ipp);
# $html = $cpgr->get($formname='search_form', $innerhtml='');
# print $html;
#
# $qry = "SELECT ...."
#  .$sql_from.$sql_join.$sql_where.$sql_order;
# if ($mode != 'download') $qry .= " LIMIT $start,$ipp";
#
#<form name='search_form'>
#<input type='hidden' name='page' value='{$form['page']}'>
###

  function pager() {
  }

  // 옵션 지정
  // option($arg1, $arg2, ...)
  function option() {
    $len = func_num_args();
    $args = func_get_args();
    for ($i = 0; $i < $len; $i++) {
      $a = $args[$i];
      $this->setoption($a);
    }
  }
  function setoption($key) {
  }
  // $class->option2('limit',10000);
  function option2($key, $value) {
    if ($key == 'limit') {
      $this->limit = $value;
    }
  }

  function get_total() {
    $total = $this->total; 
    return $total;
  }

  // 페이지 이동할 수 있는 컨트롤
  function get($formname='form', $innerhtml='') {
    global $download_mode;
    if ($download_mode) return '';

    $total = $this->total; 
    $page = $this->page;
    $last = $this->last;

    if (!$total) $tot_s = '0';
    else $tot_s = number_format($total);

    $pager = $this->Pager_f($formname, $page, $total, $this->ipp);
    $last = number_format($last);

    $html=<<<EOS
<table border='0' cellpadding='3' cellspacing='1'>
<tr><td style="border:3px solid #eeeeee;">
<table border='0' width='600'>
<tr>
<td align='center'>$pager</td>
<td align='center'>전체 {$tot_s}건&nbsp;&nbsp;$page/{$last}페이지{$innerhtml}</td>
</tr>
</table>
</td></tr></table>
EOS;

    // 36.9.16
    $limit = $this->limit;
    if ($limit && $total > $limit) {
      print $html;
      yellow_bar('검색결과가 너무 많습니다.'); exit;
    }
    return $html;
  }

  // 페이지 계산
  //   list($start, $last, $page) = calc_page($ipp, $total);
  function calc_page($ipp, $total) {
    global $form;

    $page = $form['page'];
    if ($page == '') $page = 1;
    $last = ceil($total/$ipp);
    if ($last == 0) $last = 1;
    if ($page > $last) $page = $last;
    $start = ($page-1) * $ipp;

    return array($start, $last, $page);
  }

  function calc($sql_from, $sql_join, $sql_where, $ipp) {
    if (!$ipp) $ipp = 10; // default ipp
    $qry = "SELECT count(*) count".$sql_from.$sql_join.$sql_where;
    $row = db_fetchone($qry);
    $this->total = $row['count'];
    $this->ipp = $ipp;

    list($start, $last, $page) = $this->calc_page($ipp, $this->total);
    $this->page = $page;
    $this->last = $last;
    return $start;
  }

  function Pager_f($formname, $page, $total, $ipp) {
    global $conf, $env;
    $html = '';

    $btn_prev   = "<img src='/img/pager/2/l.png' border=0 width=11 height=11>";
    $btn_next   = "<img src='/img/pager/2/r.png' border=0 width=11 height=11>";
    $btn_prev10 = "<img src='/img/pager/2/l2.png' border=0 width=11 height=11>";
    $btn_next10 = "<img src='/img/pager/2/r2.png' border=0 width=11 height=11>";

    $last = ceil($total/$ipp);
    if ($last == 0) $last = 1;

    $start = floor(($page - 1) / 10) * 10 + 1;
    $end = $start + 9;

    //print("$formname / page=$page / total=$total / ipp=$ipp / start=$start / last=$last / end=$end <br>");

    $html =<<<EOS
<div class='pager'>
<table border='0' cellpadding='2' cellspacing='0'>
<tr>
EOS;

    $attr1 = " onmouseover=\"this.className='pager_on'\""
         ." onmouseout=\"this.className='pager_off'\""
         ." class='pager_off' align='center' style='cursor:pointer;'";
    $attr2 = " onmouseover=\"this.className='pager_sel_on'\""
         ." onmouseout=\"this.className='pager_sel_off'\""
         ." class='pager_sel_off' align='center' style='cursor:pointer;'";
 
    # previous link
    if ($start > 1) {
      $prevpage = $start - 1;
      $pp2 = $prevpage;
      $html .= "<td$attr1 align=center onclick=\"pager_Go('$prevpage')\">$btn_prev10</td>\n";
    } else $html .= "<td align=center class='pager_static'>$btn_prev10</td>\n";

    if ($page > 1) {
      $prevpage = $page - 1;
      $pp1 = $prevpage;
      $html .= "<td$attr1 align=center onclick=\"pager_Go('$prevpage')\">$btn_prev</td>\n";
    } else $html .= "<td align=center class='pager_static'>$btn_prev</td>\n";


    if ($end > $last) $end = $last;
    $html .= "</td>";
    for ($i = $start; $i <= $end; $i++) {
      $s = "$i";
        if ($i != $page) {
        $html .= "<td$attr1 onclick=\"pager_Go('$i')\">$s</td>\n";
      } else {
        $html .= "<td$attr2>$s</td>\n";
      }
    }

    # next link
    if ($page < $last) {
      $nextpage = $page + 1;
      $np1 = $nextpage;
      $html .= "<td$attr1 align=center onclick=\"pager_Go('$nextpage')\">$btn_next</td>\n";
    } else $html .= "<td align=center class='pager_static'>$btn_next</td>\n";

    if ($end < $last) {
      $nextpage = $end + 1;
      $np2 = $nextpage;
      $html .= "<td$attr1 align=center onclick=\"pager_Go('$nextpage')\">$btn_next10</td>\n";
    } else {
      $np2 = $page;
      $html .= "<td align=center class='pager_static'>$btn_next10</td>\n";
    }

    $html .=<<<EOS
</tr>
</table>
</div>
EOS;
  $html .=<<<EOS
<script>
function pager_Go(page) {
  document.$formname.page.value = page;
  document.$formname.submit();
}
function pager_prev2() { pager_Go('$pp2'); }
function pager_prev1() { pager_Go('$pp1'); }
function pager_next1() { pager_Go('$np1'); }
function pager_next2() { pager_Go('$np2'); }
</script>
EOS;
    return $html;
  }

}

?>
