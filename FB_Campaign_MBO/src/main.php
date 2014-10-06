<?php

ini_set('display_errors','stderr');
set_include_path( ".." . PATH_SEPARATOR . get_include_path());
date_default_timezone_set('UTC');

require 'vendor/autoload.php';

require_once 'PropertiesFile.php';
require_once 'utils.php';
require_once 'FBUser.php';
require_once 'FBTargetUsersAndTokens.php';
require_once 'FBUsersCollection.php';
require_once 'EVFBUtils.php';
require_once 'FBTokenBase.php';
require_once 'FBApplication.php';
require_once 'InterestsMapper.php';
require_once 'DBUtils.php';



use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// echo __LINE__;



define("EMM_DEMO_DIR", "C:/Unica/Campaign/partitions/partition1/Demo");
define("EMM_BROWSER","C:/Program Files (x86)/Mozilla Firefox/firefox.exe");


// -------------------------------------------------------------------------
$log = new Logger("main_logger");
$log->pushHandler(new StreamHandler('../logs/detailed.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler('php://stdout', Logger::WARNING));

$log->addInfo("working dir: " . getcwd());


$interestsMapper = new InterestsMapper("../data/interestsMap",$log); // $interestsMapper->dummyTest();

try {

  $appProperties = new PropertiesFile("../data/appInfo", $log);
  $appInfo = array(
      'appId' => $appProperties->get('appId'),
      'secret' => $appProperties->get('appSecret'),
  );

  // currently not used
  $appToken = $appProperties->get('AppToken');
  
  
  $facebook = new Facebook($appInfo);

  // not strictly necessary
  $fbApp = new FBApplication($appInfo,$facebook,$log);

  
  // test reading from FB the list of test users
  // $testUsers = $fbApp->readTestUsers(true, true);print_r($testUsers);

  // read users from file
  $keyUsers = new FBTargetUsersAndTokens("../data/usersTokens",$log);
  $fbUsersColl = FBUsersCollection::FromFileArrays($keyUsers->getUsers() ,$facebook,$log);
  
  $updates = $fbUsersColl->createUpdatesList($interestsMapper);
  // print_r($updates);
  
  $db = new DBUtils("../data/db",$log);
  $db->connectToDB();
  $db->updateCampaignProfiles($updates);
  
  
  echo "ended, normally";
} catch (Exception $e) {
  print_r($e);
  $log->addError($e);
}

?>