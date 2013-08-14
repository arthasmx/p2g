<?php
class Module_User_Repository_Model_User_Forms_Register extends Core_Model_Repository_Model {

	public function get($post=false) {
		$idioma = App::locale()->getLang();
		require_once "Local/Form.php";
		$form=new Local_Form;
        $form->setAttribs(array('autocomplete'=>'off','enctype'=>'multipart/form-data','id'=>'form-registro'));
		//$form->setAction('#');

		// NUEVOS CLIENTES

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


// DATOS CONTACTO

                $form->addElement('text', 'nombre', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_name"),
                    'required' => true,
                    'validators' => array(
                            array('stringLength', true, array(6))
                    ),
                    'class'=>'required'
                     ,'value'=>'mi nombre de prueba'
                ));
                $form->addElement('text', 'telefono', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_phone")
                     ,'value'=>'986-5623'
                ));
                $form->addDisplayGroup(
                    array('nombre','telefono'),
                    'contacto',
                    array(
                        'legend' => App::xlat('PUBLIC_SIGNUP_title_contact'),
                    )
                );
                $form->getDisplayGroup('contacto')->getDecorator('Group')->addClass('no-border');

// DIRECCIONES
                $form->addElement('text', 'direccion', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_address")
                     ,'value'=>'Avenida de las americas #1114'
                ));
                $form->addElement('text', 'colonia', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_section")
                     ,'value'=>'Benito juarez'
                ));
                $form->addElement('text', 'cp', array(
                    'description' => App::xlat("PUBLIC_SIGNUP_zip")
                    ,'value'=>'82180'
                ));
                $form->addElement('text', 'ciudad', array(
                    'description' => App::xlat("FORM_LABEL_city")
                    ,'value'=>'Mazatlan'
                ));

		// Estado
			$world =App::module('Core')->getResource('World');
			$arrays=App::module('Core')->getResource('Arrays');

		// Paises
			$paises= $world->reset()->countries();
			$paises= $arrays->toAssociative($paises, 'id','country');
			$form->addElement('select', 'pais', array(
													'description' => App::xlat('PUBLIC_SIGNUP_country'),
													'required' => true,
													'class'=>'required',
										            'multiOptions' => $paises,
													'onChange'=>'jsForms.loadSelectList(this.value,"estado","'.App::xlat('PUBLIC_SIGNUP_loading').'","'.App::xlat('PUBLIC_SIGNUP_loading_error').'")',
										      )
			);
			if($post){
				$estados = $world->reset()->setCountry($post['pais'])->states();
				$estados= $arrays->toAssociative($estados, 'id','state');
			}else{
				$estados = array('0'=>App::xlat('Elija un país'));
			}
			$form->addElement('select', 'estado', array(
													'description' => App::xlat('PUBLIC_SIGNUP_state'),
													'required' => true,
													'class'=>'required',
													'multiOptions' => $estados,
										      )
			);

				$form->addDisplayGroup(
                    array('direccion','colonia','cp','ciudad','pais','estado'),
                    'direcciones',
                    array(
                        'legend' => App::xlat('PUBLIC_SIGNUP_title_address'),
                    )
                );
                $form->getDisplayGroup('direcciones')->getDecorator('Group')->addClass('fieldset-borde-derecha');

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