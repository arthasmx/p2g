<?php
require_once 'Core/Controller/Block.php';
class Addons_BannerBlockController extends Core_Controller_Block {

  function previewAction(){
    $this->view->preview = $this->_module->getModel('Banner')->preview( $this->getParam('username') );
  }

}