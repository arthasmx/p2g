<?php
class Module_Core_Repository_Model_Form extends Core_Model_Repository_Model {

  private $error_messages = array();

  function get_error_messages($form=null){
    if( !is_object($form) || empty($form) ){
      return null;
    }

    foreach($form->getMessages() AS $form_field => $form_value){
      $this->get_message( $form_field, $form_value );
    }
    return $this->error_messages;
  }

  function get_message($form_field=null, $field_value=null){
    if( empty($form_field) ){
      return null;
    }

    $msg = '<strong>' . App::xlat('form_element_' . $form_field) . '</strong>';
    if( count($field_value) > 1 ){
      $msg .= App::xlat('invalid_field');
    }else{
      $msg .= current( $field_value );
    }
    $this->error_messages[] = $msg;
  }


  function remove_decorator_from_all_fields($form = null, $decorator = null){
    if( !is_object($form) || empty($form) || empty($decorator) ){
      return null;
    }

    switch ($decorator){
      case 'Errors':
      case 'Label':
        foreach($form->getElements() as $element){
          $element->removeDecorator($decorator);
        }
      break;

      default:
        return null;
      break;
    }
    return true;
  }

  function get_error_fields($form=null){
    if( !is_object($form) || empty($form) ){
      return null;
    }

    $wrong_fields = array();
    foreach($form->getMessages() AS $form_field => $form_value){
      $wrong_fields[] = $form_field;
    }
    return $wrong_fields;
  }

  function get_json_error_fields($form=null){
    if( !is_object($form) || empty($form) ){
      die ("{'status':false}");
    }

    $wrong_fields = array();
    foreach($form->getMessages() AS $form_field => $form_value){
      $wrong_fields[] = array('field'=>$form_field);
    }
    die( json_encode($wrong_fields) );
  }

  /*
   * This method is a code refactor from the above due TOWN jSon response modification
   */
  function get_json_error_form_fields($form=null){
    if( !is_object($form) || empty($form) ){
      die ("{'status':false}");
    }

    $wrong_fields = array();
    foreach($form->getMessages() AS $form_field => $form_value){
      $wrong_fields[] = $form_field;
    }
    die( json_encode( array('field' => $wrong_fields )) );
  }

}