<?php
require_once 'Core/Controller/Block.php';
class Addons_Facebook_IndexBlockController extends Core_Controller_Block {

  function init() {
    if( App::getEnvironment()!=='devel' ){
      $this->facebook_jssdk();
    }
  }



  function commentsAction(){
    $this->view->article = App::xlat( $this->getParam('document_route') ) .'/'. $this->getParam('seo');
  }

  function likeAndRecommendContentAction(){
    $this->commentsAction();
  }

  function iLikeThisAction(){}



  private function facebook_jssdk(){
    App::header()->addCode("
        (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = '//connect.facebook.net/es_LA/all.js#xfbml=1&appId=".App::getConfig('facebook_app_id')."';
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    ");
  }

}