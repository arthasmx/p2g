<?php
class Module_User_Repository_Model_User_Forms_Areas_Admin_Personal extends Core_Model_Repository_Model {

	public function get($post=false) {
	$idioma = App::locale()->getLang();
	require_once "Local/Form.php";
	$form=new Local_Form;
	$form->setAttrib('id','perfil-edit-form');

	if(!$post){
		$cliente=App::module("Acl")->getModel('acl')->data;
	}else{
		$cliente=$post;
		$cliente['username']=$post['username'];
	}

		// Username
		$form->addElement('hidden', 'username', array(
		        'value'=>$cliente['username'],
				'class'=>'hidden'
		));

		// Nombre
			$form->addElement('text', 'name', array(
					'label' => App::xlat('FORM_LABEL_name'),
					'required' => true,
					'validators' => array(
			            	array('stringLength', true, array(3))
			        ),
			        'class'=>'required',
			        'size'=>35,
			));
			$form->getElement('name')->getDecorator('Element')->addClass('to-left rpad-10');

		// Email
			$form->addElement('text', 'email', array(
					'label' => App::xlat("FORM_LABEL_email"),
					'required' => true,
		            'validators' => array(
		                	'EmailAddress',
		                	array('stringLength', true, array(6))
		            ),
		            'class'=>'required',
		            'size'=>35,
			));
			$form->getElement('email')->getDecorator('Element')->addClass('to-left rpad-10');
			
		// Telefono
			$form->addElement('text', 'phone', array(
					'label' => App::xlat('FORM_LABEL_phone'),
					'validators' => array(
			            	array('stringLength', true, array(7)),
			            	//array('regex', true, array('/^([0-9\s\.]+)$/'))
			        ),
			        'size'=>20,
			));
			
		// Extension
			$form->addElement(
				'text',
				'ext',
				array(
					'label' => App::xlat('FORM_LABEL_ext'),
					'validators' => array(
			            	array('stringLength', false, array(1))
			        ),
			        'size'=>5,
			  	)
			);
			$form->getElement('ext')->getDecorator('Element')->addClass('to-left rpad-10');
			
		// Fax
			$form->addElement('text', 'fax', array(
					'label' => App::xlat('FORM_LABEL_fax'),
					'validators' => array(
			            	array('stringLength', true, array(7))
			        )
			));
			$form->getElement('fax')->getDecorator('Element')->addClass('to-left rpad-10');


		// Web
			$form->addElement('text', 'web', array(
					'label' => App::xlat('FORM_LABEL_web'),
					'validators' => array(
			            	array('stringLength', true, array(9))
			        ),
			        'size'=>35,
			));

$form->addDisplayGroup(
    array('name','email','phone','ext','fax','web'),
    'contactinfo',
    array(
		'legend' => App::xlat('PERFIL_fieldset_contact'),
    )
);


		// direccion
			$form->addElement('text', 'adress', array(
					'label' => App::xlat('FORM_LABEL_address'),
					'validators' => array(
			            	array('stringLength', true, array(9))
			        ),
			        'size'=>35,
			));
			$form->getElement('adress')->getDecorator('Element')->addClass('to-left rpad-10');

		// Colonia
			$form->addElement('text', 'section', array(
					'label' => App::xlat('FORM_LABEL_section'),
					'validators' => array(
			            	array('stringLength', true, array(5))
			        ),
			        'size'=>35,
			));
			$form->getElement('section')->getDecorator('Element')->addClass('to-left rpad-10');

		// C. Postal
			$form->addElement('text', 'zip', array(
					'label' => App::xlat('FORM_LABEL_zip'),
					'validators' => array(
			            	array('stringLength', true, array(5))
			        ),
			        'size'=>10,
			));

		// Ciudad
			$form->addElement('text', 'city', array(
					'label' => App::xlat('FORM_LABEL_city'),
					'validators' => array(
			            	array('stringLength', true, array(3))
			        ),
			        'class'=>'required',
			        'size'=>35,
			));
			$form->getElement('city')->getDecorator('Element')->addClass('to-left rpad-10');
			
		// Estado
			$world =App::module('Core')->getResource('World');
			$arrays=App::module('Core')->getResource('Arrays');

		// Paises
			$paises= $world->reset()->countries();
			$paises= $arrays->toAssociative($paises, 'id','country');
			$form->addElement('select', 'country', array(
													'label' => App::xlat('FORM_LABEL_country'),
													'required' => true,
													'class'=>'required',
										            'multiOptions' => $paises,
													'onChange'=>'jsForms.loadSelectList(this.value,"state","'.App::xlat('FORM_LABEL_loading').'","'.App::xlat('FORM_LABEL_loading_error').'")',
										      )
			);
			$form->getElement('country')->getDecorator('Element')->addClass('to-left rpad-10');
			
			if($paises){
				$estados = $world->reset()->setCountry($cliente['country'])->states();
				$estados= $arrays->toAssociative($estados, 'id','state');
			}else{
				$estados = array('0'=>App::xlat('FORM_LABEL_choose_country'));
			}
			$form->addElement('select', 'state', array(
													'label' => App::xlat('FORM_LABEL_state'),
													'required' => true,
													'class'=>'required',
													'multiOptions' => $estados,
										      )
			);

$form->addDisplayGroup(
    array('adress','section','zip','city','country','state'),
    'address',
    array(
		'legend' => App::xlat('PERFIL_fieldset_address'),
    )
);
			
		$form->populate($cliente);
		return $form;
	}


}