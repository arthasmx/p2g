<?php
class Module_User_Repository_Model_Business extends Core_Model_Repository_Model {

	protected $_resource = false;
	
	function init() {
		if (!$this->_resource) {
			$this->_resource=$this->_module->getResource('business');
		}
	}

//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°	

	/**
	 * Metodo para actualizar los datos de la empresa
	 */
	public function companyUpdate($user,$nombre=false,$email=false,$giro=false,$web=false,$telefono=false,$direccion=false,$colonia=false,$cp=false,$ciudad=false,$pais=false,$estado=false) {
		if (!$user) $this->_module->exception("PUBLIC_SIGNUP_ERROR_NOT_USER_SET");
		$this->_resource->reset()
						->setUsername($user)
						->setEmail($email)
						->setNombre($nombre)
						->setGiro($giro)
						->setWeb($web)
						->setTelefono($telefono)
						->setDireccion($direccion)
						->setColonia($colonia)
						->setCp($cp)
						->setCiudad($ciudad)
						->setPais($pais)
						->setEstado($estado)
						->companyUpdate();
		return true;
	}	

	/**
	 * Creamos los datos de direccion para una entidad
	 *
	 * @param unknown_type $direccion
	 * @param unknown_type $colonia
	 * @param unknown_type $cp
	 * @param unknown_type $estado
	 * @param unknown_type $pais
	 * @return unknown
	 */
	function userContactAdress($direccion='',$colonia='',$cp='',$estado='1',$pais='1'){
		return $this->_resource->userContactAdress($direccion,$colonia,$cp,$estado,$pais);
	}
	
	/**
	 * Creamos los datos de contacto para una entidad
	 *
	 * @param unknown_type $nombre
	 * @param unknown_type $email
	 * @param unknown_type $telefono
	 * @param unknown_type $extension
	 * @param unknown_type $fax
	 * @param unknown_type $movil
	 * @return unknown
	 */
	function userContactData($nombre='',$email='',$telefono='',$extension='',$fax='',$movil=''){
		return $this->_resource->userContactData($nombre,$email,$telefono,$extension,$fax,$movil);
	}
	
	/**
	 * Creamos los datos de Entidad 
	 *
	 * @param unknown_type $direccion_id
	 * @param unknown_type $contacto_id
	 * @param unknown_type $userid
	 * @return unknown
	 */
	function userContact($direccion_id=0,$contacto_id=0,$userid=''){
		return $this->_resource->userContact($direccion_id,$contacto_id,$userid);
	}
	
	/**
	 * Sacamos los giros de las empresas
	 */
	function businessGiros(){
		return $this->_resource->setLanguage(App::locale()->getLang())->businessGiros();
	}

	/**
	 * Sacamos el nombre del giro por su ID
	 */
	function getGiroById($giro=false){
		return $this->_resource->setLanguage(App::locale()->getLang())->setId($giro)->getGiroById();
	}	

/**
 * ENTIDADES 
 */

	/**
	 * Detalle de 1 entidad segun su ID de entidad y segun su ID de propietario
	 * No importa si la empresa tiene varias entidades, saca solamente 1
	 *
	 * @param string $entidad
	 * @param string $owner
	 * @return Array
	 */
	public function getEntidadByOwner($owner=false) {
		if(!$owner) return false;
		return $this->_resource->reset()->setUsername($owner)->setRow(true)->entidadDetail();
	}



}