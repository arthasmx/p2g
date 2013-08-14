<?php
require_once 'Core/Controller/Block.php';
class Articles_BusinessBlockController extends Core_Controller_Block {

  function galleryFileListAction(){
    $this->view->files = $this->_module->getModel('Business/Files')->load_gallery();
  }

}