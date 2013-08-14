<?php
class Module_Default_Repository_Model_Forms_Flexar extends Core_Model_Repository_Model {

  public function get() {
    require_once "Local/Form.php";
    $form = new Local_Form;
    $form->setAttribs( array( 'autocomplete' => 'off',
                              'enctype'      => 'multipart/form-data'
                              ,'id'          => 'flexar') );

    $form->addElement(  'text', 'name', array(
                        'label'       => App::xlat('FORM_name'),
                        'required'    => true,
                        'validators'  => array( array( 'stringLength', true, array(6))),
                        'class'       => 'required',
                        'size'        => 30
     ));

    $form->addElement(  'text', 'email', array(
                        'label'       => App::xlat('FORM_email'),
                        'required'    => true,
                        'validators'  => array( 'EmailAddress', array( 'stringLength', true, array(6))),
                        'class'       => 'required',
                        'size'        => 30
     ));

    $form->addDisplayGroup(
        array(
            'name','email'
        ),
        'namemail'
    );

    $form->addElement(  'textarea', 'comment', array(
                        'label'       => App::xlat('FORM_comments'),
                        'required'    => true,
                        'validators'  => array( array( 'stringLength', true, array(10))),
                        'class'       => 'required',
                        'cols'        => 70,
                        'rows'        => 8
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

     $form->addElement( 'submit', 'submit', array( 'label' => App::xlat('FORM_button_send_comment') ));

     $form->addDisplayGroup(
         array(
             'captcha','submit'
         ),
         'casu'
     );

    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');
    return $form;
  }
}