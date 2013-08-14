<?php
class Module_User_Repository_Model_Forms_Ministeries extends Core_Model_Repository_Model {

  public function get( $selected=null ) {
    require_once "Local/Form.php";
    $form = new Local_Form;

    $session = App::module('Core')->getModel('Namespace')->get( 'ministeries' );
    $form->setDecorators( array( array('ViewScript', array('viewScript' => 'detail.phtml'))));

    $populate=null;
    if( ! empty($selected) ){
      foreach($selected AS $select){
        $populate[]=$select['seo'];
      }
    }

    $form->addElement('multiselect', 'min_chosen', array(
        'label'         => App::xlat('FORM_tags')
        ,'description'  => App::xlat('FORM_category_content_relate')
        ,'class'        => 'chzn-select'
        ,'multiple'     => true
        ,'required'     => true
        ,'multiOptions' => $session->ministeries['form']
        ,'value'        => $populate
    ));

    $form->setElementDecorators(array('ViewHelper'));
    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');
    return $form;
  }

}