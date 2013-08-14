<?php
class Module_Addons_Repository_Model_Forms_Section extends Core_Model_Repository_Model {

  public function get($section=null) {
    require_once "Local/Form.php";
    $form = new Local_Form;
    $form->setAttribs( array( 'autocomplete' => 'off'
                              ,'enctype'     => 'application/x-www-form-urlencoded'
                              ,'id'          => 'add-section') );

    // USE THIS CUSTOM FORM TO RENDER
//    $form->setDecorators( array( array('ViewScript', array('viewScript' => 'add.phtml'))));

//*********
// CKEDITOR

    // ARTICLE
    $town_session = App::module('Core')->getModel('Namespace')->get( 'town' );
    $form->addElement('textarea', 'section_desc', array( 'required' => true, 'value' => empty($town_session->town['sections'][$section]['article'])? null : $town_session->town['sections'][$section]['article'] ));

// *******************

    $form->addElement(  'hidden', 'town_section', array(
        'value' => empty($section)? null : $section
    ));
    $form->addElement(  'hidden', 'town_status', array(
        'value' => empty($town_session->town['sections'][$section]['status'])? 'not-available' : $town_session->town['sections'][$section]['status']
    ));


    $form->setElementDecorators(array('ViewHelper'));
    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');

    return $form;
  }

}