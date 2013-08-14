<?php
class Module_Default_Repository_Model_Forms_Mapa extends Core_Model_Repository_Model {

    public function get($idioma) {

    require_once "Local/Form.php";
    $form=new Local_Form;

    $form->addElement('button','botoncillo',array(
													'label' => App::xlat('Mapa Satelital'),
													'onClick' => 'gestorMapa.cargar("'.$idioma.'");return false;',
													'class' => 'big-button'
                                                ));
		return $form;
    }
}