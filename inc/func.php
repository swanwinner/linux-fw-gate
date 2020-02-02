<?php

  if ($conf['use_mysqli']) {
  include($env['prefix']."/inc/func.dbase.php");
  } else {
  //include($env['prefix']."/inc/func.dbase.mysql.php");
  }
  include($env['prefix']."/inc/func.misc.php");
  include($env['prefix']."/inc/func.html.php");
  include($env['prefix']."/inc/func.rrd.php");
  include($env['prefix']."/inc/func.logic.php");

?>
