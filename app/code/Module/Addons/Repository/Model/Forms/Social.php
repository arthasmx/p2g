<?php
class Module_Addons_Repository_Model_Forms_Social extends Core_Model_Repository_Model {

  public function get( $action=null ) {
    require_once "Local/Form.php";
    $form = new Local_Form;
    $form->setAttribs( array( 'autocomplete' => 'off'
                              ,'enctype'     => 'application/x-www-form-urlencoded'
                              ,'id'          => 'add-social') );
    $business = App::module('Core')->getModel('Namespace')->get( 'business' );
    $user     = App::module('Core')->getModel('Namespace')->get( 'user' );

    // USE THIS CUSTOM FORM TO RENDER
    $form->setDecorators( array( array('ViewScript', array('viewScript' => 'edit.phtml'))));
    
//******
// INFO

    if( ! empty( $user->user['privileges']['55'] ) || ! empty( $user->user['privileges']['777'] )  ){
      // BUSINESS
      $businessX= App::module('User')->getModel('User')->get_users_by_type_to_select( 'business' );
      $form->addElement('select', 'business', array(
                        'required'     => true
                        ,'multiOptions' => $businessX
      ));
      $form->getElement("business")->setValue( empty($business->business['social']['business'])? null : $business->business['social']['business'] );
    }

    // DESCRIPTION
    $form->addElement('textarea', 'description', array(
                      'label'         => App::xlat('FORM_title')
                      ,'required'     => true
                      ,'value'        => empty($business->business['social']['description'])? null : $business->business['social']['description']
    ));

    // DATE START
    $form->addElement('text', 'event', array(
                      'required'      => true
                      ,'value'        => empty($business->business['social']['event'])? date('Y-m-d') : App::module('Core')->getModel('Dates')->toDate(777, $business->business['social']['event'] )
                      ,'readonly'     => true
                      ,'class'        => 'required'
    ));

    // TAGS
    $tags = App::module('Addons')->getModel('Categories')->get_family_grouped_for_select('etiquetas');
    $form->addElement('multiselect', 'tags', array(
        'label'         => App::xlat('FORM_tags')
        ,'description'  => App::xlat('FORM_category_content_relate')
        ,'class'        => 'chzn-select'
        ,'multiple'     => true
        ,'required'     => true
        ,'multiOptions' => $tags
    ));
    $form->getElement("tags")->setValue( empty($business->business['social']['tags'])? null : $business->business['social']['tags'] )->setAttrib('data-placeholder', App::xlat('TAGS_default_text') );


    $form->addElement(  'hidden', 'action', array(
        'value' => empty($action)? 'save' : $action
    ));

    if( $action==='edit' ){
      $form->addElement(  'hidden', 'social_id', array( 'value' => $business->business['social']['id'] ));
    }

    $form->setElementDecorators(array('ViewHelper'));
    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');
    return $form;
  }

}