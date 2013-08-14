<?php
class Module_Acl_Repository_Model_Acl_Forms_BusinessRegister extends Core_Model_Repository_Model {

  public function get() {
    require_once "Local/Form.php";
    $form = new Local_Form;

    $form->setDecorators( array( array('ViewScript', array('viewScript' => 'business-register.phtml'))));

    $form->addElement('text', 'user', array(
                      'required'    => true,
                      'validators'  => array( array( 'stringLength', true, array(6))),
                      'class'       => 'input-xlarge'
    ));
    $form->getElement("user")->setAttrib('placeholder', App::xlat('form_placeholder_email') );

    $form->addElement('password', 'password', array(
                      'required'    => true,
                      'validators'  => array( array( 'stringLength', true, array(6))),
                      'class'       => 'input-xlarge'
    ));
    $form->getElement("password")->setAttrib('placeholder', App::xlat('form_placeholder_password') );



    $form->addElement('text', 'name', array(
        'required'    => true,
        'validators'  => array( array( 'stringLength', true, array(6))),
        'class'       => 'input-xlarge'
    ));
    $form->getElement("name")->setAttrib('placeholder', App::xlat('form_placeholder_business_name') );

    $form->addElement('text', 'phone', array(
        'required'    => true,
        'validators'  => array( array( 'stringLength', true, array(6))),
        'class'       => 'input-xlarge'
    ));
    $form->getElement("phone")->setAttrib('placeholder', App::xlat('form_placeholder_phone') );

    $form->addElement('textarea', 'address', array( 'required' => true ));
    $form->getElement("address")->setAttrib('placeholder', App::xlat('form_placeholder_address') );



    $form->addElement('captcha', 'captcha', array(
        'required'   => true,
        'class'      => 'span1',
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

    $form->addElement( 'submit', 'button', array( 'label' => App::xlat('BUTTON_register'), 'class' => 'btn btn-flat btn-primary pull-right' ));
    $form->addElement('hidden', 'city', array( 'value'=> 'mazatlan' ));

    $form->setElementDecorators(array('ViewHelper'));
    $form->getElement('captcha')->removeDecorator("viewhelper");
    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');

    return $form;
  }

}