<?php
require_once 'Core/Controller/Block.php';
class Addons_SocialBlockController extends Core_Controller_Block {

  function reloadGalleryAction(){
    $this->view->files = $this->_module->getModel('Social')->reload_gallery();
  }

}