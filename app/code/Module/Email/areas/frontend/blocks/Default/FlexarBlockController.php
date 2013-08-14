<?php
require_once 'Module/Email/Controller/Block.php';

class Email_Default_FlexarBlockController extends Module_Email_Controller_Block {

  protected $_mandatoryParams = array(
    "to"          =>	"Destinatario",
    "from_email"  =>	"Email cliente",
    "from_name"   =>	"Nombre cliente",
    "msg"         =>	"Mensaje"
  );

  function init() {
    parent::init();
  }

  function htmlAction() {}
  function txtAction() {}
}