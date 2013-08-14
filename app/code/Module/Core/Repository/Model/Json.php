<?php
class Module_Core_Repository_Model_Json extends Core_Model_Repository_Model{

  function encode( $data=null ){
    if( empty($data) ){
      return false;
    }
    return $this->json()->encode($data);
  }

  function decode( $data=null, $stripslashes=false ){
    if( empty($data) ){
      return false;
    }
    if( $stripslashes ){
      $data = stripslashes($data);
    }    
    return $this->json()->decode($data); 
  }

  private function json() {
    require_once("Zend/Json.php");
    return new Zend_Json;
  }

}