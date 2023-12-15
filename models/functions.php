<?php

function __($str){
  return $str;
}

function print_pre($array = []){
  echo '<pre>';
  var_dump($array);
  echo '</pre>';
}

function abs_url(string $relative_url){
  return ABS_URL.$relative_url;
}

?>
