<?php

require 'vendor/autoload.php';
//require_once 'PropertiesFile.php';
//require_once 'utils.php';
//require_once 'FBUser.php';
//require_once 'FBUsersTokens.php';
// require_once 'EVFBUtils.php';
//require_once 'FBTokenBase.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


/**
 * At the moment it does practically nothing and its existence is
 * justfied  only because it reduceces the amount of code in the main
 * file
 * @author enrico200165@gmail.com
 *
 */
class FBApplication {

  function __construct($appInfoArray, $facebook,Logger $log) {

    $this->appID = $appInfoArray['appId'];
    $this->appSecret = $appInfoArray['secret'];
    $this->facebook = $facebook;
    $this->log = $log;

  }


  /**
   * Reads, if any, the test users of the application and writes
   * them in a file that might be re-used, after having being completed
   * and corrected manually, to reduce manual work
   */
  function readTestUsers($readProfile, $writeSampleFile) {
    // https://graph.facebook.com/APP_ID/accounts/test-users?access_token=APP_ACCESS_TOKEN
    $url = "/" . $this->appID . "/accounts/test-users";
    $testUsersData = $this->facebook->api($url);//,array('access_token' => SAMPLE_UTOKEN));

    foreach ($testUsersData['data'] as $ud) {
      $this->log->addDebug("read test user $ud[id]");
      $u= new FBUser($ud['id'], $this->facebook,$this->log, $ud);
      if ($readProfile) {
        $u->readProfile();
      }
      $testUsers[] = $u;
      $lines[] = $u->lineForUserTokenFile(); // only used to write files, no "if" to keep code simple
    }
    if ($writeSampleFile) {
      linesArrayToTxtFile("../data/FBUsersTokensTestUsersSample.properties", $lines);
    }
    $this->testUsers = $testUsersData['data'];
    return $this->testUsers;
  }


  function getTestusersArray() {
    return $this->testUsers;
  }


  private
  $appID,
  $appSecret,
  $facebook,
  $log,
  $testUsers;
}
