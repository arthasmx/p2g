<?php
class Module_Acl_Observer_Privileges extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		$acl=$this->_module->getModelSingleton( 'acl' );

		if (isset($options['action']->aclRequirePrivileges) && $options['action']->aclRequirePrivileges!=false && !empty($options['action']->aclRequirePrivileges)) {

				// Comprobamos si el usuario dispone de los privilegios necesarios
					if ( !$acl->requirePrivileges( $options['action']->aclRequirePrivileges ) )	{

						// El propio requirePrivileges() realiza el despachado de los eventos en caso de
						// que no disponga de privilegios por lo que no es necesario despacharlos aqui
						// Tampoco es necesario mostrar los errores aqui
						// Es mejor crear un observador para module_acl_noprivileges
						// Y mostrar el error ahí.

						// En el caso de que no haya ningun observador de los eventos, redireccionamos a la página principal por si acaso.
							header('Location: '.App::base('/'));
							exit;
					}

		}

	}

}