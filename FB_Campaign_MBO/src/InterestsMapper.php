<?php 

require 'vendor/autoload.php';
require_once 'PropertiesFile.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class InterestsMapper {

  function __construct($fname, Logger $log ) {
    $this->log = $log;
    $interestsProperties = new PropertiesFile($fname,$log);
    $interestsProperties->parsePropertiesFile(null, true);
    $mappings = $interestsProperties->getAllProperties();
    foreach ($mappings as $target => $mappeeInterestsAll) {
      //print_r(str_getcsv($mappeeInterests));
      $target = trim($target);
      $mappeeInterests = str_getcsv($mappeeInterestsAll);
      foreach ($mappeeInterests as $i ) {
        $i=trim($i);
        $this->map[$i] = $target;
      }
    }
  }

  function dump() {
    foreach ($this->map as $source => $dest) {
      print("\n\"$source\" -> \"$dest\"");
    }
  }

  function getMappedInterest($original) {
    if (isset($this->map[$original])) {
      //$this->log->addDebug("map $original on ".$this->map[$original]);
      return $this->map[$original];
    } else {
      $this->log->addWarning("interest $original does not have a mapping");
      return "#unmapped#";
    }
  }

  function getMappedInterestList($originalList) {    
    $mappedList = [];
    foreach($originalList as $i) {
      $mappedList[] = $this->getMappedInterest($i); 
    }
    return array_unique($mappedList);
  }
  
  
  
  function dummyTest() {
    foreach ($this->map as $source => $dest) {
      $this->log->addInfo("$source --maps-to--> " . $this->getMappedInterest($source));
    }
  }

  private $map;
  private  $log ;
}