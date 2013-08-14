<?php
require_once 'Module/Articles/Controller/Action/Admin.php';

class Articles_EventsController extends Module_Articles_Controller_Action_Admin {

	 function preDispatch(){
	   $this->designManager()->setCurrentLayout('ajax');
	 }

	 function listAction() {
	   App::module("Acl")->getModel('acl')->check_user_section_access();

    $this->designManager()->setCurrentLayout('admin');
    $this->view->current_menu = array('menu'=>9,'sub'=>10);

    App::module('Core')->getModel('Namespace')->clear('event');
    App::module('Core')->getModel('Libraries')->events();
    App::module('Core')->getModel('Libraries')->json2();

    $this->_module->getModel('Event')->event_list_grid();
	 }

	 function listDataLoaderAction(){
	  echo $this->_module->getModel('Event')->jqGrid_admin_list( $this->getRequest()->getParams() );
   exit;
	 }



	 function addAction(){
	   App::module("Acl")->getModel('acl')->check_user_section_access();

	   $this->designManager()->setCurrentLayout('admin');
	   $this->view->current_menu = array('menu'=>9,'sub'=>11);

	   $session = App::module('Core')->getModel('Namespace');
	   $session->clear('event');
	   $session->clear('event_mainpix');
	   $session->clear('event_files');

	   $event_session = $session->get( 'event' );

	   $tab_options   = ( empty($event_session->event['article_id']) ? array('disabled'=>'[1,2]') : array() );
	   $libraries     = App::module('Core')->getModel('Libraries');

	   $libraries->events();
	   $libraries->jquery_ui_tabs("add-eve-tabs", $tab_options );

	   $libraries->jquery_ui_datepicker( array('input#event_date', 'input#publicate_at', 'input#stop_publication') );
	   $libraries->block_ui();

	   $libraries->tags("tags");

	   $libraries->ckeditor('article',array('toolbar'=>'articleCreate', 'height'=>'35em', 'language'=>App::locale()->getLang() ));

	   $libraries->colorbox();
	   
	   $libraries->plUploadQueue();
	   $libraries->plUpload_events_upload_files();

	   $libraries->files_paginator();
	   $libraries->files_paginate_gallery('div.uploaded_images div.f-pagination a', '/articles-files-paginate/event-paginate-gallery/', 'a.cBox-gallery', 'cBox-gallery');

	   $libraries->placeholder();
	   $libraries->clone_elements('#links_ge', @$event_session->event['addons']['links'] );

	   $libraries->google_map_to_pick_coordinates("eventMap",@$event_session->event['addons']['map']);

	   $this->view->form = $this->_module->getModel('Forms/Event')->get();
	   App::header()->add_jquery_events("jQuery('a#mainpix_preview').colorbox({width:'1050',height:'768',iframe:true});");
	 }

	 function editAction(){
	   $session = App::module('Core')->getModel('Namespace');
	   $session->clear('event');
	   $session->clear('event_mainpix');
	   $session->clear('event_files');
	   $session = $session->get('event');

	   $model   = $this->_module->getModel('Event');
	   $article = $model->edit( $this->getRequest()->getParam('id') );
	   $tags    = $this->_module->getModel('Article')->get_tags( $article['article_id'], TRUE );
	   $addons  = $this->_module->getModel('Article')->get_article_addons( $article['article_id'], TRUE );

	   $session->event['folders'] = $model->set_event_folders($article['article_id'], $article['created']);
	   $session->event['addons']  = $addons;

	   $this->_module->getModel('Files')->main_pix_preview();

	   $this->designManager()->setCurrentLayout('admin');
	   $this->view->current_menu = array('menu'=>9);

	   $this->view->addons = $addons;
	   $this->view->form   = $this->_module->getModel('Forms/Event')->get();
	   $this->view->form->getElement('article_id')->setAttrib('data-id', $article['article_id']);
	   $this->view->form->populate( array( 'article_id'     => $article['article_id'],
                                	       'title'          => $article['title'],
                                	       'seo'            => $article['seo'],
	                                       'address'         => $article['address'],
                                	       'event_date'     => ($article['event_date']=='0000-00-00 00:00:00') ? null:$article['event_date'],
	                                       'hours'          => $article['hours'],
	                                       'minutes'        => $article['min'],
                                	       'publicate_at'   => ($article['publicated']=='0000-00-00') ? null:$article['publicated'],
                                	       'stop_publicate' => ($article['stop_publication']=='0000-00-00') ? null:$article['stop_publication'],
                                	       'tags'           => $tags,
                                	       'language'       => $article['language'],
                                	       'mobile'         => $article['mobile'],
                                	       'promote'        => $article['promote'],
                                	       'type'           => 'eventos',
                                	       'article'        => $article['article'] ) );

	   $libraries = App::module('Core')->getModel('Libraries');
	   $libraries->events();

	   $libraries->jquery_ui_tabs("add-eve-tabs" );

	   $libraries->jquery_ui_datepicker( array('input#event_date', 'input#publicate_at', 'input#stop_publication') );
	   $libraries->block_ui();

	   $libraries->tags("tags");

	   $libraries->ckeditor('article',array('toolbar'=>'articleCreate', 'height'=>'35em', 'language'=>App::locale()->getLang() ));

	   $libraries->colorbox();

	   $libraries->plUploadQueue();
	   $libraries->plUpload_events_upload_files();

	   $libraries->files_paginator();
	   $libraries->files_paginate_gallery('div.uploaded_images div.f-pagination a', '/articles-files-paginate/event-paginate-gallery/', 'a.cBox-gallery', 'cBox-gallery');

	   $libraries->placeholder();

	   $cloned = array_merge( (empty($addons['link'])?array():$addons['link']), (empty($addons['video'])?array():$addons['video']) );
	   $libraries->clone_elements('#links_ge', ( is_array($cloned)==true?$cloned:null) );

	   $libraries->google_map_to_pick_coordinates("eventMap",@$addons['map']);

	   App::header()->add_jquery_events("jQuery('a#mainpix_preview').colorbox({width:'1050',height:'768',iframe:true});");
	 }

	 function saveAction(){
	   $request = $this->getRequest();
	   $form   = $this->_module->getModel('Forms/Event')->get();
	   $answer = "{'error':'true'}";

	   if ( $request->isPost() ){
	     if( $form->isValid($_POST) ){
	       $answer = $this->_module->getModel('Cud/Events')->save($_POST);
	     }else{
	       $answer = App::module('Core')->getModel('Form')->get_json_error_fields($form);
	     }
	   }
	   echo $answer;
	   exit;
	 }



	 function saveCoordinatesAction(){
	   $this->_module->getModel('Cud/Events')->add_coordinates( $this->getRequest()->getParam('cors') );
	 }

	 function delCoordinatesAction(){
	   $this->_module->getModel('Cud/Events')->del_coordinates();
	 }

	 function linksRelAction(){
	   $this->_module->getModel('Cud/Events')->add_link( $this->getRequest()->getParam('links') );
	 }



	 function listingStatusAction(){
	   $this->_module->getModel('Cud/Events')->update_field_value( 'status', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
	 }

	 function listingLanguageAction(){
	   $this->_module->getModel('Cud/Events')->update_field_value( 'language', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
	 }

	 function listingPromoteAction(){
	   $this->_module->getModel('Cud/Events')->update_field_value( 'promote', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
	 }

	 function listingMobileAction(){
	   $this->_module->getModel('Cud/Events')->update_field_value( 'mobile', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
	 }

}