<?php
class Module_User_Repository_Model_User_Forms_Areas_User_Personal extends Core_Model_Repository_Model {

	public function get($post=false) {
	$idioma = App::locale()->getLang();
	require_once "Local/Form.php";
	$form=new Local_Form;
	$form->setAttrib('id','perfil-edit-form');

	if(!$post){
		$cliente=App::module("Acl")->getModel('acl')->data;
	}else{
		$cliente=$post;
		$cliente['username']=$post['cliente'];
	}

		// Username
		$form->addElement('hidden', 'cliente', array(
		        'value'=>$cliente['username'],
				'class'=>'hidden'
		));

		// Nombre
			$form->addElement('text', 'nombre', array(
					'label' => App::xlat('FORM_LABEL_name'),
					'required' => true,
					'validators' => array(
			            	array('stringLength', true, array(3))
			        ),
			        'class'=>'required',
			        'size'=>35,
			));
//			$form->getElement('nombre')->getDecorator('Element')->addClass('bg');

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
//			$form->getElement('email')->getDecorator('Element')->addClass('bg');

		// Telefono
			$form->addElement('text', 'telefono', array(
					'label' => App::xlat('FORM_LABEL_phone'),
					'validators' => array(
			            	array('stringLength', true, array(7)),
			            	//array('regex', true, array('/^([0-9\s\.]+)$/'))
			        ),
			        'size'=>20,
			));
//			$form->getElement('telefono')->getDecorator('Element')->addClass('float-left');

		// Extension
			$form->addElement(
				'text',
				'extension',
				array(
					'label' => App::xlat('FORM_LABEL_ext'),
					'validators' => array(
			            	array('stringLength', false, array(1))
			        ),
			        'size'=>5,
			  	)
			);
//			$form->getElement('extension')->getDecorator('Element')->addClass('width-30');

		// Fax
			$form->addElement('text', 'fax', array(
					'label' => App::xlat('FORM_LABEL_fax'),
					'validators' => array(
			            	array('stringLength', true, array(7))
			        )
			));
			$form->getElement('fax')->getDecorator('Element')->addClass('clear-left');


		// Web
			$form->addElement('text', 'web', array(
					'label' => App::xlat('FORM_LABEL_web'),
					'validators' => array(
			            	array('stringLength', true, array(9))
			        ),
			        'size'=>35,
			));
			$form->getElement('web')->getDecorator('Element')->addClass('clear-left');
		// direccion
			$form->addElement('text', 'direccion', array(
					'label' => App::xlat('FORM_LABEL_address'),
					'validators' => array(
			            	array('stringLength', true, array(9))
			        ),
			        'size'=>35,
			));
			$form->getElement('direccion')->getDecorator('Element')->addClass('clear-left');

		// Colonia
			$form->addElement('text', 'colonia', array(
					'label' => App::xlat('FORM_LABEL_section'),
					'validators' => array(
			            	array('stringLength', true, array(5))
			        ),
			        'size'=>35,
			));
			$form->getElement('colonia')->getDecorator('Element')->addClass('clear-left');

		// C. Postal
			$form->addElement('text', 'cp', array(
					'label' => App::xlat('FORM_LABEL_zip'),
					'validators' => array(
			            	array('stringLength', true, array(5))
			        ),
			        'size'=>10,
			));
			$form->getElement('cp')->getDecorator('Element')->addClass('clear-left');

		// Ciudad
			$form->addElement('text', 'ciudad', array(
					'label' => App::xlat('FORM_LABEL_city'),
					'required' => true,
					'validators' => array(
			            	array('stringLength', true, array(3))
			        ),
			        'class'=>'required',
			        'size'=>35,
			));
			$form->getElement('ciudad')->getDecorator('Element')->addClass('clear-left');

		// Estado
			$world =App::module('Core')->getResource('World');
			$arrays=App::module('Core')->getResource('Arrays');

		// Paises
			$paises= $world->reset()->countries();
			$paises= $arrays->toAssociative($paises, 'id','nombre_'.$idioma);
			$form->addElement('select', 'pais', array(
													'label' => App::xlat('FORM_LABEL_country'),
													'class'=>'required',
										            'multiOptions' => $paises,
													'onChange'=>'jsForms.loadSelectList(this.value,"estado","'.App::xlat('FORM_LABEL_loading').'","'.App::xlat('FORM_LABEL_loading_error').'")',
										      )
			);
			if($cliente['pais']>0){
				$estados = $world->reset()->setCountry($cliente['pais'])->states();
				$estados= $arrays->toAssociative($estados, 'id','nombre_'.$idioma);
			}else{
				//$estados = $world->reset()->setCountry(1)->states();
				$estados = array('0'=>App::xlat('FORM_LABEL_choose_country'));
			}
			$form->addElement('select', 'estado', array(
													'label' => App::xlat('FORM_LABEL_state'),
													'class'=>'required',
										            'multiOptions' => $estados,
										      )
			);


		$form->addElement(
			'button',
			'botoncillo',
			array(
			'label'=>App::xlat('FORM_LABEL_save'),
			'class'=>'basicButton',
			'onclick' => "account.userinfo('cfg-settings-data','','true');return false;"
			)
		);			


$form->addDisplayGroup(
    array('nombre','email','telefono','extension','fax','web'),
    'contactinfo'
);
$form->getDisplayGroup('contactinfo')->getDecorator('Group')->addClass('no-border float-left pad-10');

$form->addDisplayGroup(
    array('direccion','colonia','cp','ciudad','pais','estado'),
    'address'
);
$form->getDisplayGroup('address')->getDecorator('Group')->addClass('no-border float-left pad-10');

$form->addDisplayGroup(
    array('botoncillo'),
    'elboton'
);

		$form->populate($cliente);
		return $form;
	}
}