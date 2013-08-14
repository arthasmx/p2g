<?php
class Module_User_Repository_Model_User_Forms_Business extends Core_Model_Repository_Model {

	public function get($post=false) {
		$idioma = App::locale()->getLang();
		require_once "Local/Form.php";
		$form=new Local_Form;
        $form->setAttribs(array('autocomplete'=>'off','enctype'=>'multipart/form-data','id'=>'registro-business'));
        $arrays=App::module('Core')->getResource('Arrays');
		//$form->setAction('#');

// NUEVAS EMPRESAS

                $form->addElement('text', 'nuevoemail', array(
                    'description' => App::xlat('PUBLIC_SIGNUP_email'),
                    'required' => true,
                    'validators' => array(
                            'EmailAddress',
                            array('stringLength', true, array(6))
                    ),
                    'class'=>'required'
                     ,'value'=>'arthasmx@gmail.com'
                ));
				$form->addElement(
					'password',
					'nuevopassword',
					array(
						'description' => App::xlat('PUBLIC_SIGNUP_pwd_1'),
						'required' => true,
						'validators' => array(
				            	array('stringLength', true, array(4))
				        ),
                        'class'=>'required'
				));
				$form->addElement(
					'password',
					'nuevopasswordconfirm',
					array(
						'description' => App::xlat('PUBLIC_SIGNUP_pwd_2'),
						'required' => true,
						'validators' => array(
				            	array('stringLength', true, array(4))
				        ),
                        'class'=>'required'
				));
                $form->addDisplayGroup(
                    array('nuevoemail','nuevopassword','nuevopasswordconfirm'),
                    'claves',
                    array(
                        'legend' => App::xlat('PUBLIC_SIGNUP_title_access'),
                    )
                );
                $form->getDisplayGroup('claves')->getDecorator('Group')->addClass('fieldset-borde-derecha');


//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°
// DATOS DE LA EMPRESA

                $form->addElement('text', 'nombre', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_BUSINESS_name"),
                    'required' => true,
                    'validators' => array(
                            array('stringLength', true, array(6))
                    ),
                    'class'=>'required',
                    'value'=>'Tostaditas jhony'
                ));
			// Giro
				$giros[0]=App::xlat('PUBLIC_SIGNUP_BUSINESS_elija_opcion');
				$giros_list = $this->_module->getModel('Business')->businessGiros();
				$giros_list= $arrays->toAssociative($giros_list, 'id','activity');
				$giros=array_merge($giros,$giros_list);
				$form->addElement('select', 'giro', array(
														'description' => App::xlat('PUBLIC_SIGNUP_BUSINESS_giro'),
														'required' => true,
											            'multiOptions' => $giros,
														'class'=>'required',
											      )
				);                
                $form->addElement('text', 'telefono', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_BUSINESS_phone"),
                	'required' => true,
                    'value'=>'986-5623',
                	'class'=>'required'
                ));
                $form->addElement('text', 'direccion', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_address"),
                	'required' => true,
                    'class'=>'required'
                    ,'value'=>'Avenida de las americas #1114'
                ));
                $form->addElement('text', 'colonia', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_section"),
                	'required' => true,
                    'class'=>'required'
                    ,'value'=>'Benito juarez'
                ));
                $form->addElement('text', 'cp', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_zip"),
                	'required' => true,
                    'class'=>'required'
                    ,'value'=>'82180'
                ));
                $form->addElement('text', 'web', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_BUSINESS_web"),
                    'value'=>'http://www.google.com'
                ));                
                $form->addElement('text', 'ciudad', array(
                    'description' => App::xlat("signup_business_txt_ciudad"),
                	'required' => true,
                	'class'=>'required'
                    ,'value'=>'Mazatlán'
                )); 
                
		// Estado
			$world =App::module('Core')->getResource('World');

		// Paises
			$paises= $world->reset()->countries();
			$paises= $arrays->toAssociative($paises, 'id','country');
			$form->addElement('select', 'pais', array(
													'description' => App::xlat('PUBLIC_SIGNUP_country'),
										            'multiOptions' => $paises,
													'class'=>'required',
													'required' => true,
													'onChange'=>'jsForms.loadSelectList(this.value,"estado","'.App::xlat('PUBLIC_SIGNUP_loading').'","'.App::xlat('PUBLIC_SIGNUP_loading_error').'")',
										      )
			);
			if($post && $post['pais']>0){
				$estados = $world->reset()->setCountry($post['pais'])->states();
				$estados= $arrays->toAssociative($estados, 'id','state');
				$estados[0]=App::xlat('PUBLIC_SIGNUP_state');
			}else{
				$estados = array('0'=>App::xlat('PUBLIC_SIGNUP_BUSINESS_choose_country'));
			}

			$form->addElement('select', 'estado', array(
													'description' => App::xlat('PUBLIC_SIGNUP_state'),
													'class'=>'required',
													'required' => true,
										            'multiOptions' => $estados,
										      )
			);

$form->addDisplayGroup(
	array('nombre','giro','web','telefono','direccion','colonia','cp','ciudad','pais','estado'),
		'direcciones',
		array('legend' => App::xlat('PUBLIC_SIGNUP_BUSINESS_title_address'))
);
$form->getDisplayGroup('direcciones')->getDecorator('Group')->addClass('fieldset-borde-derecha');
			

//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°
// DATOS DE LA PERSONA CON LA QUE SE TENDRA CONTACTO

                $form->addElement('text', 'e_nombre', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_name"),
                    'required' => true,
                    'validators' => array(
                            array('stringLength', true, array(6))
                    ),
                    'class'=>'required'
                     ,'value'=>'Nombre del contacto de la empresa'
                ));
                $form->addElement('text', 'e_telefono', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_BUSINESS_phone"),
                	'required' => true,
                    'class'=>'required',
                    'value'=>'988-8565'
                ));
                $form->addElement('text', 'e_extension', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_BUSINESS_extension"),
                    'value'=>'115'
                ));
                $form->addElement('text', 'e_fax', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_BUSINESS_fax"),
                    'value'=>'988-8565'
                ));
                $form->addElement('text', 'e_movil', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_BUSINESS_movil"),
                   	'value'=>'988-8565'
                ));
                $form->addDisplayGroup(
                    array('e_nombre','e_email','e_telefono','e_extension','e_fax','e_movil'),
                    'econtacto',
                    array(
                        'legend' => App::xlat('PUBLIC_SIGNUP_BUSINESS_title_contact'),
                    )
                );
                $form->getDisplayGroup('econtacto')->getDecorator('Group')->addClass('no-border');

// CAPTCHA
			$form->addElement(
				'text',
				'captcha',
				array(
					'required' => true,
					'validators' => array(
			            	array('stringLength', true, array(5)),
			        ),
			        'size'=>5,
			        'maxlength'=>5,
			        'class'=>'required small'
			         ,'value'=>'12345'

			  	)
			);
			$form->getElement('captcha')->getDecorator('AddHtml')->prepend('<img src="'.App::base('/core/captcha/get/'.rand(0,9999)).'" class="captcha"/><br />' . App::xlat("PUBLIC_SIGNUP_captcha"));

             $form->addElement(
	                'submit',
	                'submit',
	                array(
	                	'label'=>App::xlat('PUBLIC_SIGNUP_send'),
	                	'class'=>'basicButton'
	                )
            );

			$form->addDisplayGroup(
                    array('captcha','submit'),
                    'seguridad',
                    array(
                        'legend' => App::xlat('PUBLIC_SIGNUP_title_security'),
                    	
                    )
                );
			$form->getDisplayGroup('seguridad')->getDecorator('Group')->addClass('no-border to-center');


		return $form;
	}

}