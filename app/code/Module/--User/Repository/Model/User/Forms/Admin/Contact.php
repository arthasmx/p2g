<?php
class Module_User_Repository_Model_User_Forms_Admin_Contact extends Core_Model_Repository_Model {

	public function get($post=false,$cliente) {
	$idioma = App::locale()->getLang();
	require_once "Local/Form.php";
	$form=new Local_Form;
	$form->setAttrib('id','perfil-edit-form');

	if(!$post){
		$cliente=$this->_module->getModel('User')->get( $cliente );
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
					'description' => App::xlat('ADMIN_PANEL_CLIENTS_DETAIL_name'),
					'required' => true,
					'validators' => array(
			            	array('stringLength', true, array(3))
			        ),
			        'size'=>35,
			));
//			$form->getElement('nombre')->getDecorator('Element')->addClass('bg');

		// Email
			$form->addElement('text', 'email', array(
					'description' => App::xlat("ADMIN_PANEL_CLIENTS_DETAIL_email"),
					'required' => true,
		            'validators' => array(
		                	'EmailAddress',
		                	array('stringLength', true, array(6))
		            ),
		            'size'=>35,
			));
//			$form->getElement('email')->getDecorator('Element')->addClass('bg');

		// Telefono
			$form->addElement('text', 'telefono', array(
					'description' => App::xlat('ADMIN_PANEL_CLIENTS_DETAIL_phone'),
					'required' => false,
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
					'description' => App::xlat('ADMIN_PANEL_CLIENTS_DETAIL_ext'),
					'validators' => array(
			            	array('stringLength', false, array(1))
			        ),
			        'size'=>5,
			  	)
			);
//			$form->getElement('extension')->getDecorator('Element')->addClass('width-30');

		// Fax
			$form->addElement('text', 'fax', array(
					'description' => App::xlat('ADMIN_PANEL_CLIENTS_DETAIL_fax'),
					'validators' => array(
			            	array('stringLength', true, array(7))
			        )
			));
			$form->getElement('fax')->getDecorator('Element')->addClass('clear-left');


		// Web
			$form->addElement('text', 'web', array(
					'description' => App::xlat('ADMIN_PANEL_CLIENTS_DETAIL_web'),
					'validators' => array(
			            	array('stringLength', true, array(9))
			        ),
			        'size'=>35,
			));
			$form->getElement('web')->getDecorator('Element')->addClass('clear-left');

			$form->addElement('text', 'maxreservations', array(
					'description' => App::xlat('ADMIN_PANEL_CLIENTS_EDIT_reservation_limit'),
					'required' => true,
					'validators' => array(
			            	array('stringLength', true, array(1))
			        ),
			        'size'=>35,
			));
			$form->getElement('maxreservations')->getDecorator('Element')->addClass('clear-left');
			
			
$form->addDisplayGroup(
    array('nombre','email','telefono','extension','fax','web', 'maxreservations'),
    'contactinfo',
    array(
		//'legend' => App::xlat('Datos de contacto'),
    )
);
$form->getDisplayGroup('contactinfo')->getDecorator('Group')->addClass('no-border float-left pad-10');


		// direccion
			$form->addElement('text', 'direccion', array(
					'description' => App::xlat('ADMIN_PANEL_CLIENTS_DETAIL_address'),
					'validators' => array(
			            	array('stringLength', true, array(9))
			        ),
			        'size'=>35,
			));
			$form->getElement('direccion')->getDecorator('Element')->addClass('clear-left');

		// Colonia
			$form->addElement('text', 'colonia', array(
					'description' => App::xlat('ADMIN_PANEL_CLIENTS_DETAIL_section'),
					'validators' => array(
			            	array('stringLength', true, array(5))
			        ),
			        'size'=>35,
			));
			$form->getElement('colonia')->getDecorator('Element')->addClass('clear-left');

		// C. Postal
			$form->addElement('text', 'cp', array(
					'description' => App::xlat('ADMIN_PANEL_CLIENTS_DETAIL_zip'),
					'validators' => array(
			            	array('stringLength', true, array(5))
			        ),
			        'size'=>10,
			));
			$form->getElement('cp')->getDecorator('Element')->addClass('clear-left');

		// Ciudad
			$form->addElement('text', 'ciudad', array(
					'description' => App::xlat('ADMIN_PANEL_CLIENTS_DETAIL_city'),
					'validators' => array(
			            	array('stringLength', true, array(3))
			        ),
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
													'description' => App::xlat('ADMIN_PANEL_CLIENTS_DETAIL_country'),
										            'multiOptions' => $paises,
													'onChange'=>'genericos.loadSelectList(this.value,"estado","'.App::xlat('ADMIN_PANEL_CLIENTS_EDIT_loading').'","'.App::xlat('ADMIN_PANEL_CLIENTS_EDIT_loading_error').'")',
										      )
			);
			if($cliente){
				$estados = $world->reset()->setCountry($cliente['pais'])->states();
			}else{
				$estados = $world->reset()->setCountry(1)->states();
			}

			$estados= $arrays->toAssociative($estados, 'id','nombre_'.$idioma);
			$form->addElement('select', 'estado', array(
													'description' => App::xlat('ADMIN_PANEL_CLIENTS_DETAIL_state'),
										            'multiOptions' => $estados,
										      )
			);


$form->addDisplayGroup(
    array('direccion','colonia','cp','pais','estado','ciudad'),
    'address',
    array(
		//'legend' => App::xlat('Dirección'),
    )
);
$form->getDisplayGroup('address')->getDecorator('Group')->addClass('no-border float-left pad-10');



/*Array
(
    [username] => robe
    [email] => arthasmx@gmail.com
    [telefono] => 123456789
    [extension] => 123
    [fax] =>
    [web] =>
    [referer] =>
    [ciudad] =>
    [estado] => 1
    [pais] => 1
    [codigo_postal] => 82180
)
*/
		$form->populate($cliente);
		return $form;
	}


}