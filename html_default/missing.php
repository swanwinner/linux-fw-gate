<?php

  include_once("./path.php");
  include_once("$env[prefix]/inc/common.login.php");

  print<<<EOS
<script>
document.location = "http://$conf[internalgate]/";
</script>
EOS;

?>
