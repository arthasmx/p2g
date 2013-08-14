<?php
class Module_User_Repository_Model_User_Forms_Forgotpwd extends Core_Model_Repository_Model {

	public function get() {
		require_once "Local/Form.php";
		$form=new Local_Form;
		$form->setAttrib('id','forgotpwd-form');

			$form->addElement(
				'text',
				'email',
				array(
					'description' => App::xlat('PUBLIC_FORGOT_PASSWORD_email'),
					'required' => true,
					'validators' => array(
			            	array('stringLength', true, array(4)),
			        ),
			        'class'=>'required'
//			         ,'value'=>'robe@gmail.com'
			  	)
			);
			$form->getElement('email')->getDecorator('Element')->addClass('mar-bottom-10');

			$form->addElement(
				'text',
				'captcha',
				array(
					'required' => true,
					'validators' => array(
			            	array('stringLength', true, array(5)),
			        ),
			        'maxlength'=>5
			        ,'class'=>'required small'
//					 ,'value'=>'12345'
			  	)
			);
			$form->getElement('captcha')->getDecorator('AddHtml')->prepend('<img src="'.App::base('/core/captcha/get/'.rand(0,9999)).'" class="captcha"/><br />' . App::xlat("PUBLIC_FORGOT_PASSWORD_captcha"));

		// Submit
			$form->addElement(
								'submit'
								,'submit'
								,array(
										'label' => App::xlat('PUBLIC_FORGOT_PASSWORD_button')
										,'class'=>'basicButton' )
								);

			$form->addDisplayGroup(
				array(
					'usuario','email', 'captcha' , 'submit'
				),
				'recuperar'
			);
			$form->getDisplayGroup('recuperar')->getDecorator('Group')->addClass('float-left');

		return $form;
	}

}