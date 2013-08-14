<?php
class Module_User_Repository_Model_User_Forms_Areas_Business_Password extends Core_Model_Repository_Model {

	public function get() {
		require_once "Local/Form.php";
		$form=new Local_Form;
		$form->setAttrib('id','password-change-form');

		// Username
		$form->addElement('hidden', 'cliente', array(
		        'value'=>App::module('Acl')->getModel('Acl')->data['username'],
				'class'=>'hidden'
		));

		$form->addElement(
		    'password',
		    'newpwd',
		    array(
		    	'label'=>App::xlat('FORM_LABEL_pwd'),
		        'required' => true,
		        'validators' => array(
		                array('stringLength', true, array(4))
		        ),
		        'class'=>'required',
		        'size'=>15,
		      )
		);

		$form->addElement(
		    'password',
		    'confirmpwd',
		    array(
		    	'label'=>App::xlat('FORM_LABEL_pwd_confirm'),
		        'required' => true,
		        'validators' => array(
		                array('stringLength', true, array(4))
		        ),
		        'class'=>'required',
		        'size'=>15,
		      )
		);

		$form->addElement(
			'button',
			'boton',
			array(
			'label'=>App::xlat('FORM_LABEL_save'),
			'class'=>'basicButton',
			'onclick' => "account.pwd('cfg-settings-pwd','true');return false;"
			)
		);			
		
		$form->addDisplayGroup(
		     array(
		         'oldpwd','newpwd','confirmpwd'
		     ),
		     'pwdReset'
		);
		$form->getDisplayGroup('pwdReset')->getDecorator('Group')->addClass('no-border');
		// Boton
		$form->addDisplayGroup(
		     array(
		         'boton'
		     ),
		     'pwdChange'
		);
		$form->getDisplayGroup('pwdChange')->getDecorator('Group')->addClass('no-border');
				
		return $form;
	}

}