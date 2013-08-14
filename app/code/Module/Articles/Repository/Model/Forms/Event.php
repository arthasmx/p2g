<?php
class Module_Articles_Repository_Model_Forms_Event extends Core_Model_Repository_Model {

  public function get() {
    require_once "Local/Form.php";
    $form = new Local_Form;
    $form->setAttribs( array( 'autocomplete' => 'off'
                              ,'enctype'     => 'application/x-www-form-urlencoded'
                              ,'id'          => 'add-event') );
    $event_session  = App::module('Core')->getModel('Namespace')->get( 'event' );

    // USE THIS CUSTOM FORM TO RENDER
    $form->setDecorators( array( array('ViewScript', array('viewScript' => 'add.phtml'))));

//******
// INFO

    // TITLE
    $form->addElement('text', 'title', array(
                      'label'         => App::xlat('FORM_title')
                      ,'required'     => true
                      ,'validators'   => array( array('stringLength', true, array(3) ) )
                      ,'onkeyup'      => "jQuery('input[name=seo]').val(string_to_seo(this.value))"
                      ,'value'        => empty($event_session->event['title'])? null : $event_session->event['title']
                      ,'class'        => 'required'
    ));

    // SEO TITLE
    $form->addElement('text', 'seo', array(
                      'label'        => App::xlat("FORM_seo")
                      ,'description' => App::xlat('FORM_seo_description')
                      ,'required'    => true
                      ,'validators'  => array( array('stringLength', true, array(3) ) )
                      ,'value'       => empty($event_session->event['seo'])? null : $event_session->event['seo']
                      ,'class'       => 'required'
    ));

    // DATE TO PUBLISH
    $form->addElement('text', 'publicate_at', array(
                      'label'         => App::xlat('FORM_date_publication')
                      ,'required'     => true
                      ,'value'        => empty($event_session->event['publicate_at'])? date('Y-m-d') : $event_session->event['publicate_at']
                      ,'readonly'     => true
                      ,'class'        => 'required'
    ));

    // EVENT DATE
    $form->addElement('text', 'event_date', array(
                      'label'         => App::xlat('FORM_event_date')
                      ,'value'        => empty($event_session->event['event_date'])? null : $event_session->event['event_date']
                      ,'readonly'     => true
    ));

    // EVENT HOURS
    $hours=array('0'=>null,'01'=>'01','02'=>'02','03'=>'03','04'=>'04','05'=>'05','06'=>'06','07'=>'07','08'=>'08','09'=>'09','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17','18'=>'18','19'=>'19','20'=>'20','21'=>'21','22'=>'22','23'=>'23','24'=>'24');
    $form->addElement('select', 'hours', array(
        'label'         => App::xlat('FORM_hours')
        ,'required'     => true
        ,'multiOptions' => $hours
    ));
    $form->getElement("hours")->setValue( empty($event_session->event['hours'])? null : $event_session->event['hours'] );

    $form->addElement('select', 'minutes', array(
        'label'         => App::xlat('FORM_min')
        ,'required'     => true
        ,'multiOptions' => array('00'=>'00','10'=>'10','20'=>'20','30'=>'30','40'=>'40','50'=>'50') 
    ));
    $form->getElement("minutes")->setValue( empty($event_session->event['minutes'])? null : $event_session->event['minutes'] );

    // STOP PUBLISHING
    $form->addElement('text', 'stop_publication', array(
                      'label'         => App::xlat('FORM_date_stop_publication')
                      ,'value'        => empty($event_session->event['stop_publication'])? null : $event_session->event['stop_publication']
                      ,'readonly'     => true
    ));

    // EVENT PLACE
    $form->addElement('text', 'address', array(
        'label'        => App::xlat("FORM_event_address")
        ,'required'    => true
        ,'validators'  => array( array('stringLength', true, array(3) ) )
        ,'value'       => empty($event_session->event['address'])? null : $event_session->event['address']
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
    $form->getElement("tags")->setValue( empty($event_session->event['tags'])? null : $event_session->event['tags'] )->setAttrib('data-placeholder', App::xlat('TAGS_default_text') );


//*********
// OPTIONS

    // LANGUAGES
    $languages = App::module('Addons')->getModel('Languages')->get_languages_for_select(array('name','prefix'));
    $form->addElement('select', 'language', array(
                      'label'         => App::xlat('FORM_language')
                      ,'value'        => empty($event_session->event['language'])? App::locale()->getLang() : $event_session->event['language']
                      ,'multiOptions' => $languages
                      ,'required'     => true
    ));

    // PROMOTE THIS ARTICLE AT SLIDER CONTENT
    $promote = ( empty($event_session->event['promote']) || $event_session->event['promote']=='enabled' )? '1' : '0';
    $form->addElement('checkbox', 'promote');
    $form->getElement('promote')
         ->setValue($promote)
         ->setUncheckedValue("disabled")
         ->setCheckedValue("enabled")
         ->getDecorator('AddHtml')->append("<label for='promote'>".App::xlat('FORM_promote')."</label>");

    // SHOW ARTICLE IN MOBILE DEVICES
    $mobile = (empty($event_session->event['mobile']) || $event_session->event['mobile']=='enabled' )? '1' : '0';
    $form->addElement('checkbox', 'mobile');
    $form->getElement('mobile')
         ->setValue($mobile)
         ->setUncheckedValue("disabled")
         ->setCheckedValue("enabled")
         ->getDecorator('AddHtml')->append("<label for='mobile'>".App::xlat('FORM_mobile')."</label>");

//*********
// CKEDITOR

    // ARTICLE
    $form->addElement('textarea', 'article', array( 'required' => true, 'value' => empty($event_session->event['article'])? null : $event_session->event['article'] ));


// *******************

    $form->addElement(  'hidden', 'article_id', array(
        'value' => empty($event_session->event['article_id'])? null : $event_session->event['article_id']
    ));

    $form->setElementDecorators(array('ViewHelper'));

    if( ! empty($event_session->event['article_id']) ){
      $form->getElement("article_id")->setAttrib('data-id', $event_session->event['article_id']);
    }
    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');

    return $form;
  }

}