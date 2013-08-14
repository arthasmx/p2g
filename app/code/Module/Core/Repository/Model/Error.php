<?php
class Module_Core_Repository_Model_Error extends Core_Model_Repository_Model{

  function render($msg='',$type='exception'){
    switch($type){
      case 'exception':
        $this->_module->exception( $msg );
        break;
      case 'json':
        die('{"status":false, "message":"'. $msg .'", "css_class":"error"}');
        break;
      case 'null':
        return null;
        break;
      case 'false':
        return false;
        break;
      default:
        $this->_module->exception( App::xlat('EXC_error_type_was_not_provided') );
        break;
    }
  }

}