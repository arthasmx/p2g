<?php
class Module_Articles_Repository_Model_Forms_Article extends Core_Model_Repository_Model {

  public function get() {
    require_once "Local/Form.php";
    $form = new Local_Form;
    $form->setAttribs( array( 'autocomplete' => 'off'
                              ,'enctype'     => 'application/x-www-form-urlencoded'
                              ,'id'          => 'add-article') );
    $article_session  = App::module('Core')->getModel('Namespace')->get( 'article' );

    // USE THIS CUSTOM FORM TO RENDER
    $form->setDecorators( array( array('ViewScript', array('viewScript' => 'form-article.phtml'))));

//******
// INFO

    // TITLE
    $form->addElement('text', 'title', array(
                      'label'         => App::xlat('FORM_title')
                      ,'required'     => true
                      ,'validators'   => array( array('stringLength', true, array(3) ) )
                      ,'onkeyup'      => "jQuery('input[name=seo]').val(string_to_seo(this.value))"
                      ,'value'        => empty($article_session->article['title'])? null : $article_session->article['title']
                      ,'class'        => 'required'
    ));

    // SEO TITLE
    $form->addElement('text', 'seo', array(
                      'label'        => App::xlat("FORM_seo")
                      ,'description' => App::xlat('FORM_seo_description')
                      ,'required'    => true
                      ,'validators'  => array( array('stringLength', true, array(3) ) )
                      ,'value'       => empty($article_session->article['seo'])? null : $article_session->article['seo']
                      ,'class'       => 'required'
    ));

    // DATE TO PUBLISH
    $form->addElement('text', 'publicate_at', array(
                      'label'         => App::xlat('FORM_date_publication')
                      ,'required'     => true
                      ,'value'        => empty($article_session->article['publicate_at'])? date('Y-m-d') : $article_session->article['publicate_at']
                      ,'readonly'     => true
                      ,'class'        => 'required'
    ));

    // EVENT DATE
    $form->addElement('text', 'event_date', array(
                      'label'         => App::xlat('FORM_event_date')
                      ,'value'        => empty($article_session->article['event_date'])? null : $article_session->article['event_date']
                      ,'readonly'     => true
    ));

    // STOP PUBLISHING
    $form->addElement('text', 'stop_publicate', array(
                      'label'         => App::xlat('FORM_date_stop_publication')
                      ,'value'        => empty($article_session->article['stop_publicate'])? null : $article_session->article['stop_publicate']
                      ,'readonly'     => true
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
    $form->getElement("tags")->setValue( empty($article_session->article['tags'])? null : $article_session->article['tags'] )->setAttrib('data-placeholder', App::xlat('TAGS_default_text') );


//*********
// OPTIONS

    // LANGUAGES
    $languages = App::module('Addons')->getModel('Languages')->get_languages_for_select(array('name','prefix'));
    $form->addElement('select', 'language', array(
                      'label'         => App::xlat('FORM_language')
                      ,'value'        => empty($article_session->article['language'])? App::locale()->getLang() : $article_session->article['language']
                      ,'multiOptions' => $languages
                      ,'size'         => 2
                      ,'required'     => true
    ));

    // PROMOTE THIS ARTICLE AT SLIDER CONTENT
    $promote = ( empty($article_session->article['promote']) || $article_session->article['promote']=='enabled' )? '1' : '0';
    $form->addElement('checkbox', 'promote');
    $form->getElement('promote')
         ->setValue($promote)
         ->setUncheckedValue("disabled")
         ->setCheckedValue("enabled")
         ->getDecorator('AddHtml')->append("<label for='promote'>".App::xlat('FORM_promote')."</label>");

    // SHOW ARTICLE IN MOBILE DEVICES
    $mobile = (empty($article_session->article['mobile']) || $article_session->article['mobile']=='enabled' )? '1' : '0';
    $form->addElement('checkbox', 'mobile');
    $form->getElement('mobile')
         ->setValue($mobile)
         ->setUncheckedValue("disabled")
         ->setCheckedValue("enabled")
         ->getDecorator('AddHtml')->append("<label for='mobile'>".App::xlat('FORM_mobile')."</label>");

//*********
// CKEDITOR

    // ARTICLE
    $form->addElement('textarea', 'article', array( 'required' => true, 'value' => empty($article_session->article['article'])? null : $article_session->article['article'] ));


// *******************

    $form->addElement(  'hidden', 'article_id', array(
        'value' => empty($article_session->article['article_id'])? null : $article_session->article['article_id']
    ));

    $form->setElementDecorators(array('ViewHelper'));

    if( ! empty($article_session->article['article_id']) ){
      $form->getElement("article_id")->setAttrib('data-id', $article_session->article['article_id']);
    }
    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');

    return $form;
  }

}