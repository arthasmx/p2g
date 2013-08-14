<?php
require_once 'Core/Controller/Block.php';
class Acl_Login_IndexBlockController extends Core_Controller_Block {

  function topBarLoginAction(){
    $this->view->top_bar_form = $this->_module->getModel('Acl/Forms/TopBarLogin')->get();
  }

}