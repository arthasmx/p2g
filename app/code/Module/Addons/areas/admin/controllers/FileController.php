<?php
require_once 'Module/Addons/Controller/Action/Admin.php';
class Addons_GalleryController extends Module_Addons_Controller_Action_Admin {

  function preDispatch(){
    $this->designManager()->setCurrentLayout('ajax');
  }

  function imageUploadAction(){
/*
 * Array
(
    [action] => image-upload
    [module] => Addons
    [controller] => Uploader
    [controller_prefix] =>
    [id] => 2012
    [qqfile] => 10_29_2012_19_24_16.jpg
)
 */
    echo $this->_module->getModel('Filesystem')->image_upload( $this->getRequest()->getParam('qqfile') );
    exit;
  }

}