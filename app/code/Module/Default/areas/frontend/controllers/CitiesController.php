<?php
require_once 'Module/Default/Controller/Action/Frontend.php';

class CitiesController extends Module_Default_Controller_Action_Frontend {

  function townAction(){
    $this->view->town = App::module('Addons')->getModel('Cities')->section( $this->getRequest()->getParam('town'), $this->getRequest()->getParam('section') );
  }

}