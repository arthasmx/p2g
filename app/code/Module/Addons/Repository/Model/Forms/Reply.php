<?php
class Module_Addons_Repository_Model_Forms_Reply extends Core_Model_Repository_Model {

  public function get($parent=null, $child=null, $type=null, $reference=null) {
    require_once "Local/Form.php";
    $form = new Local_Form;
    $form->setAttribs( array( 'autocomplete' => 'off',
                              'enctype'      => 'application/x-www-form-urlencoded'
                              ,'id'          => 'reply'
                              // ,'class'       => 'corner-all' 
    ));

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
     $form->getDisplayGroup('data')->getDecorator('Group')->addClass('comments-left-group');


     $form->addElement(  'textarea', 'comment', array(
         'label'       => App::xlat('FORM_comments'),
         'required'    => true,
         'validators'  => array( array( 'stringLength', true, array(10))),
         'class'       => 'required',
         'cols'        => 60,
         'rows'        => 6
     ));

     $form->addElement( 'button', 'button', array( 'label' => App::xlat('FORM_button_send_comment') ));
     $form->addElement( 'button', 'cancel', array( 'label' => App::xlat('FORM_button_cancel') ));

     $form->addDisplayGroup(
         array(
             'comment','button','cancel'
         ),
         'text'
     );
     $form->getDisplayGroup('text')->getDecorator('Group')->addClass('comments-right-group');

    $form->addElement(  'hidden', 'type'  , array('value' => (empty($type) ? 'events':$type)  ));
    $form->addElement(  'hidden', 'parent', array('value' => $parent ));
    $form->addElement(  'hidden', 'child' , array('value' => $child ));
    $form->addElement(  'hidden', 'reference', array('value' => (empty($reference) ? null:$reference)  ));
 
    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');
    return $form;
  }

}