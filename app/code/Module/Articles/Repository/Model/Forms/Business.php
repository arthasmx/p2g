<?php
class Module_Articles_Repository_Model_Forms_Business extends Core_Model_Repository_Model {

  public function get( $action = 'save' ) {
    require_once "Local/Form.php";
    $form = new Local_Form;
    $form->setAttribs( array( 'autocomplete' => 'off'
                              ,'enctype'     => 'application/x-www-form-urlencoded'
                              ,'id'          => 'add-business') );
    $business_session  = App::module('Core')->getModel('Namespace')->get( 'business' );

    // USE THIS CUSTOM FORM TO RENDER
    $form->setDecorators( array( array('ViewScript', array('viewScript' => 'add.phtml'))));

//******
// ACCESS

    if( $action == 'save' ){
      // USERNAME
      $form->addElement('text', 'username', array(
          'required'      => true
          ,'validators'   => array( array('stringLength', true, array(6) ) )
          ,'value'        => empty($business_session->business['user'])? null : $business_session->business['user']
          ,'onkeyup'      => "jQuery('input[name=user]').val(string_to_seo(this.value))"
          ,'class'        => 'required'
      ));
      // PASSWORD
      $form->addElement('text', 'pass', array(
          'required'      => true
          ,'validators'   => array( array('stringLength', true, array(6) ) )
          ,'value'        => empty($business_session->business['password'])? null : $business_session->business['password']
          ,'onkeyup'      => "jQuery('input[name=pass]').val(string_to_seo(this.value))"
          ,'class'        => 'required'
      ));


    } else {
      // PASSWORD
      $form->addElement('text', 'pass', array(
          'validators'   => array( array('stringLength', true, array(6) ) )
          ,'value'        => empty($business_session->business['password'])? null : $business_session->business['password']
          ,'onkeyup'      => "jQuery('input[name=pass]').val(string_to_seo(this.value))"
      ));
      $form->addElement('text', 'confirmation', array(
          'validators'   => array( array('stringLength', true, array(6) ) )
          ,'value'        => empty($business_session->business['confirmation'])? null : $business_session->business['confirmation']
          ,'onkeyup'      => "jQuery('input[name=confirmation]').val(string_to_seo(this.value))"
      ));
      $form->addElement('hidden', 'username', array('value' => $business_session->business['username'] ));
      
    }

//******
// INFO

    // TITLE
    $form->addElement('text', 'title', array(
                      'label'         => App::xlat('FORM_title')
                      ,'required'     => true
                      ,'validators'   => array( array('stringLength', true, array(3) ) )
                      ,'onkeyup'      => "jQuery('input[name=seo]').val(string_to_seo(this.value))"
                      ,'value'        => empty($business_session->business['title'])? null : $business_session->business['title']
                      ,'class'        => 'required'
    ));

    // SEO TITLE
    $form->addElement('text', 'seo', array(
                      'label'        => App::xlat("FORM_seo")
                      ,'description' => App::xlat('FORM_seo_description')
                      ,'required'    => true
                      ,'validators'  => array( array('stringLength', true, array(3) ) )
                      ,'value'       => empty($business_session->business['seo'])? null : $business_session->business['seo']
                      ,'class'       => 'required'
    ));

    // EMAIL
    $form->addElement('text', 'email', array(
                      'required'     => true
                      ,'validators'  => array( array('stringLength', true, array(7) ) )
                      ,'value'       => empty($business_session->business['email'])? null : $business_session->business['email']
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
    $form->getElement("tags")->setValue( empty($business_session->business['tags'])? null : $business_session->business['tags'] )->setAttrib('data-placeholder', App::xlat('TAGS_default_text') );


//*********
// OPTIONS

    // LANGUAGES
    $languages = App::module('Addons')->getModel('Languages')->get_languages_for_select(array('name','prefix'));
    $form->addElement('select', 'language', array(
                      'label'         => App::xlat('FORM_language')
                      ,'value'        => empty($business_session->business['language'])? App::locale()->getLang() : $business_session->business['language']
                      ,'multiOptions' => $languages
                      ,'required'     => true
    ));

    // PROMOTE THIS ARTICLE AT SLIDER CONTENT
    $promote = ( empty($business_session->business['promote']) || $business_session->business['promote']=='enabled' )? '1' : '0';
    $form->addElement('checkbox', 'promote');
    $form->getElement('promote')
         ->setValue($promote)
         ->setUncheckedValue("disabled")
         ->setCheckedValue("enabled")
         ->getDecorator('AddHtml')->append("<label for='promote'>".App::xlat('FORM_promote')."</label>");

    // SHOW ARTICLE IN MOBILE DEVICES
    $mobile = (empty($business_session->business['mobile']) || $business_session->business['mobile']=='enabled' )? '1' : '0';
    $form->addElement('checkbox', 'mobile');
    $form->getElement('mobile')
         ->setValue($mobile)
         ->setUncheckedValue("disabled")
         ->setCheckedValue("enabled")
         ->getDecorator('AddHtml')->append("<label for='mobile'>".App::xlat('FORM_mobile')."</label>");

//*********
// CKEDITOR

    // ARTICLE
    $form->addElement('textarea', 'article', array( 'required' => true, 'value' => empty($business_session->business['article'])? null : $business_session->business['article'] ));


// *******************

    $form->addElement(  'hidden', 'article_id', array(
        'value' => empty($business_session->business['article_id'])? null : $business_session->business['article_id']
    ));

    $form->setElementDecorators(array('ViewHelper'));

    if( ! empty($business_session->business['article_id']) ){
      $form->getElement("article_id")->setAttrib('data-id', $business_session->business['article_id']);
    }
    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');

    return $form;
  }

}