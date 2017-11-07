<?php

  db_connect();

  # GET/POST 방식 어느 경우이든 $form 에 저장된다.
  $form = $_REQUEST;
  $mode = $form['mode'];

  # 브라우져에서 호출된 php 파일의 url
  $env['self'] = $_SERVER['SCRIPT_NAME'];

?>
