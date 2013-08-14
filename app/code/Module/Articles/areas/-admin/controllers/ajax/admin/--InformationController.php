<?php
require_once 'Module/User/Controller/Action/Admin.php';

class Ajax_Admin_InformationController extends Module_User_Controller_Action_Admin{

	function preDispatch() {
		App::module("Acl")->getModelSingleton('acl')->requirePrivileges('admin');
		$this->designManager()->setCurrentLayout('ajax');
	}

	/**
	 * Editamos el perfil del usuario
	 */
	function personalAction() {
		$form=$this->_module->getModel('User/Forms/Areas/Admin/Personal')->get(@$_POST);

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				if ( $this->_module->getModel('user')->update(false,false,$_POST['nombre'],$_POST['email'],@$_POST['telefono'],@$_POST['extension'],@$_POST['fax'],@$_POST['web'],@$_POST['direccion'],@$_POST['colonia'],@$_POST['cp'],@$_POST['ciudad'],@$_POST['estado'],@$_POST['pais']) ) {
					// Como el EMAIL es utilizado como USERNAME para esta aplicacion, debemos validar si fue modificado
					// Si fue modificado, debemos actualizar TODOS los username de la base de datos que correspondan al anterior
						if($_POST['cliente']!=$_POST['email']){
							$this->_module->getModel('User')->updateUsername($_POST['cliente'],$_POST['email']);
						}
					// Recargamos al usuario. Enviamos el EMAIL por si acaso se modifico el EMAIL
					// y asi recargue todo correctamente. Ya vez que los USERNAME son los emails
					// y si modificas el EMAIL, esto daba error porque buscaba al USERNAME antiguo
					$this->_module->getModel('user')->reload($_POST['email']);
					echo "true"; exit;
				}
			}
			$form->populate($_POST);
		}
		$this->view->form=$form;
	}

	/**
	 * Modificar contraseña
	 */
	function pwdAction() {
		$form=$this->_module->getModel('User/Forms/Areas/Admin/Password')->get();

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				if($_POST['newpwd']==$_POST['confirmpwd']){	 // Revisamos si las claves coinciden
					if ( $this->_module->getModel('user')->passwordReset($_POST['cliente'],$_POST['newpwd']) ) {
						echo 'true'; exit;
					}
				}else{
					$this->view->nomatch=true;
				}
			}
		}
		$this->view->form=$form;
	}

}