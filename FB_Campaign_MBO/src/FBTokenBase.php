<?php

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class FBTokenBase {

  function __construct($val, Facebook $facebook, Logger $log) {
    $this->token = $val;
    $this->facebook = $facebook;
    $this->log = $log;
  }

  function get() {
    return $this->token;
  }


  function isValid( $forceRead = false) {
    if ($forceRead) {
      $this->readFromFB();
    }
    if (!isset($this->data)) {
      $this->readFromFB();
    }
    $ret = $this->data['is_valid'];
    return $ret;
  }

  function expires_at() {
    if (isset($this->data['expires_at'])) {
      return $this->data['expires_at'];
    } else {
      return 0;
    }
  }


  function readFromFB() {
    $data = $this->facebook->api("/debug_token", array(
        'input_token' => $this->get(),
        // 'access_token' => $access_token,
    ));
    $this->data = $data['data'];
    return $this->data;
  }

  function dump($msg,$facebook,$access_token, $quiet = true) {

    $ret = $this->readFromFB();

    if (isset($ret)) {
      $ret['token'] = $this->get();
      if (isset($ret['data']['expires_at'])) {
        $ret['data']['expires_at_formatted'] = timeFromUnix($ret['data']['expires_at']);
      }
      if ($quiet and  $ret['data']['is_valid'] ==1) {
        return "";
      } else {
        return $msg . "\n" . print_r($ret,true);
      }
    } else {
      return "";
    }
  }

  private $data;
  private $facebook;
  private $token;
  private $log;
}