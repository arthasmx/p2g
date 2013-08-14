<?php
require_once 'Module/Articles/Controller/Action/Admin.php';

class Articles_UploadsController extends Module_Articles_Controller_Action_Admin {

	 function preDispatch(){
	   $this->designManager()->setCurrentLayout('ajax');
	 }

  function zipGalleryAction(){
    $this->_module->getModel('Uploads')->zip_gallery();
  }

  function imagesToGalleryAction(){
    $this->_module->getModel('Uploads')->image_to_gallery();
  }

  function uploadMainPixAction(){
    $this->_module->getModel('Uploads')->main_pix();
  }

  function uploadAudioAction(){
    $this->_module->getModel('Uploads')->audio();
  }

  function uploadDocsAction(){
    $this->_module->getModel('Uploads')->doc();
  }



  function eventZipGalleryAction(){
    $this->_module->getModel('Event/Uploads')->zip_gallery();
  }

  function eventGalleryAction(){
    $this->_module->getModel('Event/Uploads')->gallery();
  }

  function eventMainPixAction(){
    $this->_module->getModel('Event/Uploads')->main_pix();
  }


  // business
  function businessZipGalleryAction(){
    $this->_module->getModel('Business/Uploads')->zip_gallery();
  }

  function businessGalleryAction(){
    $this->_module->getModel('Business/Uploads')->gallery();
  }

  function businessMainPixAction(){
    $this->_module->getModel('Business/Uploads')->main_pix();
  }

}