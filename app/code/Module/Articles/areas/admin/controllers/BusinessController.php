<?php
require_once 'Module/Articles/Controller/Action/Admin.php';
class Articles_BusinessController extends Module_Articles_Controller_Action_Admin {

  private $business_page = null;

	 function preDispatch(){
	   $this->designManager()->setCurrentLayout('ajax');
	 }

	 function listAction() {
	   App::module("Acl")->getModel('acl')->check_user_section_access();


	   $this->designManager()->setCurrentLayout('admin');
	   $this->view->current_menu = array('menu'=>12,'sub'=>13);

	   App::module('Core')->getModel('Namespace')->clear('business');

	   $libraries = App::module('Core')->getModel('Libraries');
	   $libraries->business();
	   $libraries->json2();

	   $this->_module->getModel('Business')->business_list_grid();
	 }

	 function listDataLoaderAction(){
	  echo $this->_module->getModel('Business')->jqGrid_admin_list( $this->getRequest()->getParams() );
   exit;
	 }



	 function addAction(){
	   $this->designManager()->setCurrentLayout('admin');
	   $this->view->current_menu = array('menu'=>12,'sub'=>14);

	   $session = App::module('Core')->getModel('Namespace');
	   $session->clear('business');
	   $session->clear('business_mainpix');
	   $session->clear('business_files');
	   $session = $session->get('business');

	   $tab_options   = ( empty($session->business['article_id']) ? array('disabled'=>'[1,2]') : array() );
	   $libraries     = App::module('Core')->getModel('Libraries');

	   $libraries->business();
	   $libraries->jquery_ui_tabs("add-emp-tabs", $tab_options );

	   $libraries->block_ui();

	   $libraries->tags("tags");

	   $libraries->ckeditor('article',array('toolbar'=>'articleCreate', 'height'=>'46em', 'language'=>App::locale()->getLang() ));

	   $libraries->colorbox();

	   $libraries->plUploadQueue();
	   $libraries->plUpload_business_upload_files();

	   $libraries->files_paginator();
	   $libraries->files_paginate_gallery('div.uploaded_images div.f-pagination a', '/articles-files-paginate/business-paginate-gallery/', 'a.cBox-gallery', 'cBox-gallery');

	   $libraries->placeholder();
	   $libraries->clone_elements('#links_ge', @$session->business['addons']['links'] );

	   $libraries->google_map_to_pick_coordinates("businessMap",@$session->business['addons']['map']);

	   $this->view->form = $this->_module->getModel('Forms/Business')->get();
	   App::header()->add_jquery_events("jQuery('a#mainpix_preview').colorbox({width:'1050',height:'768',iframe:true});");
	 }

	 function editAction(){
	   $this->designManager()->setCurrentLayout('admin');
	   $this->view->current_menu = array('menu'=>12);

	   $session = App::module('Core')->getModel('Namespace');
	   $session->clear('business');
	   $session->clear('business_mainpix');
	   $session->clear('business_files');
	   $session = $session->get('business');

	   $model   = $this->_module->getModel('Business');
	   $article = $model->edit( empty( $this->business_page ) ? $this->getRequest()->getParam('id') : $this->business_page );
	   $tags    = $this->_module->getModel('Article')->get_tags( $article['article_id'], TRUE );
	   $addons  = $this->_module->getModel('Article')->get_article_addons( $article['article_id'], TRUE );

	   $session->business['folders'] = $model->set_business_folders($article['article_id'], $article['created']);
	   $session->business['addons']  = $addons;

	   $this->_module->getModel('Files')->main_pix_preview();

	   $this->view->addons = $addons;
	   $this->view->form   = $this->_module->getModel('Forms/Business')->get( 'update' );
	   $this->view->form->getElement('article_id')->setAttrib('data-id', $article['article_id']);
	   $this->view->form->populate( array( 'article_id'     => $article['article_id'],
                                	       'title'          => $article['title'],
                                	       'seo'            => $article['seo'],
                                	       'event_date'     => ($article['event_date']=='0000-00-00 00:00:00') ? null:$article['event_date'],
                                	       'publicate_at'   => ($article['publicated']=='0000-00-00') ? null:$article['publicated'],
                                	       'stop_publicate' => ($article['stop_publication']=='0000-00-00') ? null:$article['stop_publication'],
                                	       'tags'           => $tags,
                                	       'language'       => $article['language'],
                                	       'mobile'         => $article['mobile'],
                                	       'promote'        => $article['promote'],
                                	       'type'           => 'empresas',
                                	       'article'        => $article['article'] ) );

	   $libraries = App::module('Core')->getModel('Libraries');
	   $libraries->business();

	   $libraries->jquery_ui_tabs("add-emp-tabs" );

	   $libraries->jquery_ui_datepicker( array('input#business_date', 'input#publicate_at', 'input#stop_publicate') );
	   $libraries->block_ui();

	   $libraries->tags("tags");

	   $libraries->ckeditor('article',array('toolbar'=>'articleCreate', 'height'=>'46em', 'language'=>App::locale()->getLang() ));

	   $libraries->colorbox();

	   $libraries->plUploadQueue();
	   $libraries->plUpload_business_upload_files();

	   $libraries->files_paginator();
	   $libraries->files_paginate_gallery('div.uploaded_images div.f-pagination a', '/articles-files-paginate/business-paginate-gallery/', 'a.cBox-gallery', 'cBox-gallery');

	   $libraries->placeholder();

	   $cloned = array_merge( (empty($addons['link'])?array():$addons['link']), (empty($addons['video'])?array():$addons['video']) );
	   $libraries->clone_elements('#links_ge', ( is_array($cloned)==true?$cloned:null) );

	   $libraries->google_map_to_pick_coordinates("businessMap",@$addons['map']);

	   App::header()->add_jquery_events("jQuery('a#mainpix_preview').colorbox({width:'1050',height:'768',iframe:true});");
	 }

	 function saveAction(){
	   $request = $this->getRequest();
	   $form   = $this->_module->getModel('Forms/Business')->get(@$_POST['action']);
	   $answer = "{'error':'true'}";

	   if ( $request->isPost() ){
	     if( $form->isValid($_POST) ){
	       $answer = $this->_module->getModel('Cud/Business')->save($_POST);
	     }else{
	       $answer = App::module('Core')->getModel('Form')->get_json_error_fields($form);
	     }
	   }
	   echo $answer;
	   exit;
	 }

	 function pageAction(){
	   $session = App::module('Core')->getModel('Namespace')->get( 'user' );
	   $this->business_page = $this->_module->getModel('Business')->get_business_page( $session->user['username'] );
	   $this->_helper->getHelper('ViewRenderer')->setScriptAction( "edit" );
	   $this->editAction();
	 }



	 function saveCoordinatesAction(){
	   $this->_module->getModel('Cud/Business')->add_coordinates( $this->getRequest()->getParam('cors') );
	 }

	 function delCoordinatesAction(){
	   $this->_module->getModel('Cud/Business')->del_coordinates();
	 }

	 function linksRelAction(){
	   $this->_module->getModel('Cud/Business')->add_link( $this->getRequest()->getParam('links') );
	 }



	 function listingStatusAction(){
	   $this->_module->getModel('Cud/Business')->update_field_value( 'status', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
	 }

	 function listingLanguageAction(){
	   $this->_module->getModel('Cud/Business')->update_field_value( 'language', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
	 }

	 function listingPromoteAction(){
	   $this->_module->getModel('Cud/Business')->update_field_value( 'promote', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
	 }

	 function listingMobileAction(){
	   $this->_module->getModel('Cud/Business')->update_field_value( 'mobile', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
	 }

}