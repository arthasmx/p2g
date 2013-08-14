<?php
class Module_User_Observer_Business_Entidad extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		$business=$this->_module->getModel( 'business' );
		$direccion_id=0; $contacto_id=0;
		
		// Creamos registro en tabla USER_CONTACT_ADRESS. (Solamente si este lo requiere)
			if(App::getConfig('entidad_usa_direccion')==1){
				$direccion_id=$business->userContactAdress(@$options['e_direccion'],@$options['e_colonia'],@$options['e_cp'],@$options['e_estado'],@$options['e_pais']);
			}
		// Creamos registro en tabla USER_CONTACT_DATA. (Solamente si este lo requiere)
			if(App::getConfig('entidad_usa_contacto')==1){
				$contacto_id=$business->userContactData(@$options['e_nombre'],@$options['e_email'],@$options['e_telefono'],@$options['e_extension'],@$options['e_fax'],@$options['e_movil']);
			}
		// Creamos el usuario en tabla USER_CONTACT
			$business->userContact($direccion_id,$contacto_id,$options['user']);

		return true;
	}

}