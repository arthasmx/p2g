<?php
class Module_Addons_Repository_Model_Forms_Promotions extends Core_Model_Repository_Model {

  public function get( $action=null ) {
    require_once "Local/Form.php";
    $form = new Local_Form;
    $form->setAttribs( array( 'autocomplete' => 'off'
                              ,'enctype'     => 'application/x-www-form-urlencoded'
                              ,'id'          => 'add-promotion') );
    $business = App::module('Core')->getModel('Namespace')->get( 'business' );

    // USE THIS CUSTOM FORM TO RENDER
    $form->setDecorators( array( array('ViewScript', array('viewScript' => 'add.phtml'))));

//******
// INFO

    // DESCRIPTION
    $form->addElement('textarea', 'description', array(
                      'label'         => App::xlat('FORM_title')
                      ,'value'        => empty($business->business['promotions']['description'])? null : $business->business['promotions']['description']
    ));

    // ONCLICK BEHAVIOR
    $form->addElement('select', 'onclick_action', array(
                      'required'      => true,
                      'multiOptions'  => array('expand'=>App::xlat('promo_expand'),'link'=>App::xlat('promo_link')),
                      'onChange'      => 'promotions.linker(this.value)'
    ));
    $form->getElement("onclick_action")->setValue( empty($business->business['promotions']['onclick_action'])? null : $business->business['promotions']['onclick_action'] );

    $form->addElement('text', 'onclick_url', array(
      'value' => empty($business->business['promotions']['onclick_url'])? null : $business->business['promotions']['onclick_url']
    ));

    // DATE START
    $form->addElement('text', 'start', array(
                      'required'      => true
                      ,'value'        => empty($business->business['promotions']['start'])? date('Y-m-d') : $business->business['promotions']['start']
                      ,'readonly'     => true
                      ,'class'        => 'required'
    ));

    // DATE START
    $form->addElement('text', 'finish', array(
                      'required'      => true
                      ,'value'        => empty($business->business['promotions']['finish'])? date("Y-m-d",strtotime("+10 day")) : $business->business['promotions']['finish']
                      ,'readonly'     => true
                      ,'class'        => 'required'
    ));

    // MAKE IT THE MAIN PROMOTION
    $main = ( empty( $business->business['promotions']['main'] ) || $business->business['promotions']['main']=='yes' )? '1' : '0';
    $form->addElement('checkbox', 'main');
    $form->getElement('main')
         ->setValue($main)
         ->setUncheckedValue("no")
         ->setCheckedValue("yes")
         ->getDecorator('AddHtml')->append("<label for='main'>".App::xlat('FORM_main')."</label>");

    $form->addElement(  'hidden', 'action', array(
        'value' => empty($action)? 'add' : $action
    ));

    $form->setElementDecorators(array('ViewHelper'));
    App::module('Core')->getModel('Form')->remove_decorator_from_all_fields($form,'Errors');
    return $form;
  }

}