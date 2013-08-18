<?php
require_once 'Module/Default/Controller/Action/Frontend.php';

class CategoriesController extends Module_Default_Controller_Action_Frontend {

  function listAction(){
    $this->view->categories = App::module('Addons')->getModel('Categories')->get_children_by_seo('etiquetas');
  }

  function categoryAction(){
    $this->view->categories = App::module('Addons')->getModel('Categories')->get_children_by_seo( $this->getRequest()->getParam('category') );
    $this->view->category   = $this->getRequest()->getParam('category');
  }

  function businessListByTagAction(){
    $this->view->business = App::module('Articles')->getModel('Business')->get_by_tag( $this->getRequest()->getParam('tag') );
    $this->view->category = $this->getRequest()->getParam('category');
    $this->view->tag      = App::module('Addons')->getModel('Categories')->get_category_by_seo( $this->getRequest()->getParam('tag') );
  }



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