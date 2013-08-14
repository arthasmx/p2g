<?php
require_once 'Core/Controller/Block.php';
class Addons_Video_IndexBlockController extends Core_Controller_Block {

  /*
   * Streaming
   */
  function liveAction(){
    $this->view->title    = $this->getParam('title');
    $this->view->subtitle = $this->getParam('subtitle');
  }

  /*
   * Youtube
   */
  function showVideoAction(){
    $this->view->video    = $this->_module->getModel('Video')->get_video( $this->getParam('id') );
    $this->view->embed    = $this->getParam('embed');
    
    $this->view->title    = $this->getParam('title');
    $this->view->subtitle = $this->getParam('subtitle');
    $this->view->img      = $this->getParam('img');
  }

  function showUserVideosAction(){
    $this->view->videos = $this->_module->getModel('Video')->get_user_videos( $this->getParam('user') );
  }

}