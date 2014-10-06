<?php 

require 'vendor/autoload.php';

use Monolog\Logger;

require_once 'FBUser.php';
require_once 'InterestsMapper.php';

/**
 * Developed in a hurry just to group operations on a list of users
 * @author Administrator
 *
 */

class FBUsersCollection {

  function __construct($fb, $log) {
    $this->facebook = $fb;
    $this->log = $log;
  }

  public static function FromFileArrays($usersArray, Facebook $facebook,Logger $log ) {

    $instance = new self($facebook, $log);

    foreach($usersArray as $id => $val) {
      $u = FBUser::FromDataFile($id,$val,$facebook,$log);
      $u->readProfile();
      $instance->users[] = $u;
    }
    return $instance;
  }

  function dump() {
    foreach($this->users as $u) {
      $this->log->addInfo("\n".$u->dump() . "\n");
    }
  }

  function getUsers() {
    return $this->users;
  }

  function createUpdatesList(InterestsMapper $mapper) {
    $updates = [];
    foreach($this->users as $u) {
      $origInterests = $u->getInterestsNameList();
      $mapped = $mapper->getMappedInterestList($origInterests);
      //print_r($origInterests); print_r($mapped);
      $updates[] = array($u->getFBId(), $u->getEMMId(), implode(":", $mapped));
    }
    return $updates;
  }


  private $users;
  private $facebook;
  private $log;
}
