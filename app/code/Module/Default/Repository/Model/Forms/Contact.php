<?php
class Module_Default_Repository_Model_Forms_Contact extends Core_Model_Repository_Model {

  public function get($post=false) {
    require_once "Local/Form.php";
    $form = new Local_Form;

    // USE THIS CUSTOM FORM TO RENDER
    $form->setDecorators( array( array('ViewScript', array('viewScript' => 'contact-us.phtml'))));    

    $form->addElement(  'text', 'name', array(
        'required'    => true,
        'validators'  => array( array( 'stringLength', true, array(6))),
//'value' => 'el nombre aqui',
        'class'       => 'input-xlarge'
    ));
    $form->getElement("name")->setAttrib('placeholder', App::xlat('form_placeholder_name') );

    $form->addElement(  'text', 'email', array(
        'required'    => true,
        'validators'  => array( 'EmailAddress', array( 'stringLength', true, array(6))),
//'value' => 'mi_email@gmail.com',
        'class'       => 'input-xlarge'
    ));
    $form->getElement("email")->setAttrib('placeholder', App::xlat('form_placeholder_email') );

    $form->addElement(  'textarea', 'comments', array(
        'required'    => true,
        'validators'  => array( array( 'stringLength', true, array(10))),
        'class'       => 'span12',
//'value' => 'Este el contenido del mensaje que llegara al correo',
        'rows'        => 8
    ));
    $form->getElement("comments")->setAttrib('placeholder', App::xlat('form_placeholder_msg') );

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
            'dotNoiseLevel'=> 10
        )
    ));

    $form->addElement( 'button', 'button', array( 'label' => App::xlat('BUTTON_send_message'), 'class' => 'btn btn-flat btn-primary pull-right' ));

    $form->setElementDecorators(array('ViewHelper'));
    $form->getElement('captcha')->removeDecorator("viewhelper");

    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');
    return $form;
  }
}