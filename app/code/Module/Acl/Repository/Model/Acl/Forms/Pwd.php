<?php

class Module_Acl_Repository_Model_Acl_Forms_Pwd extends Core_Model_Repository_Model {

	public function get() {
		require_once "Local/Form.php";
		$form=new Local_Form;
		$form->setAttribs(array('autocomplete'=>'off','id'=>'form-pwd-recover'));

			$form->addElement(
				'password',
				'passwd',
				array(
					//'label' => App::xlat('PUBLIC_FORGOT_PASSWORD_new_pwd'),
					'description' => App::xlat('PUBLIC_FORGOT_PASSWORD_new_pwd'),
					'required' => true,
					'class'=>'required',
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
					//'label' => App::xlat('PUBLIC_FORGOT_PASSWORD_pwd_confirmation'),
					'description' => App::xlat('PUBLIC_FORGOT_PASSWORD_pwd_confirmation'),
					'required' => true,
					'class'=>'required',
					'validators' => array(
			            	array('stringLength', true, array(4))
			        ),
			        'size'=>12,
			  	)
			);
            $form->addDisplayGroup(
                array('passwd','passwdconfirm'),
                'forgotpwdclaves',
                array(
                    //'legend' => App::xlat('PUBLIC_SIGNUP_title_access'),
                )
            );
			
		// Cambiar tipo
			$url=App::base("/");
			$form->addElement('button','back',array(
														'label' => "<< ".App::xlat("PUBLIC_FORGOT_PASSWORD_button_cancel")
														//'label' => App::xlat(App::xlat(''))." >>"
														,'onclick' => 'document.location.href="'.$url.'"'
														,'class'=>'brown-small-button nomargin'
			 										)
			);
			$form->getElement('back')->getDecorator('Element')->addClass('float-left mar-right-10');

		// Submit
			$form->addElement('submit'
							  ,'submit'
							  ,array( 
							  			'label' => App::xlat(App::xlat('PUBLIC_FORGOT_PASSWORD_button_change'))." >>" 
							  			,'class'=>'brown-small-button nomargin'
							  ));

            $form->addDisplayGroup(
                array('back','submit'),
                'forgotpwdbotones',
                array(
                		'class'=>'pad-left-20'
                    //'legend' => App::xlat('PUBLIC_SIGNUP_title_access'),
                )
            );

		return $form;
	}

}