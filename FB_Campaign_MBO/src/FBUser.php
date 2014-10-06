<?php 

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class FBUser {

  function __construct($FBId, Facebook $facebook, Logger $log , $dataArr = null) {
    if (isset($dataArr)) {
      $this->FBId = $dataArr['id'];
      $this->access_token =  isset($dataArr['access_token']) ? $dataArr['access_token'] : null;
      $this->login_url =  isset($dataArr['login_url']) ? $dataArr['login_url'] : null;
      $this->data = $dataArr;
    } else {
      $this->FBId = $FBId;
    }
    $friends = null;
    $this->facebook = $facebook;
    $this->log = $log;
  }

  public static function FromDataFile($id,$data, Facebook $facebook, Logger $log ) {

    $instance = new self($id,$facebook, $log, null);
    $instance->emmId = $data['emmID'];
    $instance->access_token = $data['utoken'];

    return $instance;
  }



  function lineForUserTokenFile() {
    $emmId = isset($this->emmId) ? $this->emmId : "#fake, replace this" . time() . "#";
    $loginUrl = isset($this->login_url) ? $this->login_url : null;

    $ret = $this->FBId . "=" ."emmID{". $emmId .  "}";
    if (isset($loginUrl))
      $ret .= "login_url{" . $loginUrl . "}";
    $ret .= "utoken{" . $this->access_token . "}";

    return $ret;
  }

  function dump() {
    $ret = "";
    $ret .= "FBId=" . $this->FBId;
    $ret .= "\nemmId=" . getStrVal($this->emmId);
    $ret .= "\naccess_token=" . getStrVal($this->access_token);
    $ret .= "\ninterests=" . getStrVal($this->interests);

    //   $ret .= "\n" . ;
    // $ret .= "\n" . ;
    //        return print_r($this,true);
    return $ret;
  }

  function getFBId() {
    return $this->FBId;
  }

  function readFB($what, $dump = false) {
    $url = '/'. $this->FBId . "/" . $what;
    try {
      $this->$what = $this->facebook->api($url);
      $tmp = $this->$what;
      if (isset($tmp['data'])) {
        $tmp = $tmp['data'];
        if (count($tmp) > 0) {
          $this->log->addInfo("for FB uid: {$this->getFBId()} read {$what}" );
        }
      }

      $this->data[$what] = null; // meglio a null che indefinito
      foreach($tmp as $cur) {
        $this->data[$what][] = $cur;
      }
      if ($dump) {
        print_r($this->data[$what]);
      }
    } catch(Exception $e) {
      $this->log->addError( "graph API error with $url "
          . "\ntrapped at file, line:". __FILE__
          . ", ". __LINE__
          . "\n" . print_r($e.true));
    }
  }

  function getInterestsNameList() {
    if (!isset($this->interests)) {
      return [];
    }

    $data = $this->interests['data'];
    $interestNames = [];
    foreach($data as $interest) {
      $interestNames[] = $interest['name'];
    }
    return $interestNames;
  }

  function hasToken() {
    return isset($this->access_token);
  }


  function isTokenValid() {
    $token = new FBTokenBase($this->access_token,$this->facebook,$this->log);
    $ret = $token->isValid(true);
    if (!$ret) {
      $msg = "fb uid $this->FBId: token not valid ";
      if ($token->expires_at() != 0) {
        $msg .= "expired at " . timeFromUnix($token->expires_at());
      }
      $this->log->addInfo($msg);
    }
    return $ret;
  }

  function readProfile() {
    if (!$this->hasToken()) {
      $this->log->addWarning("cannot read profile for uid: " .
          $this->getFBId() . " because it does NOT have a user token");
      return[];
    }
    if (!$this->isTokenValid()) {
      $this->log->addWarning("cannot read profile for FB uid: " .
          $this->getFBId() . " Campaign UID: ". $this->getEMMId() . " because token expired");
      return[];
    }
    try {
      $url = '/'. $this->FBId;
      $this->data = $this->facebook->api($url,
          array('access_token' => $this->access_token));

      $this->readFB("friends", false);   // valid token
      $this->readFB("interests", false); // user_interests permission
      $this->readFB("movies", false); //
      $this->readFB("activities", false);
      $this->readFB("books", false);
      $this->readFB("groups", false);
      $this->readFB("music", false);


      // print_r($this->data);
      return $this->data;
    } catch(Exception $e) {
      print_r($e);
    }
  }

  function getEMMId() {
    return $this->emmId;
  }


  private $FBId;
  private $emmId;
  private $facebook;
  private $access_token;
  private $login_url;
  private $friends;
  private $data;
  private $interests;

  private $log;
}
