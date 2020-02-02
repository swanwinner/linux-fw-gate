<?php

# include_once("$env[prefix]/inc/class.dao2.php"); 
# $dao = new dao('ziongroup', 'table_name');
# $dao->boilerplate(); // 
# $set = array();
# $set['name'] = array($name, '');
# $set['tel'] = array($tel, '');
# $set['idate'] = array('now()', 'noq');
# ....
#
# $qry = $dao->get_insert_query($set);
# db_query($qry);
#
# $set['id'] = $id;
# $qry = $dao->get_update_query($set, $keycol='id');
# db_query($qry);

// class dao 
// class zdao 

class dao {
  var $db;
  var $table;
  var $cols;
  var $debug;

  function __construct($db='', $table='', $debug=false) {
    $this->db = $db;
    $this->table = $table;
    //dd("$db.$table");
    $this->debug = $debug;

    if ($db != '' && $table != '') {
      // 테이블의 컬럼 정보를 파악
      $qry = "show columns from $db.$table";
      $ret = db_query($qry);
      $cols = array();
      while ($row = db_fetch($ret)) {
        $col = $row['Field'];
        $cols[$col] = true;
      }
      if (!$cols) die("dao: table=$table error");
      $this->dd($cols);
      $this->cols = $cols;
    }
  }
  function dd($info) {
    if (!$this->debug) return;
    dd($info);
  }

  // set 에 들어 있는 정보를 테이블에 반영하기 위한 sql_set 정보
  function add_set_info(&$s, $set) {
    $keys = array_keys($set);
    foreach ($keys as $k) {

      if (!($this->cols[$k])) continue;

      list($v, $opt) = $set[$k];
      if (preg_match("/noq/", $opt)) $noq = true;  else $noq = false; // noquote
      if (preg_match("/nle/", $opt)) $nle = true;  else $nle = false; // null if empty

      $v = db_escape_string($v);
      if ($noq) $tmp = "$k=$v";
      else $tmp = "$k='$v'";

      $this->dd($tmp);
      $s[] = $tmp;
    }
  }

  function get_insert_query($set) {
    $s = array(); 
    $this->add_set_info($s, $set);
    $sql_set = " SET ".join(",", $s);
    $qry = "INSERT INTO {$this->db}.{$this->table}".$sql_set;
    return $qry;
  }
  function get_update_query($set, $keycol='id') {
    $keyval = $set[$keycol];
    if ($keyval == '') die("get_update_query error keyval is null");
    unset($set[$keycol]);

    $s = array(); 
    $this->add_set_info($s, $set);
    $sql_set = " SET ".join(",", $s);
    $qry = "UPDATE {$this->db}.{$this->table}".$sql_set." WHERE $keycol='$keyval'";
    return $qry;
  }
  function get_update_query2($set, $key1='', $key2='') {
    if ($key1) $keyval1 = $set[$key1];
    if ($key2) $keyval2 = $set[$key2];
    if ($keyval1 == '' && $keyval2 == '') die("get_update_query error keyval is null");
    unset($set[$key1]);
    unset($set[$key2]);

    $sql_where = " WHERE 1";
    if ($keyval1) $sql_where .= " and $key1='$keyval1'";
    if ($keyval2) $sql_where .= " and $key2='$keyval2'";

    $s = array(); 
    $this->add_set_info($s, $set);
    $sql_set = " SET ".join(",", $s);
    $qry = "UPDATE {$this->db}.{$this->table}".$sql_set.$sql_where;
    return $qry;
  }

  function get_data($keycol, $keyval) {
    $qry = "select * from {$this->db}.{$this->table} WHERE $keycol='$keyval'";
    return db_fetchone($qry);
  }
  function insert_one($keycol, $keyval) {
    $qry = "insert into {$this->db}.{$this->table} set $keycol='$keyval'";
    return db_query($qry);
  }


  function select_before_insert($set, $keys) {
    $ks = preg_split("/,/", $keys);
    $w = array();
    foreach ($ks as $k) {
      $v = $set[$k][0];
      $w[] = "$k='$v'";
    }
    $s = join(" AND ", $w);
    $qry = "select * from {$this->db}.{$this->table} WHERE $s";
    $row = db_fetchone($qry);
    return $row;
  }

  // 36.6.5
  function set_dump($set, $style=1) {
    $s = [];
    foreach ($set as $key=>$item) {
      $val = $item[0];
      if ($style == 1) {
        $s[] = "[$key]=>$val";
      } else if ($style == 2) {
        $s[] = "$key:$val";
      }
    }
    return join(" ", $s);
  }

  function boilerplate() {
    $keys = array_keys($this->cols);
    print('  <pre>'."\n");
    print('  $set = array();'."\n");
    foreach ($keys as $k) {
      print("  \$set['$k'] = array(\$$k, '');"."\n");
    }
    print("  \$set['idate'] = array('now()', 'noq');"."\n");
    print('  $qry = $dao->get_insert_query($set);'."\n");
    print('  db_query($qry);'."\n");
    print('  </pre>'."\n");
exit;
  }

};

// 각종 테이블 입력용
class zdao {

  function insert_member_gangsa($sid, $gid, $levl, $memo, $seq) {
    $dao = new dao('ziongroup', 'member_gangsa');
    $set = array();
    $set['sid'] = array($sid, '');
    $set['gid'] = array($gid, '');
    $set['levl'] = array($levl, '');
    $set['memo'] = array($memo, '');
    $set['seq'] = array($seq, '');
    $set['idate'] = array('now()', 'noq');
    $set['udate'] = array('now()', 'noq');
    $set['mid'] = array(my_inputno());
    $qry = $dao->get_insert_query($set);
    return $qry;
  }

  function insert_member_jundosa($sid, $jid, $memo, $seq) {
    $dao = new dao('ziongroup', 'member_jundosa');
    $set = array();
    $set['sid'] = array($sid, '');
    $set['jid'] = array($jid, '');
    $set['memo'] = array($memo, '');
    $set['seq'] = array($seq, '');
    $set['idate'] = array('now()', 'noq');
    $set['udate'] = array('now()', 'noq');
    $set['mid'] = array(my_inputno());
    $qry = $dao->get_insert_query($set);
    return $qry;
  }

  // $zdao->insert_career_regs($mode, $sid, $cdate, $rt, $rs, $rc, $aflag, $seq);
  function insert_career_regs($mode, $sid, $cdate, $rt, $rs, $rc, $aflag, $seq) {
    $dao = new dao('ziongroup', 'career_regs');
    $set = array();
    $set['sid'] = array($sid, '');
    $set['cdate'] = array($cdate, '');
    $set['Rt'] = array($rt, '');
    $set['Rs'] = array($rs, '');
    $set['Rc'] = array($rc, '');
    $set['aflag'] = array($aflag, '');
    $set['mode'] = array($mode, '');
    $set['seq'] = array($seq, '');
    $set['idate'] = array('now()', 'noq');
    $set['udate'] = array('now()', 'noq');
    $set['mid'] = array(my_inputno());
    $qry = $dao->get_insert_query($set);
    return $qry;
  }

};


?>
