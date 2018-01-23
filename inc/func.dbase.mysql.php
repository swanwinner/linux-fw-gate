<?php

function db_connect() {
  global $conf;
  $connect = mysql_connect($conf['dbhost'], $conf['dbuser'], $conf['dbpasswd']);
  if (!$connect) die('db connection fail');
  mysql_set_charset('utf8');
  $ret = mysql_select_db($conf['dbname'], $connect);
  if (!$ret) die('db selection fail');
}

// db_debug(true)
function db_debug($debug) {
  global $env;
  $env['_db_debug_'] = $debug;
}
function db_escape_string($str) {
  return mysql_real_escape_string($str);
}
function db_insert_id() {
  return mysql_insert_id();
}
function db_affected_rows() {
  return mysql_affected_rows();
}
function db_error() {
  return mysql_error();
}
function db_num_rows($ret) {
  return mysql_num_rows($ret);
}
function db_close() {
  mysql_close();
}
function db_query($qry, $debug=0) {
  if ($debug) { dd($qry); return; }
  global $env;
  $ret = mysql_query($qry);
  $err = mysql_error();
  if ($err) { die($err); }
  return $ret;
}
function db_fetch($ret) {
  return mysql_fetch_assoc($ret);
}
function db_fetchone($qry, $debug=0) {
  if ($debug) { dd($qry); return; }
  global $env; if (@$env['_db_debug_']) dd($qry);
  $ret = mysql_query($qry);
  $err = mysql_error();
  if ($err) { die($err); }
  return mysql_fetch_assoc($ret);
  return $ret;
}

function db_table_exists($tablename, $dbname='') {
  $qry = "SHOW TABLES";
  if ($dbname != '') $qry .= " IN $dbname";
  $ret = db_query($qry);
  while ($row = db_fetch($ret)) {
    $a = array_values($row);
    $table = $a[0];
    if ($table == $tablename) return true;
  }
  return false;
}


?>
