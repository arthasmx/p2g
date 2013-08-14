<?php
require_once 'Module/Articles/Controller/Action/Admin.php';

class Articles_FilesController extends Module_Articles_Controller_Action_Admin {

	 function preDispatch(){
	   $this->designManager()->setCurrentLayout('ajax');
	 }

  function mainPixPreviewAction(){
    $this->view->files = $this->_module->getModel('Files')->main_pix_preview();
  }

  function reloadGalleryAction(){
    $this->view->files = $this->_module->getModel('Files')->load_article_gallery();
  }

  function reloadAudioAction(){
    $this->view->files = $this->_module->getModel('Files')->load_article_audio();
  }

  function reloadFilesAction(){
    $this->view->files = $this->_module->getModel('Files')->load_article_files();
  }


  function deleteImageAction(){
    $this->_module->getModel('Files')->delete_image( $this->getRequest()->getParam('image') );
  }

  function deleteAudioAction(){
    $this->_module->getModel('Files')->delete_audio( $this->getRequest()->getParam('audio') );
  }

  function deleteFileAction(){
    $this->_module->getModel('Files')->delete_file( $this->getRequest()->getParam('file') );
  }



  function paginateGalleryAction(){
    $this->view->files = $this->_module->getModel('Files')->load_article_gallery( $this->getRequest()->getParam('page') );
  }



  // events
  function eventMainPixPreviewAction(){
    $this->view->files = $this->_module->getModel('Event/Files')->main_pix_preview();
  }

  function eventReloadGalleryAction(){
    $this->view->files = $this->_module->getModel('Event/Files')->load_gallery();
  }

  function eventDeleteImageAction(){
    $this->_module->getModel('Event/Files')->delete_image( $this->getRequest()->getParam('image') );
  }

  function eventPaginateGalleryAction(){
    $this->view->files = $this->_module->getModel('Event/Files')->load_gallery( $this->getRequest()->getParam('page') );
  }


// Business
  function businessMainPixPreviewAction(){
    $this->view->files = $this->_module->getModel('Business/Files')->main_pix_preview();
  }

  function businessReloadGalleryAction(){
    $this->view->files = $this->_module->getModel('Business/Files')->load_gallery();
  }

  function businessDeleteImageAction(){
    $this->_module->getModel('Business/Files')->delete_image( $this->getRequest()->getParam('image') );
  }

  function businessPaginateGalleryAction(){
    $this->view->files = $this->_module->getModel('Business/Files')->load_gallery( $this->getRequest()->getParam('page') );
  }

}