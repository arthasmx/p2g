<?php
require_once 'Core/Controller/Block.php';
class Articles_IndexBlockController extends Core_Controller_Block {

  function galleryFileListAction(){
    $this->view->files = $this->_module->getModel('Files')->load_article_gallery();

//    $session = App::module('Core')->getModel('Namespace');
  }

  function audioFileListAction(){
    $this->view->files = $this->_module->getModel('Files')->load_article_audio();
  }

  function filesFileListAction(){
    $this->view->files = $this->_module->getModel('Files')->load_article_files();
  }

}