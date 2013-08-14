<?php
class Module_Articles_Repository_Model_Forms_ArticleContent extends Core_Model_Repository_Model {

  public function get() {
    require_once "Local/Form.php";
    $form = new Local_Form;
    $form->setAttribs( array( 'autocomplete' => 'off'
                              ,'enctype'     => 'application/x-www-form-urlencoded'
                              ,'id'          => 'article-content') );

    /*
    // CUSTOM UPLOAD IMAGE
    $form->addElement('hidden', 'image', array('label' => App::xlat('FORM_image_upload')) );
    $form->getElement('image')->getDecorator('AddHtml')->append('<section id="image-upload">  </section>');
    $form->addDisplayGroup( array('image'), 'image-upload', array('legend' => App::xlat('FORM_FIELDSET_image')) );
    */

    // ARTICLE
    $form->addElement('textarea', 'article', array(
                      'cols'        => 80,
                      'rows'        => 5
    ));
    $form->addDisplayGroup( array('article'), 'arti', array('legend' => App::xlat('FORM_FIELDSET_article')) );
    return $form;
  }

}