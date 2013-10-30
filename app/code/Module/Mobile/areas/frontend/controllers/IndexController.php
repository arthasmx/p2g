<?php
require_once 'Module/Mobile/Controller/Action/Frontend.php';

class Mobile_IndexController extends Module_Mobile_Controller_Action_Frontend {

  function preDispatch(){
    $this->designManager()->setCurrentLayout('mobile');
  }

  function indexAction(){
    die('forbidden');
  }

  function eventosAction(){}
  
  function articulosAction(){
    $this->view->articles = App::module('Articles')->getModel('Article')->latest( App::xlat('articulos'), 5 );
  }

  function promocionesAction(){}

  function importBusinessAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $resp = App::module('Articles')->getModel('Business')->get_business_to_sync_with_mobile_app();
    echo json_encode($resp);
    exit;
  }

}