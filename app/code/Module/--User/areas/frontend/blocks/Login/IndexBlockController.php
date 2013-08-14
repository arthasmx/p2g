<?php
require_once 'Core/Controller/Block.php';
class User_Login_IndexBlockController extends Core_Controller_Block {

  function init() {}

  /**
  * Efectuamos el login principal del sitio.
  * Esto es un bloque para asi poderlo poner en cualquier parte del diseño
  * OJO: Recuerda que para poder hacer uso del isPOST, tuvimos que enviar una instancia de la clase http_request_object, del controlador donde se carga este bloque
  * de lo contrario, no se encontre una manera de poder validar el formulario
  */
  function loginAction(){
    //$request = $this->getParam('request');
    $form    = null;
    if ( ! empty($_POST) ) {
      $form = App::module('Acl')->getModel('acl')->login($_POST['user'],$_POST['password']);
    }
    $this->view->loginForm = empty($form) ? $this->_module->getModel('User/Forms/Login')->get() : $form; 
/*
        
        $form    = $this->_module->getModel('User/Forms/Login')->get();
        //$form    = App::module('User')->getModel('User/Forms/Login')->get();

        if ($request->isPost()) {
            if ($form->isValid($_POST)) {
                if ( App::module('Acl')->getModel('acl')->login($_POST['user'],$_POST['clave']) ) {
                    App::module('Acl')->getModel('acl')->loginRedirectArea();
                }
            } else {
                //$this->_addError('LOGIN_BLOCK_msg_wrong_credentials',array('user'=>$request->getParam('user'),'pwd'=>$request->getParam('password')));
                App::module('Core')->getModel('Flashmsg')->error( App::xlat('LOGIN_BLOCK_msg_wrong_credentials') );
            }
        $form->populate($_POST);
        }
        $this->view->loginForm = $form;
*/
  }

}