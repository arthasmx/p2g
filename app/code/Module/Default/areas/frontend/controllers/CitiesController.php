<?php
require_once 'Module/Default/Controller/Action/Frontend.php';

class CitiesController extends Module_Default_Controller_Action_Frontend {

  function townAction(){
    $this->view->town = App::module('Addons')->getModel('Cities')->section( $this->getRequest()->getParam('town'), $this->getRequest()->getParam('section') );
  }

  function municipalityAction(){
    $this->view->sections = App::module('Addons')->getModel('Cities')->get_town_sections( $this->getRequest()->getParam('town'), false );
    $this->view->town     = ucfirst( str_replace('-', ' ', $this->getRequest()->getParam('town')) );
  }

  function townListAction(){
    $this->view->towns = App::module('Addons')->getModel('Cities')->get_towns_from_city('mazatlan');
  }


}