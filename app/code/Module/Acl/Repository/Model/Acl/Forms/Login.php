<?php
class Module_Acl_Repository_Model_Acl_Forms_Login extends Core_Model_Repository_Model {

  public function get() {
    require_once "Local/Form.php";
    $form = new Local_Form;
    // USE THIS CUSTOM FORM TO RENDER
    $form->setDecorators( array( array('ViewScript', array('viewScript' => 'login.phtml'))));

    $form->addElement('text', 'user', array(
                      'required'    => true,
                      'validators'  => array( array( 'stringLength', true, array(6))),
                      'class'       => 'form-control'
    ));
    $form->getElement("user")->setAttrib('placeholder', App::xlat('form_placeholder_user') );

    $form->addElement('password', 'password', array(
                      'required'    => true,
                      'validators'  => array( array( 'stringLength', true, array(6))),
                      'class'       => 'form-control'
    ));
    $form->getElement("password")->setAttrib('placeholder', App::xlat('form_placeholder_password') );

    $form->addElement('captcha', 'captcha', array(
        'required'   => true,
        'class'      => 'form-control',
        'maxlength'  => 3,
        'captcha'    => array(
            'captcha' => 'Image',
            'font'    => WP . DS .'skin'. DS.'v1'. DS.'default'. DS.'frontend'. DS.'font'. DS.'comfortaa_bold.ttf',
            'imgDir'  => WP . DS .'media' .DS. 'captchas' .DS,
            'imgUrl'  => App::base("/media/captchas/"),
            'GcFreq'  => '20',
            'wordLen' => 3,
            'dotNoiseLevel'=> 10,
            'Height'=>40,
            'Width'=>150,
            'FontSize'=>20
        )
    ));

    $form->addElement( 'submit', 'button', array( 'label' => App::xlat('BUTTON_login'), 'class' => 'btn btn-flat btn-primary pull-right' ));

    $form->setElementDecorators(array('ViewHelper'));
    $form->getElement('captcha')->removeDecorator("viewhelper");
    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');

    return $form;
  }

}