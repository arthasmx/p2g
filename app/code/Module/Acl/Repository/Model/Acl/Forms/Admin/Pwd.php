<?php

class Module_Acl_Repository_Model_Acl_Forms_Admin_Pwd extends Core_Model_Repository_Model {


	public function get(array $user=array()) {

		require_once "Local/Form.php";
		$form=new Local_Form;

				$form->addElement(
					'text',
					'username',
					array(
						'label' => App::xlat('Usuario'),
						'description' => App::xlat('Usuario del que va a modificarse la contraseña.'),
				        'size'=>25,
				        'readonly'=>true,
				        'disabled'=>true,
				        'value'=>$user['username']
				  	)
				);


				$form->addElement(
					'password',
					'passwd',
					array(
						'label' => App::xlat('Nueva contraseña'),
						'description' => App::xlat('4 caracteres mínimo. Distingue mayúsculas de minúsculas.'),
						'required' => true,
						'validators' => array(
				            	array('stringLength', true, array(4))
				        ),
				        'size'=>12,
				  	)
				);

				$form->addElement(
					'password',
					'passwdconfirm',
					array(
				        'size'=>12,
				        'required' => true,
				  	)

				);

				$form->getElement('passwd')->getDecorator('Element')->addClass('bg');
				//$form->getElement('passwd')->getDecorator('AddHtml')->append(App::xlat('Confirmar'));

				$form->getElement('passwdconfirm')->getDecorator('AddHtml')->prepend(App::xlat('Confirmar:'));
				$form->getElement('passwd')->getDecorator('MergeElement')->append('passwdconfirm');



		// Cambiar tipo
			$url=App::module('Core')->getResourceSingleton('Goback')->get() ?
					App::module('Core')->getResourceSingleton('Goback')->get()
					:
					App::base("/account/admin")
			;
			$form->addElement('button','back',array(
														'label' => "<< ".App::xlat("Regresar"),
														'onclick' => 'document.location.href="'.$url.'"',
			 										)
			);
			$form->getElement('back')->getDecorator('Element')->addClass('clear-left float-left back');

		// Submit
			$form->addElement('submit','submit',array( 'label' => App::xlat(App::xlat('Modificar contraseña'))." >>" ));
			//$form->getElement('submit')->getDecorator('Element')->addClass('clear-left');


		return $form;
	}


}
