<?php
require_once 'Module/Addons/Controller/Action/Frontend.php';
class Addons_SocialController extends Module_Addons_Controller_Action_Frontend   {

  function preDispatch(){}

  function previewAction(){
    $this->view->images = $this->_module->getModel('Social')->get_event_images( $this->getRequest()->getParam('id') );
  }

}