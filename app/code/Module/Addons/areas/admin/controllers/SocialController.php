<?php
require_once 'Module/Addons/Controller/Action/Admin.php';
class Addons_SocialController extends Module_Addons_Controller_Action_Admin {


  private $business_shared = false; 

  function listAction() {
    $this->view->current_menu = array('menu'=>23,'sub'=>24);
    App::module('Core')->getModel('Namespace')->clear('business');

    $this->view->main_social = $this->_module->getModel('Social')->jqGrid_definition_resources();
  }

  function listDataLoaderAction(){
    $this->designManager()->setCurrentLayout('ajax');
    echo $this->_module->getModel('Social')->jqGrid_list( $this->getRequest()->getParams() );
    exit;
  }



  private function add_edit_shared_code(){
    $this->designManager()->setCurrentLayout('admin');
    $this->business_shared  = App::module('Core')->getModel('Namespace')->get( 'business' );
    $libraries = App::module('Core')->getModel('Libraries');

    $libraries->jquery_ui_tabs("add-social-tabs");
    $libraries->plUploadQueue();
    $libraries->social();
    $libraries->jquery_ui_datepicker( array('input#event') );
    $libraries->tags("tags");
    $libraries->block_ui();

    $libraries->colorbox();
    App::header()->add_jquery_events("
        jQuery('a#mainpix_preview').colorbox({width:'1050',height:'768',iframe:true});
        jQuery('span#article_preview').colorbox({width:'780',height:'768',iframe:true, href: '". App::base('/articles/preview/') ."'});
        ");
    $libraries->files_paginate_gallery('div.uploaded_images div.f-pagination a', '/social-files-paginate/paginate-gallery/', 'a.cBox-gallery', 'cBox-gallery');
  }

  function addAction(){
    $this->add_edit_shared_code();

    $this->view->form = $this->_module->getModel('Forms/Social')->get();

    $this->view->current_menu = array('menu'=>23,'sub'=>25);

    // temporary folder
    if( empty($this->business_shared->business['social']['folder']) ){
      $this->business_shared->business['social']['folder'] = strtotime(date('Y-m-d H:i:s'));
    }
  }

  function editAction(){
    $this->_module->getModel('Social')->edit( $this->getRequest()->getParam('id') );

    $this->add_edit_shared_code();
    $this->view->form         = $this->_module->getModel('Forms/Social')->get('edit');
    $this->view->current_menu = array('menu'=>23);
  }

  function saveAction(){
    $request  = $this->getRequest();
    $form     = $this->_module->getModel('Forms/Social')->get(@$_POST['action']);
    $answer   = "{'error':'true'}";

    if ( $request->isPost() ){
      if( $form->isValid($_POST) ){
        $answer = $this->_module->getModel('Cud/Social')->save($_POST);
      }else{
        $answer = App::module('Core')->getModel('Form')->get_json_error_form_fields($form);
      }
    }
    echo $answer;
    exit;
  }




  function uploadAction(){
    $this->_module->getModel('Cud/Social')->upload( $this->getRequest()->getParam('image_name') );
  }

  function reloadGalleryAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $this->view->files = $this->_module->getModel('Social')->reload_gallery();
  }

  function uploadMainPixAction(){
    $this->_module->getModel('Cud/Social')->upload_main_pix();
  }

  function mainPixPreviewAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $this->view->files = $this->_module->getModel('Social')->main_pix_preview();
  }

  function deleteImageAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $this->_module->getModel('Social')->delete_image( $this->getRequest()->getParam('image') );
  }


  function listingStatusAction(){
    $this->_module->getModel('Cud/Social')->update_field_value( 'status', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
  }

}