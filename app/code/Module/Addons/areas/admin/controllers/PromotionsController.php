<?php
require_once 'Module/Addons/Controller/Action/Admin.php';
class Addons_PromotionsController extends Module_Addons_Controller_Action_Admin {

  function listAction() {
    $this->view->current_menu    = array('menu'=>15,'sub'=>16);
    $business = App::module('Core')->getModel('Namespace')->get( 'business' );
    unset($business->business['promotions']);

    $this->view->main_promotions = $this->_module->getModel('Promotions')->jqGrid_admin_main_promotions();
  }

  function listDataLoaderAction(){
    $this->designManager()->setCurrentLayout('ajax');
    echo $this->_module->getModel('Promotions')->jqGrid_list( $this->getRequest()->getParams() );
    exit;
  }



  function addAction(){
//    $business = App::module('Core')->getModel('Namespace')->get( 'business' );
//    unset($business->business['promotions']);

    $this->designManager()->setCurrentLayout('admin');
    $libraries = App::module('Core')->getModel('Libraries');

    $libraries->jquery_ui_tabs("add-promotions-tabs");
    $libraries->plUploadQueue();
    $libraries->promotions();
    $libraries->jquery_ui_datepicker( array('input#start', 'input#finish') );
    $libraries->block_ui();

    $this->view->form         = $this->_module->getModel('Forms/Promotions')->get();
    $this->view->current_menu = array('menu'=>15,'sub'=>17);
  }

  function editAction(){
    $session = App::module('Core')->getModel('Namespace')->get( 'business' );
    unset($session->business['promotions']);

    $promotion = $this->_module->getModel('Promotions')->edit( $this->getRequest()->getParam('id') );

    $libraries = App::module('Core')->getModel('Libraries');

    $libraries->jquery_ui_tabs("add-promotions-tabs");
    $libraries->plUploadQueue();
    $libraries->promotions();
    $libraries->jquery_ui_datepicker( array('input#start', 'input#finish') );
    $libraries->block_ui();

    $this->view->form         = $this->_module->getModel('Forms/Promotions')->get( 'edit' );
    $this->view->current_menu = array('menu'=>15);
    $this->view->url = $promotion['onclick_url'];
  }

  function uploadAction(){
    $this->_module->getModel('Promotions')->upload();
  }

  function previewAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $this->view->preview = $this->_module->getModel('Promotions')->preview();
  }



  function saveAction(){
    $request = $this->getRequest();
    $form    = $this->_module->getModel('Forms/Promotions')->get();
    $answer  = "{'error':'true'}";

    if ( $request->isPost() ){
     if( $form->isValid($_POST) ){
       $answer = $this->_module->getModel('Cud/Promotions')->save($_POST);
     }else{
       $answer = App::module('Core')->getModel('Form')->get_json_error_fields($form);
     }
    }
    echo $answer;
    exit;
  }



  function listingStatusAction(){
    $this->_module->getModel('Cud/Promotions')->update_field_value( 'status', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
  }

}