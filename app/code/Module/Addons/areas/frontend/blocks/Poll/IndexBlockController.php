<?php
require_once 'Core/Controller/Block.php';
class Addons_Poll_IndexBlockController extends Core_Controller_Block {

  function init() {}

  function getPollAction(){
    $this->view->poll = $this->_module->getModel('Poll')->get_poll( $this->getParam('id') );

    if ( ! empty($this->view->poll) ){
      App::module('Core')->getModel('Libraries')->poll();
    }
  }

}