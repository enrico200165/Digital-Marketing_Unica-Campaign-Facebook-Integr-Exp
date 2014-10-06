<?php  

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class PropertiesFile {

  function __construct($filePathName , Logger $log) {
    $this->log = $log;

    $this->fname = $filePathName;
    $this->properties = null;
    if (isset($this->fname)) {
      $this->parsePropertiesFile($this->fname);
    }
  }

  function parsePropertiesString($txtProperties) {
    // http://blog.rafaelsanches.com/2009/08/05/reading-java-style-properties-file-in-php/
    $result = array();
    $lines = split("\n", $txtProperties);
    $key = "";
    $isWaitingOtherLine = false;
    foreach($lines as $i=>$line) {
      if(empty($line) || (!$isWaitingOtherLine && strpos($line,"#") === 0))
        continue;
      if(!$isWaitingOtherLine) {
        $key = substr($line,0,strpos($line,'='));
        $value = substr($line,strpos($line,'=') + 1, strlen($line));
      } else {
        $value .= $line;
      } /* Check if ends with single '\' */
      if(strrpos($value,"\\") === strlen($value)-strlen("\\")) {
        $value = substr($value, 0, strlen($value)-1)."\n";
        $isWaitingOtherLine = true;
      } else {
        $isWaitingOtherLine = false;
      }
      $result[$key] = $value;
      unset($lines[$i]);
    }
    // print_r($result);
    return $result;
  }

  function parsePropertiesFile($fname, $force = false) {
    if (!isset($fname)) {
      $fname = $this->fname;
    }
    if (isset($this->properties) and !$force) {
      $this->log->addWarning($this->fname . " already parsed");
      return;
    }
    $users = file_get_contents($fname . ".properties");
    $this->properties = $this->parsePropertiesString($users);
  }

  function dump() {
    if (!isset($this->properties) ) {
      $this->log->addWarning("dumping unparsed properties: " . $this->fname );
      return "";
    }
    foreach ($this->properties as $prop => $val) {
      print("\n".$prop . "=" . $val);
    }
  }

  function get($propertyName) {
    if (!isset($this->properties)) {
      $this->log->addError("uproperties not parsed: " . $this->fname);
      return "";
    }
    if (!isset($this->properties[$propertyName])) {
      $this->log->addError("property not defined: " . $propertyName);
      return null;
    }
    return $this->properties[$propertyName];
  }

  function getAllProperties() {
    return $this->properties;
  }


  const SAMPLE_IGNORE = 'fbss';

  private $fname;
  private $properties;
  private $log;
};




