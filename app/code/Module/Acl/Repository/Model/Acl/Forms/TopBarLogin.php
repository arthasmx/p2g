<?php
class Module_Acl_Repository_Model_Acl_Forms_TopBarLogin extends Core_Model_Repository_Model {

  public function get() {
    require_once "Local/Form.php";
    $form = new Local_Form;

    // USE THIS CUSTOM FORM TO RENDER
    $form->setDecorators( array( array('ViewScript', array('viewScript' => 'top-bar-login.phtml'))));

    $form->addElement( 'text', 'user', array(
                       'required'     => true,
                       'validators'   => array( array('stringLength', true, array(4))),
                       'class'        => 'field',
                       'placeholder'  => App::xlat('FORM_user')
    ));

    $form->addElement( 'password', 'password', array(
                       'required'     => true,
                       'validators'   => array( array('stringLength', true, array(4))),
                       'class'        => 'field',
                       'placeholder'  => App::xlat('FORM_password')
    ));



    $form->setElementDecorators(array('ViewHelper'));
    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');

    return $form;
  }

}