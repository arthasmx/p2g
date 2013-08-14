<?php
class Module_User_Repository_Model_User_Forms_Areas_Root_Password extends Core_Model_Repository_Model {

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
		    	'description'=>App::xlat('ROOT_PANEL_FORM_PWDCHANGE_clave'),
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
		    	'description'=>App::xlat('ROOT_PANEL_FORM_PWDCHANGE_confirm'),
		        'required' => true,
		        'validators' => array(
		                array('stringLength', true, array(4))
		        ),
		        'class'=>'required',
		        'size'=>15,
		      )
		);

		$form->addDisplayGroup(
		     array(
		         'oldpwd','newpwd','confirmpwd'
		     ),
		     'pwdReset'
		);
		$form->getDisplayGroup('pwdReset')->getDecorator('Group')->addClass('float-left');

		return $form;
	}

}