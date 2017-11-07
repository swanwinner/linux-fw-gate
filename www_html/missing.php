<?php

  unset($env);
  $env['prefix'] = "/www/gate";
  include("$env[prefix]/inc/common.login.php");

  print<<<EOS
<script>
document.location = "http://$conf[internalgate]/";
</script>
EOS;

?>
