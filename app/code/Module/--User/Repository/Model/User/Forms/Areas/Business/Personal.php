<?php
class Module_User_Repository_Model_User_Forms_Areas_Business_Personal extends Core_Model_Repository_Model {

	public function get($post=false) {
	$idioma = App::locale()->getLang();
	require_once "Local/Form.php";
	$form=new Local_Form;
	$form->setAttrib('id','perfil-edit-form');
	$arrays=App::module('Core')->getResource('Arrays');

	if(!$post){
		$cliente=App::module("Acl")->getModel('acl')->data;
	}else{
		$cliente=$post;
		$cliente['username']=$post['cliente'];
	}

	
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°
//	Bloque izquierdo
	
		// Username
		$form->addElement('hidden', 'cliente', array(
		        'value'=>$cliente['username'],
				'class'=>'hidden'
		));
		// Nombre
			$form->addElement('text', 'nombre', array(
					'label' => App::xlat('FORM_LABEL_business'),
					'required' => true,
					'validators' => array(
			            	array('stringLength', true, array(3))
			        ),
			        'class'=>'required',
			));
			$form->getElement('nombre')->getDecorator('Element')->addClass('clear-left');
		// Giro
			$giros[0]=App::xlat('FORM_LABEL_choose_option');
			$giros_list = $this->_module->getModel('Business')->businessGiros();
			$giros_list= $arrays->toAssociative($giros_list, 'id','giro');
			$giros=array_merge($giros,$giros_list);
			$form->addElement('select', 'giro', array(
													'label' => App::xlat('FORM_LABEL_business_activity'),
													'required' => true,
										            'multiOptions' => $giros,
													'class'=>'required',
										      )
			);                
		// Web
			$form->addElement('text', 'web', array(
					'label' => App::xlat('FORM_LABEL_web'),
					'validators' => array(
			            	array('stringLength', true, array(9))
			        ),
			));
			$form->getElement('web')->getDecorator('Element')->addClass('clear-left');
		// Telefono
			$form->addElement('text', 'telefono', array(
					'label' => App::xlat('FORM_LABEL_phone'),
					'validators' => array(
			            	array('stringLength', true, array(7)),
			        ),
			));
			$form->getElement('telefono')->getDecorator('Element')->addClass('clear-left');
		// Email
                $form->addElement('text', 'email', array(
                    'label' => App::xlat('FORM_LABEL_email'),
                    'required' => true,
                    'validators' => array(
                            'EmailAddress',
                            array('stringLength', true, array(6))
                    ),
                    'class'=>'required'
                ));          			
// Fieldset
$form->addDisplayGroup(
    array('nombre','giro','web','telefono','email'),
    'contactinfo',
    array(
		//'legend' => App::xlat('Datos de contacto'),
    )
);
$form->getDisplayGroup('contactinfo')->getDecorator('Group')->addClass('no-border');
			
			
			
	
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°
//	Bloque izquierdo
		// direccion
			$form->addElement('text', 'direccion', array(
					'label' => App::xlat('FORM_LABEL_address'),
					'validators' => array(
			            	array('stringLength', true, array(9))
			        ),
			));
			$form->getElement('direccion')->getDecorator('Element')->addClass('clear-left');

		// Colonia
			$form->addElement('text', 'colonia', array(
					'label' => App::xlat('FORM_LABEL_section'),
					'validators' => array(
			            	array('stringLength', true, array(5))
			        ),
			));
			$form->getElement('colonia')->getDecorator('Element')->addClass('clear-left');
		// C. Postal
			$form->addElement('text', 'cp', array(
					'label' => App::xlat('FORM_LABEL_zip'),
					'validators' => array(
			            	array('stringLength', true, array(5))
			        ),
			));
			$form->getElement('cp')->getDecorator('Element')->addClass('clear-left');
		// Ciudad
			$form->addElement('text', 'ciudad', array(
					'label' => App::xlat('FORM_LABEL_city'),
					'validators' => array(
			            	array('stringLength', true, array(3))
			        ),
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
										            'multiOptions' => $paises,
													'onChange'=>'jsForms.loadSelectList(this.value,"estado","'.App::xlat('FORM_LABEL_loading').'","'.App::xlat('FORM_LABEL_loading_error').'")',
										      )
			);
			/*if($cliente){
				$estados = $world->reset()->setCountry($cliente['pais'])->states();
			}else{
				$estados = $world->reset()->setCountry(1)->states();
			}*/
			if($cliente['pais']>0){
				$estados = $world->reset()->setCountry($cliente['pais'])->states();
				$estados= $arrays->toAssociative($estados, 'id','nombre_'.$idioma);
			}else{
				$estados = array('0'=>App::xlat('FORM_LABEL_choose_country'));
			}
			$form->addElement('select', 'estado', array(
													'label' => App::xlat('FORM_LABEL_state'),
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
    array('direccion','colonia','cp','ciudad','pais','estado'),
    'address'
);
$form->getDisplayGroup('address')->getDecorator('Group')->addClass('no-border');
$form->addDisplayGroup(
    array('botoncillo'),
    'elboton'
);
		$form->populate($cliente);
		return $form;
	}


}