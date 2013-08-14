<?php
require_once 'Module/Acl/Controller/Action/Frontend.php';

class Acl_ForgotpwdController extends Module_Acl_Controller_Action_Frontend   {

	function recoverAction() {
		// Accedemos al modelo que gestiona la recuperacion de contraseñas (que no es más que un módulo que permite hacer login a través de un hash y que el hash expire por usos o por tiempo)
			$validator	= App::module('Acl')->getModelSingleton('acl/forgotpwd')->setHash($this->getRequest()->getParam('hash'));
			if ($validator->validate()) {
				// Si se ha podido iniciar la sesión del usuario del hash redireccionamos a la página de cambio de contraseña
				if ($validator->doLogin()) {
					App::module('Core')->getModel('flashmsg')->success(	App::xlat("PUBLIC_FORGOT_PASSWORD_restored_ok_3") ." <strong>".$validator->getUsername()."</strong> ". App::xlat("PUBLIC_FORGOT_PASSWORD_restored_ok_4") );
					//App::module('Core')->getResourceSingleton('flashmsg')->info(App::xlat("Por seguridad, el link que ha seguido para acceder a la cuenta ha sido desactivado."));
					header("Location: ".App::base("/password-recover/set-new-password")); // Redireccionamos a la página de cambio de contraseña.
					exit;
				}
			}

		// En caso de que por cualquier motivo no se haya podido validar el hash, mostramos error
			App::module('Core')->getModel('flashmsg')->error( App::xlat("PUBLIC_FORGOT_PASSWORD_restored_error") );
			header("Location: ".App::base("/site/reset-pass")); // Redireccionamos a la página de recuperación de la contraseña.
			exit;
	}
	
	/**
	 * El cliente va a cambiar su contraseña
	 */
	function setNewPasswordAction() {
		$this->designManager()->setCurrentLayout('public');
		// Requerimos que el usuario esté identificado para acceder a estas acciones
		if(!App::module('Acl')->getModel('acl')->isLogged()){
			App::module('Core')->getResourceSingleton('Flashmsg')->error( App::xlat('PUBLIC_FORGOT_PASSWORD_error_not_logged') );
			header('Location: ' . App::www('/') );
		}
				

		// FORMULARIO
			$form=$this->_module->getModelObject('Acl/Forms/Pwd')->get();

			if ($this->getRequest()->isPost()) {

				// Comprobamos que las contraseñas coincidan
						if (@$_POST['passwd'] != @$_POST['passwdconfirm']) {
							$form->getElement('passwd')->getValidator('Custom')->addError("valuesDontMatch", App::xlat('PUBLIC_FORGOT_PASSWORD_error_pwd_dont_match') );
						}

				if ($form->isValid($_POST)) {
					// Realizamos login
						if ( App::module('Acl')->getModelSingleton('acl')->changePasswd($_POST['passwd']) ) {
							App::module('Core')->getResourceSingleton('Flashmsg')->success(App::xlat('PUBLIC_FORGOT_PASSWORD_ok_pwd_changed'));
							// Redireccionamos al usuario al area que le corresponde
							App::module('Acl')->getModel('acl')->loginRedirectArea();
   							exit;
						}
				}
				$form->populate($_POST);
			}

		$this->view->form=$form;
	}	

}