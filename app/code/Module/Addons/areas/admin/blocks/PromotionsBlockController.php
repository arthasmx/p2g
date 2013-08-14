<?php
require_once 'Core/Controller/Block.php';
class Addons_PromotionsBlockController extends Core_Controller_Block {

  function previewAction(){
    $this->view->preview = $this->_module->getModel('Promotions')->preview( $this->getParam('id') );
  }

}