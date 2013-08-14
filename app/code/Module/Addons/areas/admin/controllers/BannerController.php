<?php
require_once 'Module/Addons/Controller/Action/Admin.php';
class Addons_BannerController extends Module_Addons_Controller_Action_Admin {

  function listAction() {
    $this->view->current_menu = array('menu'=>18,'sub'=>19);
    $this->view->main_banner = $this->_module->getModel('Banner')->jqGrid_admin_main_Banner();
  }

  function listDataLoaderAction(){
    $this->designManager()->setCurrentLayout('ajax');
    echo $this->_module->getModel('Banner')->jqGrid_admin_list( $this->getRequest()->getParams() );
    exit;
  }



  function addAction(){
    $this->designManager()->setCurrentLayout('admin');
    $libraries = App::module('Core')->getModel('Libraries');

    $libraries->jquery_ui_tabs("add-banner-tabs");
    $libraries->plUploadQueue();
    $libraries->banner();
    $libraries->block_ui();

    $this->_module->getModel('Banner')->get_preview();

    $this->view->current_menu = array('menu'=>18,'sub'=>20);
  }

  function uploadAction(){
    $this->_module->getModel('Banner')->upload();
  }

  function previewAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $this->view->preview = $this->_module->getModel('Banner')->preview();
  }

  function userStatusAction(){
    echo $this->_module->getModel('Cud/Banner')->banner_status_change( $this->getRequest()->getParam('value'), $this->getRequest()->getParam('username') );
    exit;
  }

}