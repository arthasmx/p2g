<?php
require_once 'Core/Controller/Block.php';
class Addons_Guestbook_IndexBlockController extends Core_Controller_Block {

  function latestAction(){
    $this->view->latest = $this->_module->getModel('Guestbook')->latest();
  }

}