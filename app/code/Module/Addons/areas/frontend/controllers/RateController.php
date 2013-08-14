<?php
require_once 'Module/Addons/Controller/Action/Frontend.php';
class Addons_RateController extends Module_Addons_Controller_Action_Frontend   {

  function preDispatch(){
    $this->designManager()->setCurrentLayout('ajax');
  }

  function rateAction(){
    echo $this->_module->getModel('Cud/Rating')->rate( $this->getRequest()->getParam('id'), $this->getRequest()->getParam('rate') );
    exit;
  }

}