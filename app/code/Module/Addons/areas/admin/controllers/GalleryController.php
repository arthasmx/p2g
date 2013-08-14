<?php
require_once 'Module/Addons/Controller/Action/Admin.php';
class Addons_GalleryController extends Module_Addons_Controller_Action_Admin {

  function preDispatch(){
    $this->designManager()->setCurrentLayout('ajax');
  }

  function representAction(){
    echo $this->_module->getModel('Gallery')->represent( $this->getRequest()->getParam('id'), $this->getRequest()->getParam('qqfile') );
    exit;
  }

  function collageAction(){
    echo $this->_module->getModel('Gallery')->collage( $this->getRequest()->getParam('id'), $this->getRequest()->getParam('qqfile') );
    exit;
  }

}