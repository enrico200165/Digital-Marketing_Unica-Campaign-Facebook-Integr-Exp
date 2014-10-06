<?php


function linesArrayToTxtFile($pathname, $lines) {
  $file = fopen($pathname, "w");
  foreach ($lines as $line) {
    fwrite($file, $line . "\n");
  }
  fclose($file);
}


function timeFromUnix($unixTime) {
  date_default_timezone_set("Europe/Rome");
  return date("Y-m-d H:i:s", $unixTime);
}

function getStrVal($orig,$def = null) {
  return  isset($orig)?  $orig : null;
}

function launchBrowser($browser,$url) {
  $output = [];
  $return_var = 0;
  $cmd="\"$browser\" \"$url\"";
  exec($cmd, $output, $return_var);
  print_r($output);
}

