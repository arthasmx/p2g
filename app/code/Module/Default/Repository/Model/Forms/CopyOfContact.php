<?php
class Module_Default_Repository_Model_Forms_Contact extends Core_Model_Repository_Model {

  public function get($post=false) {
    require_once "Local/Form.php";
    $form = new Local_Form;
    $form->setAttribs( array( 'autocomplete' => 'off',
                              'enctype'      => 'multipart/form-data'
                              ,'id'          => 'form-contact') );

    $form->addElement(  'text', 'name', array(
        'label'       => App::xlat('FORM_name'),
        'required'    => true,
        'validators'  => array( array( 'stringLength', true, array(6))),
        'class'       => 'required'
    ));

    $form->addElement(  'text', 'email', array(
        'label'       => App::xlat('FORM_email'),
        'required'    => true,
        'validators'  => array( 'EmailAddress', array( 'stringLength', true, array(6))),
        'class'       => 'required'
    ));

    $form->addElement( 'text', 'captcha', array(
        'required'   => true,
        'validators' => array( array('stringLength', true, array(5))),
        'size'       => 5,
        'maxlength'  => 5,
        'class'      => 'required captcha'
    ));

    $form->getElement('captcha')->getDecorator('AddHtml')->append(App::xlat('FORM_captcha'));
    $form->getElement('captcha')
         ->getDecorator('AddHtml')
         ->prepend('<img src="'.App::base('/core/captcha/get/'.rand(0,9999)).'" class="captcha"/>' );

    $form->addDisplayGroup(
        array(
            'name','email','captcha'
        ),
        'data'
    );
    $form->getDisplayGroup('data')->getDecorator('Group')->addClass('contact-left-group');

    $form->addElement(  'textarea', 'comment', array(
        'label'       => App::xlat('FORM_comments'),
        'required'    => true,
        'validators'  => array( array( 'stringLength', true, array(10))),
        'class'       => 'required',
        'cols'        => 60,
        'rows'        => 6
    ));

    $form->addElement( 'submit', 'submit', array( 'label' => App::xlat('FORM_button_send_comment') ));

    $form->addDisplayGroup(
        array(
            'comment','submit'
        ),
        'text'
    );
    $form->getDisplayGroup('text')->getDecorator('Group')->addClass('comments-right-group');

    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');
    return $form;
  }
}