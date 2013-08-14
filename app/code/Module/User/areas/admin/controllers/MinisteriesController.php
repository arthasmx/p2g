<?php
require_once 'Module/User/Controller/Action/Admin.php';
class MinisteriesController extends Module_User_Controller_Action_Admin {

  function preDispatch(){
    $this->designManager()->setCurrentLayout('admin');
  }

  function listAction(){
    $this->view->current_menu = array('menu'=>9);

    $libraries = App::module('Core')->getModel('Libraries');
    $libraries->ministeries();
    $libraries->json2();
    $libraries->jquery_ui_tabs("ministeries-tab" );
    $libraries->block_ui();

    $this->_module->getModel('Ministeries')->list_grid();
  }

  function listDataLoaderAction(){
    echo $this->_module->getModel('Ministeries')->jqGrid_admin_list( $this->getRequest()->getParams() );
    exit;
  }

  function jqgridMinisteriesAction(){
    echo $this->_module->getModel('Ministeries')->jqGrid_record_ministeries();
    exit;
  }

  function userAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $this->_helper->getHelper('ViewRenderer')->setScriptAction( "detail" );
    $this->view->user     = $this->_module->getModel('Ministeries')->get_user_ministeries( $this->getRequest()->getParam('user') );
    $this->view->username = $this->getRequest()->getParam('user');

    $this->view->form = $this->_module->getModel('Forms/Ministeries')->get( $this->view->user['ministeries'] );
  }

  function uploadPictureAction(){
    $this->_module->getModel('Ministeries')->upload_picture( $_FILES, $this->getRequest()->getParam('avatar'), $this->getRequest()->getParam('user') );
  }



  function saveAction(){
    $this->_module->getModel('Cud/Ministeries')->save( $this->getRequest()->getParams() );
  }

  function saveMultipleAction(){
    $this->_module->getModel('Cud/Ministeries')->save_multiple( $this->getRequest()->getParams() );
  }

  protected function get_breadcrumbs( $breadcrumb = null ){
    if( empty($breadcrumb)){
      return null;
    }

    return array( array('title'=> App::xlat($breadcrumb) ) );
  }

}