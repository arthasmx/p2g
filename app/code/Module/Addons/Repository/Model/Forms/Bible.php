<?php
class Module_Addons_Repository_Model_Forms_Bible extends Core_Model_Repository_Model {

  public function get($type = "simple", $reset = null) {
    require_once "Local/Form.php";
    $form = new Local_Form;
    $form->setAttribs( array( 'autocomplete' => 'off',
                              'enctype'      => 'application/x-www-form-urlencoded'
                              ,'id'          => 'bible-search'
                              ,'action'      => App::base( App::xlat('route_bible') ) . App::xlat('route_search')
        ));
    $session = App::module('Core')->getModel('Namespace')->get( 'search' );
    if( empty($reset) ){
      unset($session->search);
    }

    $form->addElement(  'text', 'search', array(
                         'class' => 'intro'
                        ,'value' => empty($session->search['keyword']) ? false : $session->search['keyword']
     ));
    $form->getElement("search")->setDecorators(array('ViewHelper'));

    if( $type === "advanced" ){
      $f_testament_selected =   empty( $session->search['filters'][App::xlat('LINK_bible')]['f_testament'] ) ?
                                  "null" 
                                :
                                  $session->search['filters'][App::xlat('LINK_bible')]['f_testament'];

      $form->addElement('radio', 'f_testament', array(
          'multiOptions' => array( "old" => App::xlat("BIBLE_testament_old")
                                 , "new" => App::xlat("BIBLE_testament_new")
                                 , "null"   => App::xlat("BIBLE_testament_both") )
          ,'value'   => $f_testament_selected
          ,'onClick' => "bible.load_books(this.value);"
      ));
      $form->getElement("f_testament")->setDecorators(array('ViewHelper'));

      $options = ($f_testament_selected=="null") ?
                   array("null" => App::xlat('AJAX_choose_an_option'))
                 :
                   $this->_module->getModel("Bible")->get_books_for_dropbox( $f_testament_selected );

      $f_seo_selected = empty($session->search['filters'][App::xlat('LINK_bible')]['f_seo']) ? "old" : $session->search['filters'][App::xlat('LINK_bible')]['f_seo'];
      $form->addElement('select', 'f_seo', array(
          'label'         => App::xlat('BIBLE_book_selector')
          ,'multiOptions' => $options
          ,'value'        => $f_seo_selected
      ));
      if( $f_testament_selected == "null" ){
        $form->getElement('f_seo')->getDecorator('Element')->addClass('hide');
      }

      $form->addElement( 'button', 'button', array( 'label' => App::xlat('FORM_search') ));
      $form->addElement( 'button', 'close', array( 'label' => App::xlat('FORM_close') ));
      $form->addDisplayGroup( array('button','close'), 'buttons' );

      $form->getElement("button")->setDecorators(array('ViewHelper'));
      $form->getElement("close")->setDecorators(array('ViewHelper'));
    }else{
      $form->addElement( 'submit', 'button', array( 'label' => App::xlat('FORM_search') ));
      $form->getElement("button")->setDecorators(array('ViewHelper'));
    }

    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');
    return $form;
  }

}