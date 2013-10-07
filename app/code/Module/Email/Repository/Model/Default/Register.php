<?php

require_once "Module/Email/Repository/Model/Email.php";

class Module_Email_Repository_Model_Default_Register extends Module_Email_Repository_Model_Email {

  protected $firma      =  "Solicitud Registro Empresa";  // ¿Quién firma el email?, se usa en la plantilla
  protected $remitente  =  "noreply";              // Remitentes definidos en /Module/Email/etc/core.ini
  protected $asunto     =  "Mensaje recibido desde página de Registro";
  protected $to         =  false;
  protected $from_email =  false;
  protected $from_name  =  false;
  protected $from_tel   =  false;
  protected $msg        =  false;
  protected $business   =  false;
  protected $carboncopy =  false;

  protected function setData() {}

  // Accede al sender del email (Zend_Mail), lo configura, carga el contenido de las plantillas y envía el email.
	public function submit() {
      $this->sender=&$this->getSender();
      $this->setData();

    // Generamos el contenido del email (todos los atributos en $this se asignan a la vista del bloque de la plantilla)
      $this->body=array(
        "html"  =>  $this->_module->renderBlock('default/register'  , 'html'  , $this->asArray() ),
        "txt"   =>  $this->_module->renderBlock('default/register'  , 'txt'   , $this->asArray() ),
      );

    // Generamos el contenido junto con la plantilla general (todos los atributos en $this se asignan a la vista del bloque de la plantilla)
      $this->content = array(
        "html"  =>  $this->_module->renderBlock('template/default'  , 'html'  , $this->asArray() ),
        "txt"	  =>  $this->_module->renderBlock('template/default'  , 'txt'   , $this->asArray() ),
      );

      $this->sender->setBodyHTML ($this->content['html']  , 'UTF-8');
      $this->sender->setFrom     ( $this->to              , $this->firma );
      $this->sender->addTo       ( $this->to              , $this->firma );
      $this->sender->setSubject  ( $this->asunto );

      if (!@$this->debug) {
        $this->send();
      } else {
        $this->debug();
      }
    return true;
  }


}