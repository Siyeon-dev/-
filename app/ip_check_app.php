<?php

$ip = $_SERVER['REMOTE_ADDR'];

$accessibleIP = ['210.101.247.3', '210.101.247.1', '110.35.222.149'];

$status = false;

foreach ($accessibleIP as $value) {
  if ($value == $ip) {
    $status = true;
  }
}

  $json = json_encode(["access"=>$status]);
  print_r($json);
