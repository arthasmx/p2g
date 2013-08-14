<?php
require_once 'Core/Controller/Block.php';
class Addons_Social_IndexBlockController extends Core_Controller_Block {

  function init() {}

  function eventsAction(){
    $this->view->events = $this->_module->getModel('Social')->events( $this->getParam('business') );

    App::module('Core')->getModel('Libraries')->colorbox('white');
  }

  function hiddenImagesAction(){
    $files              = $this->_module->getModel('Social')->get_event_images( $this->getParam('id'), $this->getParam('created') );
    $this->view->images = $files['images'];
    $this->view->path   = $files['path'];
    $this->view->id     = $this->getParam('id');

    App::header()->add_jquery_events("
      jQuery(document).on('click', 'a.sGal_". $this->getParam('id') ."_btn', function(e){
        e.preventDefault();
        $('a[rel=sGal_". $this->getParam('id') ."]').colorbox({rel:'sGal_". $this->getParam('id') ."', slideshow:true, slideshowSpeed:5000}).eq(0).click();
      });
    ");

  }

}