<?php
class Module_Core_Repository_Model_Flashmsg extends Core_Model_Repository_Model {

  protected $_messages   = array();
  protected $_namespace  = null;

  public function init() {
    $this->_namespace = $this->_module->getModel('Namespace')->get( get_class($this) );
    $this->load_namespace_messages();
  }


  protected function load_namespace_messages() {
    $this->_messages = $this->_namespace->messages;
  }

  protected function save_messages_to_namespace() {
    $this->_namespace->messages = $this->_messages;
  }

  public function error($msg) {
    $this->add('error',$msg);
  }

  public function info($msg) {
    $this->add('info',$msg);
  }

  public function warning($msg) {
    $this->add('warning',$msg);
  }

  public function success($msg) {
    $this->add('success',$msg);
  }

  public function add($type=null, $msg=null) {
    if(empty($type) || empty($msg)){
      return null;
    }
    $this->_messages[$type][] = array( 'time' => time(), 'msg' => $msg );
    $this->save_messages_to_namespace();
  }

  function get_flash_messages($type=false){
    $messages = array();

    if (!$type) {
      foreach ( array_keys((array)$this->_messages) as $msgType ) {
        $msgs = $this->get_flash_messages($msgType);
        if (count($msgs)>0) $messages[$msgType]=$msgs;
      }
      $this->_messages = array();
      $this->save_messages_to_namespace();

    }else{
      if (isset($this->_messages[$type])) {
        $messages=$this->_messages[$type];
        unset($this->_messages[$type]);
        $this->save_messages_to_namespace();
      }
    }

    if (is_array($messages) && count($messages)>0) {
      return $messages;
    }
    return false;
  }

}