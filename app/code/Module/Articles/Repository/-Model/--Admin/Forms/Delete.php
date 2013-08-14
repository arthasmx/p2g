<?php

class Module_Blog_Repository_Model_Admin_Forms_Delete extends Core_Model_Repository_Model {

	public function get($id=false) {
        if(!$id) return false;

		require_once "Local/Form.php";
		$form=new Local_Form;
		$form->setAttribs(array('autocomplete'=>'off'));
		// Cambiar tipo
			$url=App::base("/articulos/blog/index");
			$form->addElement('button','back',array(
														'label' => "<< ".App::xlat("Cancelar"),
														'onclick' => 'document.location.href="'.$url.'"',
			 										)
			);
			$form->getElement('back')->getDecorator('Element')->addClass('float-left back');


		// Submit
			$form->addElement('submit','submit',array( 'label' => App::xlat(App::xlat('Eliminar registro'))." >>" ));

			$form->addDisplayGroup(
				array(
					'back','submit',
				),
				'buttons'
			);
			$form->getDisplayGroup('buttons')->getDecorator('Group')->addClass('clear-left');

			$form->addElement(
				'hidden',
				'id',array('value'=>$id)
                
			);
		//$form->populate($blog);
		return $form;
	}

}