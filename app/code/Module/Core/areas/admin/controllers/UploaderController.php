<?php
require_once 'Local/Controller/Action.php';
class Core_UploaderController extends Local_Controller_Action   {

  function preDispatch(){
    $this->designManager()->setCurrentLayout('ajax');
  }

  function imageUploadAction(){
    echo $this->_module->getModel('Filesystem')->image_upload( $this->getRequest()->getParam('qqfile') );
    exit;
  }

  function imagesUploadAction(){
    echo $this->_module->getModel('Filesystem')->images_upload( $this->getRequest()->getParam('qqfile') );
    exit;
  }

  function upNcropAction(){
    exit;
  }

  function fileAction(){
    exit;
  }

  function audioAction(){
    exit;
  }

  function articleImagesAction(){
    echo $this->_module->getModel('Filesystem')->article_images_upload( $this->getRequest()->getParam('qqfile') );
    exit;
  }

}