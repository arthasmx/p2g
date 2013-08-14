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

  function promocionesAction(){}

}