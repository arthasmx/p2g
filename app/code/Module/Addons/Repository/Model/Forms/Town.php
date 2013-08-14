<?php
class Module_Addons_Repository_Model_Forms_Town extends Core_Model_Repository_Model {

  public function get($action='save') {
    require_once "Local/Form.php";
    $form = new Local_Form;
    $form->setAttribs( array( 'autocomplete' => 'off'
                              ,'enctype'     => 'application/x-www-form-urlencoded'
                              ,'id'          => 'add-town') );
    $town_session  = App::module('Core')->getModel('Namespace')->get( 'town' );

    // USE THIS CUSTOM FORM TO RENDER
    $form->setDecorators( array( array('ViewScript', array('viewScript' => 'add.phtml'))));

    //******
    // ACCESS

    if( $action == 'save' ){
      // USERNAME
      $form->addElement('text', 'username', array(
          'required'      => true
          ,'validators'   => array( array('stringLength', true, array(6) ) )
          ,'value'        => empty($town_session->town['username'])? null : $town_session->town['username']
//          ,'onkeyup'      => "jQuery('input[name=user]').val(string_to_seo(this.value))"
          ,'class'        => 'required'
      ));
      // PASSWORD
      $form->addElement('text', 'pass', array(
          'required'      => true
          ,'validators'   => array( array('stringLength', true, array(6) ) )
          ,'value'        => empty($town_session->town['pass'])? null : $town_session->town['pass']
          ,'onkeyup'      => "jQuery('input[name=pass]').val(string_to_seo(this.value))"
          ,'class'        => 'required'
      ));

    } else {
      // PASSWORD
      $form->addElement('text', 'pass', array(
          'validators'   => array( array('stringLength', true, array(6) ) )
          ,'value'        => empty($town_session->town['pass'])? null : $town_session->town['pass']
          ,'onkeyup'      => "jQuery('input[name=pass]').val(string_to_seo(this.value))"
      ));
      $form->addElement('text', 'confirmation', array(
          'validators'   => array( array('stringLength', true, array(6) ) )
          ,'value'        => empty($town_session->town['confirmation'])? null : $town_session->town['confirmation']
          ,'onkeyup'      => "jQuery('input[name=confirmation]').val(string_to_seo(this.value))"
      ));

      // Username as Email
      $form->addElement('text', 'username', array(
          'required'     => true
          ,'validators'  => array( array('stringLength', true, array(7) ) )
          ,'value'       => empty($town_session->town['username'])? null : $town_session->town['username']
          ,'class'       => 'required'
      ));

    }


//******
// INFO

    // ENABLED CITIES
    $arrays = App::module('Core')->getResource('Arrays');
    $cities = $this->_module->getModel('Cities')->cities();
    $cities = $arrays->toAssociative($cities, 'seo','name');
    $form->addElement('select', 'city', array( 'multiOptions' => $cities ) );

    // NAME
    $form->addElement('text', 'name', array(
                      'label'         => App::xlat('FORM_name')
                      ,'required'     => true
                      ,'validators'   => array( array('stringLength', true, array(3) ) )
                      ,'onkeyup'      => "jQuery('input[name=seo]').val(string_to_seo(this.value))"
                      ,'value'        => empty($town_session->town['name'])? null : $town_session->town['name']
                      ,'class'        => 'required'
    ));

    // SEO
    $form->addElement('text', 'seo', array(
                      'label'        => App::xlat("FORM_seo")
                      ,'description' => App::xlat('FORM_seo_description')
                      ,'required'    => true
                      ,'validators'  => array( array('stringLength', true, array(3) ) )
                      ,'value'       => empty($town_session->town['seo'])? null : $town_session->town['seo']
                      ,'class'       => 'required'
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
    $form->getElement("tags")->setValue( empty($town_session->town['tags'])? null : $town_session->town['tags'] )->setAttrib('data-placeholder', App::xlat('TAGS_default_text') );


//*********
// CKEDITOR

    // ARTICLE
    $form->addElement('textarea', 'article', array( 'required' => true, 'value' => empty($town_session->town['article'])? null : $town_session->town['article'] ));


// *******************

    $form->addElement(  'hidden', 'article_id', array(
        'value' => empty($town_session->town['article_id'])? null : $town_session->town['article_id']
    ));

    $form->setElementDecorators(array('ViewHelper'));

    if( ! empty($town_session->town['article_id']) ){
      $form->getElement("article_id")->setAttrib('data-id', $town_session->town['article_id']);
    }
    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');

    return $form;
  }

}