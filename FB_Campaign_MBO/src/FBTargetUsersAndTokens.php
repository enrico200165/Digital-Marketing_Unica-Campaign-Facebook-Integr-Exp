<?php

require_once 'PropertiesFile.php';


class FBTargetUsersAndTokens {
  
  const FNAME = "../data/usersTokens";
  
  function __construct($fname,$log) {
    $this->log = $log;
    $FBUTproperties = new PropertiesFile($fname, $log);
    foreach($FBUTproperties->getAllProperties() as $p => $val) {
      //echo "\n".$p . " = " . $val;
   
      $matches = [];
      preg_match ("/utoken{([^}]*)}/", $val, $matches );
      $this->users[$p]['utoken'] = $matches[1]; 
      
      $matches = [];
      preg_match ("/emmID{([^}]*)}/", $val, $matches );
      $this->users[$p]['emmID'] = $matches[1]; 
    }
  }

  function getUserToken($user) {
    return $this->users[$user]['utoken'];
  }
  
  function getEMMID($user) {
    return $this->users['emmID']['utoken'];
  }
  
  function dump() {
    foreach($this->users as $id => $u) {
      echo "\nFBId=$id token=". $this->getUserToken($id); 
    }
  }
  
  function getUsers() {
    return $this->users;
  }
  
  private $users;
  private $log;
}
