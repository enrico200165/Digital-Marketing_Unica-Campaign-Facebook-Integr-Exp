<?php

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once 'PropertiesFile.php';


class DBUtils {

  function __construct($cfgFName, Logger $log) {
    $this->log = $log;
    $this->cfgFile = $cfgFName;
    $this->conn = null;
    $this->db_properties = new PropertiesFile($this->cfgFile, $this->log);
    $this->fb_interests_table = $this->db_properties->get('fb_interests_table');
    $this->fb_interests_column = $this->db_properties->get('fb_interests_column');
  }

  function updateCampaignProfiles($updates) {
    $table = $this->fb_interests_table;
    $column = $this->fb_interests_column;

    if (!isset($this->conn)) {
      $this->connectToDB();
    }
    try {
      $this->conn->beginTransaction();
      foreach($updates as $update) {
        $val = $update[2];
        if (!isset($val) or strlen($val) <=0 ) {
          continue;
        }
        $EMMId = $update[1];
        $stmt = "UPDATE [dcc].[dbo].[$table]"
        . " SET [$column] = '$val'"
        . " WHERE Indiv_ID = '$EMMId'";
        // $this->log->addDebug("statement:\n\"$stmt\"");
        $this->conn->exec($stmt);
        $this->conn->commit();

        // for debugging
        $stmt = "SELECT [$column] FROM [dcc].[dbo].[$table] WHERE Indiv_ID = '$EMMId'";
        // $this->log->addDebug("statement:\n\"$stmt\"");
        $ret = $this->conn->query($stmt);
        $result = $ret->fetchAll(PDO::FETCH_ASSOC);
        $newVal = $result[0][$column];
        $this->log->addInfo("interests of user with Campaign ID $EMMId set, from Facebook, to $newVal");
      }
    } catch(Exception $e) {
      if (strpos($e->getMessage(), 'Invalid column name') !== false) {
        $this->log->addError("column $column not found in table $table ");
      } else {
        print_r($e);
      }
    }
    $this->conn = null;
    return;
  }


  function getProp($property) {
    return $this->db_properties->get($property);
  }

  function connectToDB() {
    if (isset($this->conn))
      return $this->conn;

    $serverName = $this->getProp('server_name');
    $dbName = $this->getProp('db_name');
    $uid = $this->getProp('db_user');
    $pwd = $this->getProp('db_pwd');

    try {
      $this->conn = new PDO( "sqlsrv:server=$serverName ; Database=dcc", "sa", "unica*03");
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    } catch(Exception $e) {
      $this->log->addError("fallisce");
      $this->log->addError(print_r( $e->getMessage(),true ));
      $this->conn = null;
    }
    return $this->conn;
  }


  function simpleTestDB() {
     

    $this->log->addInfo("comincio");
    $tsql = "SELECT * FROM dbo.indiv_contact WHERE Indiv_ID < 10";

    $stmt = $this->connectToDB()->query($tsql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
      print_r($row);
    }
    $productCount = count($result);
    if($productCount > 0) {
      print("ci sono prodotti: " . $productCount);
    } else {
      print("NIENTE");
    }
  }

  /**
   * NOT used
   * @param unknown_type $tableName
   */
  function createFBUsersTable($tableName = "FBUsers") {
    $stmt =
    "CREATE TABLE [dcc].[dbo].[FBUsers] ("
    .	" [FB_ID] [varchar](256) NOT NULL,"
    . " [UC_ID] [int] NOT NULL,"
    .	" [FB_app_token] [varchar](256) NULL,"
    .	" [FB_user_name] [varchar](64) NULL,"
    .	" [FB_user_surname] [varchar](64) NULL,"
    .	" [FB_Interests] [varchar](256) NULL,"
    . " PRIMARY KEY (FB_ID)"
    . " )"  ;

    $log->addInfo($stmt);
    $conn = connectToDB();
    $conn->query($stmt);

    $conn = null;
  }



  function tableExists($tableName, $conn = null) {
    global $log;
    if ($conn == null) {
      $conn = connectToDB();
    }
    try {
      $result = $conn->exec("SELECT count(*) FROM $tableName");
      $tableExists = (gettype($result) == "integer")? true:false;
    } catch (Exception $e) {
      // $log->addInfo(print_r($e,true));
      return false;
    }
    return $tableExists;
  }

  private $cfgFile;
  private $log;
  private $conn;
  private $db_properties;
  private $fb_interests_table;
  private $fb_interests_column;
}

