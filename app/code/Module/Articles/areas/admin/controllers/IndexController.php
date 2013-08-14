<?php
require_once 'Module/Articles/Controller/Action/Admin.php';
class Articles_IndexController extends Module_Articles_Controller_Action_Admin {

	 function preDispatch(){
	   $this->designManager()->setCurrentLayout('ajax');
	 }

	 function listAction() {
	   App::module("Acl")->getModel('acl')->check_user_section_access();

    $this->designManager()->setCurrentLayout('admin');
    $this->view->current_menu = array('menu'=>6,'sub'=>7);

    App::module('Core')->getModel('Namespace')->clear('article');
    App::module('Core')->getModel('Libraries')->articles();
    App::module('Core')->getModel('Libraries')->json2();

    $this->_module->getModel('Article')->article_list_grid();
	 }

  function listDataLoaderAction(){
	  echo $this->_module->getModel('Article')->jqGrid_admin_list( $this->getRequest()->getParams() );
    exit;
  }

  function jqgridTypesAction(){
    echo $this->_module->getModel('Article')->jqGrid_record_types();
    exit;
  }



	 function addAction(){
	   App::module("Acl")->getModel('acl')->check_user_section_access();

	   $this->designManager()->setCurrentLayout('admin');
	   $this->_helper->getHelper('ViewRenderer')->setScriptAction( "form-article" );
    $this->view->current_menu = array('menu'=>6,'sub'=>8);

    $libraries = App::module('Core')->getModel('Libraries');
    $libraries->articles();

    $article_session = App::module('Core')->getModel('Namespace')->get( 'article' );
    if( ! empty($article_session->article['section']) ){
      $article_session->article =array();
    }

    $tab_options     = ( empty($article_session->article['article_id']) ? array('disabled'=>'[1,2,3]') : array() );

    $libraries->jquery_ui_tabs("add-art-tabs", $tab_options );

    $libraries->jquery_ui_datepicker( array('input#event_date', 'input#publicate_at', 'input#stop_publicate') );
    $libraries->block_ui();

    $libraries->tags("tags");

    $libraries->ckeditor('article',array('toolbar'=>'articleCreate', 'height'=>'31em', 'language'=>App::locale()->getLang() ));

    $this->view->basic_data   = $this->_module->getModel('Forms/Article')->get();

    $libraries->colorbox();
    $libraries->plUploadQueue();
    $libraries->plUpload_article_upload_files();

    App::header()->add_jquery_events("
      jQuery('a#mainpix_preview').colorbox({width:'1050',height:'768',iframe:true});
      jQuery('span#article_preview').colorbox({width:'780',height:'768',iframe:true, href: '". App::base('/articles/preview/') ."'});
    ");

    $libraries->files_paginator();
    $libraries->files_paginate_gallery('div.uploaded_images div.f-pagination a', '/articles-files-paginate/paginate-gallery/', 'a.cBox-gallery', 'cBox-gallery');

    $libraries->placeholder();
    $libraries->clone_elements('#links_ge', @$article_session->article['addons']['links'] );

    $libraries->google_map_to_pick_coordinates("googleMap",@$article_session->article['addons']['map']);
	 }

	 function editAction(){

	   $session = App::module('Core')->getModel('Namespace');
	   $session->clear('article');
	   $session->clear('mainpix');
	   $session = $session->get('article');

	   $model   = $this->_module->getModel('Article');
	   $article = $model->edit( $this->getRequest()->getParam('id') );
	   $tags    = $model->get_tags( $article['article_id'], TRUE );
	   $addons  = $model->get_article_addons( $article['article_id'], TRUE );

	   $session->article['folders'] = $model->set_article_folders($article['article_id'], $article['created']);
	   $session->article['addons']  = $addons;

	   $this->_module->getModel('Files')->main_pix_preview();

	   $this->designManager()->setCurrentLayout('admin');
	   $this->view->current_menu = array('menu'=>6);

	   $this->view->addons = $addons;
    $this->view->basic_data   = $this->_module->getModel('Forms/Article')->get();
    $this->view->basic_data->getElement('article_id')->setAttrib('data-id', $article['article_id']);
    $this->view->basic_data->populate( array( 'article_id'     => $article['article_id'],
                                              'title'          => $article['title'],
                                              'seo'            => $article['seo'],
                                              'event_date'     => ($article['event_date']=='0000-00-00 00:00:00') ? null:$article['event_date'],
                                              'publicate_at'   => ($article['publicated']=='0000-00-00') ? null:$article['publicated'],
                                              'stop_publicate' => ($article['stop_publication']=='0000-00-00') ? null:$article['stop_publication'],
                                              'tags'           => $tags,
                                              'language'       => $article['language'],
                                              'mobile'         => $article['mobile'],
                                              'promote'        => $article['promote'],
                                              'type'           => 'articulos',
                                              'article'        => $article['article'] ) );

    $libraries = App::module('Core')->getModel('Libraries');
    $libraries->articles();

    $libraries->jquery_ui_tabs("add-art-tabs" );

    $libraries->jquery_ui_datepicker( array('input#event_date', 'input#publicate_at', 'input#stop_publicate') );
    $libraries->block_ui();

    $libraries->tags("tags");

    $libraries->ckeditor('article',array('toolbar'=>'articleCreate', 'height'=>'31em', 'language'=>App::locale()->getLang() ));

    $libraries->colorbox();
    $libraries->plUploadQueue();
    $libraries->plUpload_article_upload_files();

    $libraries->files_paginator();
    $libraries->files_paginate_gallery('div.uploaded_images div.f-pagination a', '/articles-files-paginate/paginate-gallery/', 'a.cBox-gallery', 'cBox-gallery');

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
//    App::module("Acl")->getModel('acl')->check_user_section_access();

    $request = $this->getRequest();
    $form   = $this->_module->getModel('Forms/Article')->get();
    $answer = "{'error':'true'}";

    if ( $request->isPost() ){
     if( $form->isValid($_POST) ){
       $answer = $this->_module->getModel('Cud/Articles')->save($_POST);
     }else{
       $answer = App::module('Core')->getModel('Form')->get_json_error_fields($form);
     }
    }
    echo $answer;
    exit;
  }



  function saveCoordinatesAction(){
    $this->_module->getModel('Cud/Articles')->add_map_coordinates( $this->getRequest()->getParam('cors') );
  }

  function delCoordinatesAction(){
    $this->_module->getModel('Cud/Articles')->del_map_coordinates();
  }

  function linksRelAction(){
    $this->_module->getModel('Cud/Articles')->add_link( $this->getRequest()->getParam('links') );
  }



  function listingStatusAction(){
    $this->_module->getModel('Cud/Articles')->update_field_value( 'status', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
  }

  function listingLanguageAction(){
    $this->_module->getModel('Cud/Articles')->update_field_value( 'language', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
  }

  function listingPromoteAction(){
    $this->_module->getModel('Cud/Articles')->update_field_value( 'promote', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
  }

  function listingMobileAction(){
    $this->_module->getModel('Cud/Articles')->update_field_value( 'mobile', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
  }



  function previewAction(){}
}