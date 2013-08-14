<?php
class Module_Addons_Repository_Model_Forms_Category extends Core_Model_Repository_Model {

  public function get() {
    require_once "Local/Form.php";
    $form = new Local_Form;
    $form->setAttribs( array( 'autocomplete' => 'off'
                              ,'enctype'     => 'application/x-www-form-urlencoded'
                              ,'id'          => 'category') );

    // TITLE
    $form->addElement('text', 'name', array(
                      'label'         => ucfirst(App::xlat('name'))
                      ,'required'     => true
                      ,'validators'   => array( array('stringLength', true, array(3) ) )
                      ,'onkeyup'      => "jQuery('input[name=seo]').val(string_to_seo(this.value))"
                      ,'class'        => 'required'
                      ,'size'         => 55
    ));

    // SEO TITLE
    $form->addElement('text', 'seo', array(
                      'label'        => App::xlat("FORM_seo_category")
                      ,'description' => App::xlat('FORM_seo_description')
                      ,'required'    => true
                      ,'validators'  => array( array('stringLength', true, array(3) ) )
                      ,'class'       => 'required'
                      ,'size'        => 55
    ));

    // LANGUAGES
    $languages = App::module('Addons')->getModel('Languages')->get_languages_for_select(array('name','prefix'));
    $form->addElement('select', 'language', array(
        'label'          => ucfirst(App::xlat('FORM_language'))
        ,'multiOptions'  => $languages
        ,'size'          => 2
        ,'value'         => App::locale()->getLang()
    ));

    // STATUS
    $form->addElement('select', 'status', array(
        'label'          => ucfirst(App::xlat('visible'))
        ,'multiOptions'  => array('enabled'=>App::xlat('enabled'), 'disabled'=>App::xlat('disabled'))
        ,'size'          => 2
        ,'value'         => 'enabled'        
    ));

    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');
    return $form;
  }

}