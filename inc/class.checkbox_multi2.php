<?php

# include_once("$env[prefix]/inc/class.checkbox_multi2.php");
# $cbm = new checkbox_multi();
# $cbm->setoption('화살표숨김');
# $cbm->option('form', 'form2');
# $cbm->option('select_count', 'select_count');
# $cbm->option('selected_list', 'selected_list');
# print("<table border='1' class='mmdata' id='maindata'>"); # id ===>> maindata
# print("<thead>");
# print("</thead>");
# print("<tbody>");
# while ($no...) {
#   $tr = $cbm->get_tr($no);
#   $attr = $cbm->get_td_drag_attr($no);
#   $checkbox = $cbm->checkbox($no);
#   print("$tr");
#   print("<td $attr>$cnt</td>");
#   print("<td>$checkbox</td>");
#   ...
# }
# print("</tbody>");
# print $cbm->get_script();
# print $cbm->get_style();

# $b1 = button_general('전체선택', 0, "select_all()", $style='', $class='');
# $b2 = button_general('선택항목 실행', 0, "executeBtn()", $style='', $class='button_red');
# $btns = button_box($b1,$b2);

#function executeBtn() {
#  var form = document.form2;
#  var c = select_count();
#  if (c == 0) {
#    alert('선택한 것이 아무것도 없습니다.'); return;
#  }
#  var msg = "선택항목 "+c+"건을 요청대로 처리할까요?";
#  if (!confirm(msg)) return;
#  form.mode.value = 'batchproc';
#  form.submit();
#}

// $list = get_checked_list($prefix='cb');

class checkbox_multi {
  var $formname = 'form';
  var $cb_prefix = 'cb';
  var $onclick_fn = "_click_cb";
  var $tr_prefix = 'tr';
  var $select_count = 'select_count';
  var $selected_list= 'selected_list';
  var $ids = array();
  var $list_js_var = '_cbxs';
  var $cell_select_image = "/img/tcursor/aa.png";
  var $hide_arrow = false;

  function checkbox_multi() {
    $this->ids = array();
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
    if ($key == 'DEBUG') {
      $this->debug = true;
    } else if ($key == '화살표숨김') {
      $this->hide_arrow = true;
    }
  }
  function option2($key, $value) {
    if ($key == 'form') {
      $this->formname = $value;
    } else if ($key == 'prefix') {
      $this->cb_prefix = $value;
    } else if ($key == 'select_count') {
      $this->select_count = $value;
    } else if ($key == 'selected_list') {
      $this->selected_list = $value;
    }
  }

  function checkbox($no, $cb_prefix='cb', $checked=false) {
    $this->ids[] = $no; // 리스트에 추가
    $attr = '';
    $attr = " onclick=\"{$this->onclick_fn}('$no')\"";

    $name = "{$this->cb_prefix}_{$no}";
    if ($this->debug) dd($name);

    if ($checked) $c = ' checked'; else $c = '';
    $html = " <input class='checkmulti' type='checkbox' name='$name' id='$name' $attr$c>";
    return $html;
  }

  // <tr> 태그
  function get_tr($no, $attr='') {
    $html = "<tr id='{$this->tr_prefix}_$no'";
    $html .= $attr;
    $html .= ">";
    return $html;
  }

  // 번호셀 <td> 속성
  function get_td_drag_attr($no) {
    $attr = '';

      $attr .= " style='cursor:pointer'";
      $attr .= " ondragstart='return false'";
      $attr .= " onselectstart='return false'";
      $attr .= " onclick=\"_mouseclick('$no')\"";
      $attr .= " onmouseover=\"_mouseover('$no')\"";

      $attr .= " onmousedown=\"_mousedown('$no')\"";
      $attr .= " onmouseup=\"_mouseup('$no')\"";
    return $attr;
  }
  function select_all_button($title='전체선택') {
    $str = "<input type='button' value='$title' onclick='select_all()'>";
    return $str;
  }

  function get_list_var_name() { return $this->list_js_var; }

  function get_script() {

    $js_cbs = join(",", $this->ids);
    print<<<EOS
<script> var {$this->list_js_var} = [$js_cbs]; </script>
EOS;

#   $stop_propagation = " e.preventDefault(); e.stopPropagation(); ";
    $stop_propagation = "";

    if ($this->hide_arrow) $show_arrow = '0'; else $show_arrow = '1';

    if ($this->debug) $debug = '1'; else $debug = '0';

    $script =<<<EOS
<script>
var cursor = 0;
var max_cursor = 0;

function _toggle_check() {
  var md = document.getElementById('maindata');
  var tbody = md.getElementsByTagName('tbody')[0];
  var tr = tbody.children[cursor];

  if ($debug) console.log(tr);
  var cbox = tr.getElementsByClassName('checkmulti')[0];
  if ($debug) console.log(cbox);

  if (cbox.checked) {
    cbox.checked = false;
    $(tr).removeClass('tr_select');
  } else {
    cbox.checked = true;
    $(tr).addClass('tr_select');
  }
}

function _set_max() {
  var md = document.getElementById('maindata');
  var tbody = md.getElementsByTagName('tbody')[0];
  max_cursor = tbody.children.length;
}
function _show_cursor(show) {


  var md = document.getElementById('maindata');
  var tbody = md.getElementsByTagName('tbody')[0];
  var tr = tbody.children[cursor];
  if ($debug) console.log(tr);

  //var trid = $(tr).attr('id');
  //console.log(trid);
  
  var first_td = $(tr).children()[0];
  if ($debug) console.log(first_td);

  if (show) {
    if ($show_arrow) $(first_td).addClass('myClass');
  } else {
    if ($show_arrow) $(first_td).removeClass('myClass');
  }
}


$(document).ready(function() {
  $('input.checkmulti[type="checkbox"]').click(function(e) {
     $stop_propagation 
  });

  try{ _set_max(); } catch(err) {}
  try{ _show_cursor(1); } catch(err) {}

});

// checkbox,select,input 클릭시 click_tr()이 실행되는 것을 방지함
var ignore_tr_event = false;
function {$this->onclick_fn}(id) {
console.log('checked');
  ignore_tr_event = true;
  var cbid = "{$this->cb_prefix}_"+id;
  var trid = "{$this->tr_prefix}_"+id;
  var tr = document.getElementById(trid);
  var cb = document.getElementById(cbid);
  if (cb.checked) {
    $(tr).addClass('tr_select');
  } else {
    $(tr).removeClass('tr_select');
  }
}

// 번호 영역에서 마우스를 드래그
var f_mousedown = false;
var f_downid = 0;
function _mousedown(id) {
  //console.log('mouse down');
  _toggle_cb(id);
  f_mousedown = true; // 마우스가 눌려진 상태
  f_downid = id; // 마우스가 눌려진 항목
}
function _mouseup(id) {
  //console.log('mouse up');
  f_mousedown = false; // 마우스가 눌려진 상태 = false
}
function _mouseover(id) {
  if (f_mousedown) {
    _toggle_cb(id); // 마우스가 눌려진 상태에서 드래그하면 토글한다.
  }
}
function _mouseclick(id) {
  //console.log('mouse click');
  //if (id == f_downid) return;
  //_toggle_cb(id);
}

function _toggle_cb(id) {
  ignore_tr_event = true;
  var cbid = "{$this->cb_prefix}_"+id;
  var trid = "{$this->tr_prefix}_"+id;
  var tr = document.getElementById(trid);
  var cb = document.getElementById(cbid);
  if (cb.checked) {
    cb.checked = false;
    $(tr).removeClass('tr_select');
  } else {
    cb.checked = true;
    $(tr).addClass('tr_select');
  }
}

function {$this->selected_list}() {
  var list = [];
  var idx = 0;
  for (i = 0; i < {$this->list_js_var}.length; i++) {
    var id = {$this->list_js_var}[i];
    var cb = document.getElementById("{$this->cb_prefix}_"+id);
    if (cb.checked) {
      list[idx] = id; idx++;
    }
  }
  return list;
}

function {$this->select_count}() {
  var count = 0;
  for (i = 0; i < {$this->list_js_var}.length; i++) {
    var id = {$this->list_js_var}[i];
    var cb = document.getElementById("{$this->cb_prefix}_"+id);
    if (cb.checked) count++;
  }
  return count;
}

var select_all_flag = 1;
function select_all() {
  for (var i = 0; i < {$this->list_js_var}.length; i++) {
    id = {$this->list_js_var}[i];
    var obj = document.getElementById("{$this->cb_prefix}_"+id);
    if (select_all_flag == 0) {
      obj.checked = false;
    } else {
      obj.checked = true;
    }
    {$this->onclick_fn}(id);
  }
  if (select_all_flag == 0) {
    select_all_flag = 1;
  } else {
    select_all_flag = 0;
  }
}

function __cbm_onload__() {
}

if (window.addEventListener) {
  window.addEventListener("load", __cbm_onload__, false);
} else if (document.attachEvent) {
  window.attachEvent("onload", __cbm_onload__);
}
</script>
EOS;
    return $script;
  }

  function get_style() {
    $img = $this->cell_select_image;
    $style=<<<EOS
<style>
td.myClass {
 font-weight:bold;
 background-image:url($img);
 background-repeat:no-repeat;
}
tr.tr_select { background-color:#ddffdd; }
</style>
EOS;
    return $style;
  }

  // form으로 전달되어 넘어온 이후 (func.misc.php 에 get_checked_list();
  function selected_list($form, $cb_prefix='cb') {
    $keys = array_keys($form);
    $list = array();
    for ($i = 0; $i < count($keys); $i++) {
      $key = $keys[$i]; # 형식: cb_prefix_$no
      $val = $form[$key];
      list($a, $b) = explode('_', $key);
      if ($a != $cb_prefix) continue;
      $list[] = $b;
    }
    //dd($list);
    return $list;
  }

}

?>
