<?php
class Module_User_Repository_Model_User_Forms_Areas_Root_UserPrivileges extends Core_Model_Repository_Model {

	public function get($user=false) {
		require_once "Local/Form.php";
		$form=new Local_Form;
		$form->setAttrib('id','privileges-change-form');

		// Username
		$form->addElement('hidden', 'user', array(
		        'value'=>$user,
				'class'=>'hidden'
		));

		
		$form->addElement(
							'multiCheckbox'
							,'privilegios'
							, array(
								'multiOptions' => array(
														"carboncopy" => " ".App::xlat("Deseo recibir una copia de este mensaje")
													)
							)
		);		
		
/*		
        $form->addElement(
            'password',
            'newpwd',
            array(
            	'description'=>App::xlat('ADMIN_PANEL_FORM_PWDCHANGE_clave'),
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
                    	'description'=>App::xlat('ADMIN_PANEL_FORM_PWDCHANGE_confirm'),
                        'required' => true,
                        'validators' => array(
                                array('stringLength', true, array(4))
                        ),
                        'class'=>'required',
                        'size'=>15,
                      )
                );

                $form->addElement(
                    'checkbox',
                    'sendtoclient',
                    array(
                    	'description'=>App::xlat('ADMIN_PANEL_FORM_PWDCHANGE_sendtoclient'),
                      )
                );

           $form->addDisplayGroup(
                array(
                    'oldpwd','newpwd','confirmpwd','sendtoclient'
                ),
                'pwdReset'
            );
            $form->getDisplayGroup('pwdReset')->getDecorator('Group')->addClass('float-left');
*/
		return $form;
	}

}