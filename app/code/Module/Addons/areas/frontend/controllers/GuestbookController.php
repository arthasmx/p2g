<?php
require_once 'Module/Addons/Controller/Action/Frontend.php';
class Addons_GuestbookController extends Module_Addons_Controller_Action_Frontend   {

  function preDispatch(){}

  function showAction(){
    $this->view->guestbook = $this->_module->getModel('Guestbook')->get_signs( $this->getRequest()->getParam( App::xlat('route_paginator_page') ) );
    $this->view->form      = $this->_module->getModel('Forms/Guestbook')->get();

    App::header()->addScript(App::url()->get('/guestbook.js','js'));
    App::header()->addCode("
          var guestbook_add_url = '". App::url_translate('LINK_guestbook') ."/add';
          var guestbook_error   = '". App::xlat('EXC_missing_arguments_at_adding_comments') ."';
        ");

    $this->view->pageBreadcrumbs = array( array('title'=> App::xlat('BREADCRUM_guestbook' ) ) );
  }

  function addAction(){
    $this->designManager()->setCurrentLayout('ajax');

    $request = $this->getRequest();
    $form    = $this->_module->getModel('Forms/Guestbook')->get();

    if ( $request->isPost() ){

      require_once('Xplora/Captcha.php');
      $captcha = new Xplora_Captcha();
      if ( ! $captcha->validate(@$_POST['captcha']) ) {
        $form->getElement('captcha')->getValidator('Custom')->addError("captchaWrongCode",App::xlat("ERROR_bad_captcha"));
      }

      if( $form->isValid($_POST) ) {
        $this->view->sign = $this->_module->getModel('Cud/Guestbook')->sign( $request->getParam('name'), $request->getParam('email'), $request->getParam('gender'),$request->getParam('comment') );
      }else{
        $form->populate($_POST);
      }
    }

    $this->view->form = $form;
  }

}