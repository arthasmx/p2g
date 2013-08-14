<?php
require_once 'Core/Controller/Block.php';
class Addons_Languages_IndexBlockController extends Core_Controller_Block {

  function init() {}

  function languagesAction(){
    $this->view->languages = $this->_module->getModel("languages")->get_enabled_languages();
  }

}