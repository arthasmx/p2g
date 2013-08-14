<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Cud_Guestbook extends Module_Core_Repository_Model_Abstract {

  function sign($name=null, $email=null, $gender=null, $comment=null){
    App::module('Core')->getModel('Parser')->check_function_params( func_get_args() );

    $sign = sprintf("INSERT INTO guestbook(name, email, gender, comment, created, lang) VALUES('%s','%s','%s','%s','%s','%s');"
                    ,$name, $email, $gender, $comment, date('Y-m-d H:i:s'), App::locale()->getLang() );

    $this->_db->query($sign);
    $id = $this->_db->lastInsertId();

    return empty($id)? null: $id;
  }

}