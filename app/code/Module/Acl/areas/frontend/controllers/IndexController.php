<?php
require_once 'Module/Acl/Controller/Action/Frontend.php';

class Acl_IndexController extends Module_Acl_Controller_Action_Frontend {

  function preDispatch(){
    // $this->designManager()->setCurrentLayout('login');
  }

  function loginAction(){
    $request           = $this->getRequest();
    $form              = $this->_module->getModel('Acl/Forms/Login')->get();
    $this->view->login = true;

    if ( $request->isPost() ){
      if( $form->isValid($_POST) ){
        $this->_module->getModel('Acl')->login( $this->getRequest()->getParam('user'), $this->getRequest()->getParam('password'));
      }
      $form->populate($_POST);
    }
    $this->view->form = $form;

    App::header()->add_jquery_events("
      //jQuery('form#login span#captcha-refresh').click(function(){
      jQuery('button#captcha-refresh').click(function(){
        jQuery.ajax({
          url: wwwUrl  + 'captcha-contact-refresh',
          dataType:'json',
          beforeSend:function(){
            jQuery( '#login input#captcha-input' ).val('');
          },
          success: function(data) {
            jQuery( 'form#login img').attr('src', data.src);
            jQuery( 'form#login input#captcha-id' ).attr('value', data.id);
          }
        });

      });
    ");
  }

  function businessRegisterAction(){
    $request = $this->getRequest();
    $form    = $this->_module->getModel('Acl/Forms/BusinessRegister')->get();
    $this->view->response = null;

    if ( $request->isPost() ){
      if( $form->isValid($_POST) ){
        $this->view->response = $this->_module->getModel('Acl')->business_register( $_POST );
      }
      $form->populate($_POST);
    }
    $this->view->form = $form;

    App::header()->add_jquery_events("
      jQuery('form#business-register span#captcha-refresh').click(function(){
        jQuery.ajax({
          url: baseUrl  + 'captcha-contact-refresh',
          dataType:'json',
          beforeSend:function(){
            jQuery( '#business-register input#captcha-input' ).val('');
          },
          success: function(data) {
            jQuery( 'form#business-register img').attr('src', data.src);
            jQuery( 'form#business-register input#captcha-id' ).attr('value', data.id);
          }
        });

      });");
  }

  protected function get_breadcrumbs( $breadcrumb = null ){
    if( empty($breadcrumb)){
      return null;
    }

    return array( array('title'=> App::xlat($breadcrumb) ) );
  }

}