<?php
require_once 'Module/Addons/Controller/Action/Admin.php';
class Addons_TownsController extends Module_Addons_Controller_Action_Admin {

  function listAction() {
    $this->view->current_menu    = array('menu'=>26,'sub'=>27);
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
    App::module("Acl")->getModel('acl')->check_user_section_access();

    $this->designManager()->setCurrentLayout('admin');
    $this->view->current_menu = array('menu'=>26,'sub'=>28);

    $libraries = App::module('Core')->getModel('Libraries');
    $libraries->towns();

    $town_session = App::module('Core')->getModel('Namespace')->get( 'town' );
    if( ! empty($town_session->town['sections']) ){
      //$town_session->town = array();
    }

    $tab_options = ( empty($town_session->town['article_id']) ? array('disabled'=>'[1,2,3]') : array() );
    $libraries->jquery_ui_tabs("add-town-tabs", $tab_options );
    $libraries->block_ui();
    $libraries->tags("tags");

    $libraries->ckeditor('article',array('toolbar'=>'articleCreate', 'height'=>'31em', 'language'=>App::locale()->getLang() ));

    $this->view->basic_data = $this->_module->getModel('Forms/Town')->get();

    $libraries->colorbox();
    $libraries->plUploadQueue();
    $libraries->plUpload_town_upload_files();

    App::header()->add_jquery_events("
      jQuery('a#mainpix_preview').colorbox({width:'1050',height:'768',iframe:true});
      jQuery('span#article_preview').colorbox({width:'780',height:'768',iframe:true, href: '". App::base('/articles/preview/') ."'});
    ");

    $libraries->placeholder();
    $libraries->clone_elements('#links_ge', @$town_session->town['addons']['links'] );

    $libraries->files_paginator();
    $libraries->files_paginate_gallery('div.uploaded_images div.f-pagination a', '/towns-files-paginate/paginate-gallery/', 'a.cBox-gallery', 'cBox-gallery');

    $libraries->google_map_to_pick_coordinates("googleMap",@$town_session->town['addons']['map']);
  }

  function editAction(){
    $this->view->current_menu = array('menu'=>26,'sub'=>28);
    $this->designManager()->setCurrentLayout('admin');
    App::header()->addLink(App::skin('/css/town.css'),array( "rel"=>"stylesheet" ));

    $session = App::module('Core')->getModel('Namespace');
    $session->clear('town');
    $town_session = $session->get('town');
    $user_session = $session->get('user');

    $model  = $this->_module->getModel('Cities');
    $town   = $model->town( $this->getRequest()->getParam('seo'),'session' );
    $tags   = $model->get_tags( $town['seo'], TRUE );
    $addons = $model->get_addons( $town['seo'], TRUE );

    $town_session->town = $town;
    $town_session->town['folders'] = $model->set_town_folders($town['folder']);
    $town_session->town['addons']  = $addons;
    $town_session->town['tags']    = $tags;

    $town_session->town['article_id'] = $town['id'];
    $town_session->town['username']   = $user_session->user['username'];
    $town_session->town['pass']       = "";

    $model->main_pix_preview();

    $this->view->basic_data = $this->_module->getModel('Forms/Town')->get('edit');



    $libraries = App::module('Core')->getModel('Libraries');
    $libraries->towns();

    $libraries->jquery_ui_tabs("add-town-tabs" );

    $libraries->block_ui();

    $libraries->tags("tags");

    $libraries->ckeditor('article',array('toolbar'=>'articleCreate', 'height'=>'31em', 'language'=>App::locale()->getLang() ));

    $libraries->colorbox();
    $libraries->plUploadQueue();
    $libraries->plUpload_town_upload_files();

    $libraries->files_paginator();
    $libraries->files_paginate_gallery('div.uploaded_images div.f-pagination a', '/towns-files-paginate/paginate-gallery/', 'a.cBox-gallery', 'cBox-gallery');

    $libraries->placeholder();
    $cloned = array_merge( (empty($addons['link'])?array():$addons['link']), (empty($addons['video'])?array():$addons['video']) );
    $libraries->clone_elements('#links_ge', ( is_array($cloned)==false?null:$cloned ) );
    $libraries->google_map_to_pick_coordinates("googleMap",@$addons['map']);

    App::header()->add_jquery_events("
      jQuery('a#mainpix_preview').colorbox({width:'1050',height:'768',iframe:true});
      jQuery('span#article_preview').colorbox({width:'780',height:'768',iframe:true, href: '". App::base('/articles/preview/') ."'});
    ");

  }



  function saveAction(){
    $request = $this->getRequest();
    $form   = $this->_module->getModel('Forms/Town')->get(@$_POST['action']);
    $answer = "{'error':'true'}";

    if ( $request->isPost() ){
      if( $form->isValid($_POST) ){
        $answer = $this->_module->getModel('Cud/Town')->save($_POST);
      }else{
        $answer = App::module('Core')->getModel('Form')->get_json_error_form_fields($form);
      }
    }
    echo $answer;
    exit;
  }

  function sectionValueAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $this->view->section = $this->getRequest()->getParam('section');
    $this->view->form    = $this->_module->getModel('Forms/Section')->get( $this->view->section );
  }

  function saveSectionAction(){
    $request = $this->getRequest();
    $form    = $this->_module->getModel('Forms/Section')->get($request->getParam('town_section'));
    $answer  = "{'error':'true'}";

    if ( $request->isPost() ){
      if( $form->isValid($_POST) ){
        $answer = $this->_module->getModel('Cud/Town')->save_section($_POST);
      }else{
        $answer = App::module('Core')->getModel('Form')->get_json_error_fields($form);
      }
    }
    echo $answer;
    exit;
  }

  function quitSectionAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $this->_module->getModel('Cud/Town')->quit_section($this->getRequest()->getParam('section'));
  }


  function mainPixPreviewAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $this->view->files = $this->_module->getModel('Cities')->main_pix_preview();
  }

  function uploadMainPixAction(){
    $this->_module->getModel('Cities')->upload_main_pix();
  }

  function imagesToGalleryAction(){
    $this->_module->getModel('Cities')->image_to_gallery();
  }

  function reloadGalleryAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $this->view->files = $this->_module->getModel('Cities')->load_gallery();
  }

  function deleteImageAction(){
    $this->_module->getModel('Cities')->delete_image( $this->getRequest()->getParam('image') );
  }

  function linksRelAction(){
    $this->_module->getModel('Cud/Town')->add_link( $this->getRequest()->getParam('links') );
  }

  function saveCoordinatesAction(){
    $this->_module->getModel('Cud/Town')->add_map_coordinates( $this->getRequest()->getParam('cors') );
  }

  function delCoordinatesAction(){
    $this->_module->getModel('Cud/Town')->del_map_coordinates();
  }

}