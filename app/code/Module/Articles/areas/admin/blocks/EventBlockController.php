<?php
require_once 'Core/Controller/Block.php';
class Articles_EventBlockController extends Core_Controller_Block {

  function galleryFileListAction(){
    $this->view->files = $this->_module->getModel('Event/Files')->load_gallery();
  }

}