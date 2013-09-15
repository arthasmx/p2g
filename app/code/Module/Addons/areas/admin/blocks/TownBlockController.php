<?php
require_once 'Core/Controller/Block.php';
class Addons_TownBlockController extends Core_Controller_Block {

  function sectionsAction(){
    $this->view->sections = $this->_module->getModel('Cities')->get_sections();
  }

  function galleryAction(){
    $this->view->files = $this->_module->getModel('Cities')->load_gallery();
  }

  function sectionGalleryAction(){
    $this->view->files = $this->_module->getModel('Cities')->section_load_gallery();
  }

}