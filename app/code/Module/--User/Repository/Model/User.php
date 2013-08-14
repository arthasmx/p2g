<?php

class Module_User_Repository_Model_User extends Core_Model_Repository_Model {

	public $user 			= null;
	public $data 			= null;

	protected $_errors 		= array();
	protected $_messages = false;
	protected $_doNotSave	= false;

	function doNotSave() {
		$this->_doNotSave	= true;
		return $this;
	}

	/**
	 * Inicialización del modelo
	 *
	 * Se comprueba que el recurso haya sido especificado, se establece el namespace y se cargan los datos.
	 *
	 */
	function init() {
		if (!$this->_namespace) {
			// $this->_namespace=App::module('Core')->getResourceObject('Namespace')->get(get_class($this));

			   $this->_namespace=App::module('Core')->getModel('Namespace')->get(get_class($this));
		}

		$this->_loadData();
		$this->_saveData();
		
		// Cargo los errores con traduccion
		$this->_messages = array(
		'ERROR_NOTFOUND' 	=> App::xlat('PUBLIC_SIGNUP_ERROR_NOTFOUND'), //No se ha podido cargar la información del usuario <b>%user%</b>, contacte con el soporte técnico',
		'ERROR_USEREXISTS'	=> App::xlat('PUBLIC_SIGNUP_ERROR_USEREXISTS'), //'El usuario <b>%user%</b> ya se encuentra registrado en el sistema. Utilize otro.',
		'ERROR_EMAILEXISTS'	=> App::xlat('PUBLIC_SIGNUP_ERROR_EMAILEXISTS') //'El email <b>%email%</b> ya se encuentra registrado en el sistema. Utilize otro.',
		);
	}

	/**
	 * Carga de datos de la sesión
	 *
	 */
	protected function _loadData() {
		// Cargamos todos los atributos públicos
			foreach ($this->_namespace as $var=>$value) {
				$this->{$var}=$value;
			}

		//$this->_loadUserData();
	}

	/**
	 * Carga de datos del resource
	 *
	 * @return bool
	 */
	protected function _loadUserData() {
		// Si está identificado

			if ($this->user) {
				// Instanciamos el modulo ACL
					$aclMod = App::module('Acl')->getModel('Acl');
				// Cargamos datos del usuario
					//$this->data=$this->get($this->user);
					$this->data=App::module('Acl')->getResource('acl')->reset()->setUsername($this->user)->getRow();
				// Cargamos los privilegios
					$this->data['privileges'] = $aclMod->getAllUserPrivileges($this->user);
				// Cargamos los idiomas del sitio
					$this->_setLanguages();
				// Recargamos los valores de la session del modulo ACL
					$aclMod->reload($this->data, $this->user); 

					// Comprobamos si está bloqueado
						if (!$this->data) {
							//$this->_addError('ERROR_NOTFOUND',array('user'=>$this->user));
							App::module('Core')->getModel('Flashmsg')->error(App::xlat('PUBLIC_SIGNUP_ERROR_NOTFOUND'));
							App::events()->dispatch('module_user_notfound',array('user'=>$this->user),false,Core_Model_Log::ERR);
							$this->_unloadData();
							return false;
						}

					// Si tiene un referido actualizamos el referido actual
					/*
						if (isset($this->data['referer']) && !empty($this->data['referer'])) {
							$this->_module->getModelSingleton('user/referer')->set($this->data['referer'],false);
						} else {
							$this->_module->getModelSingleton('user/referer')->clear();
						}
					*/

				$this->_saveData();
				return true;
			}
			return false;
	}

	/**
	 * Almacena datos en la sesión
	 *
	 */
	protected function _saveData() {
		if (!$this->_doNotSave) {
			// Almacenamos todos los atributos públicos
				foreach ($this as $var=>$value) {
					if ($var[0]!="_") {
						$this->_namespace->{$var}=$value;
					}
				}
		}
	}

	/**
	 * Elimina datos de la sesión y reinicia el usermanager
	 *
	 */
	protected function _unloadData() {
		// Eliminamos todos los atributos públicos y reiniciamos el namespace
			foreach ($this as $var=>$value) {
				if ($var[0]!="_") {
					$this->{$var}=null;
				}
			}
			$this->_namespace->unsetAll();
			//$this->_namespace->lock();
	}

	protected function _addError($errorKey,$vars=array()) {
		$this->_errors[$errorKey]=array(
			"message"=>@$this->_messages[$errorKey],
			"vars"=>$vars
		);
	}

	protected function _setLanguages(){
	    echo 'Mover este metodo al modulo ADDONS'; exit;
		$xLang = App::module('Core')->getResource('Languages')->languages();
		foreach ($xLang as $lang) {
			$langs[$lang['prefix']]=$lang;
		}
		$this->data['languages']=$langs;
	}
	
// MAIN ****************************************************************************************

	/**
	 * Devuelve los errores que se hayan producido y vacia la cola de errores
	 *
	 * @return mixed [array | bool]
	 */
	function flushErrors () {
		if (count($this->_errors)) {
			$errors=$this->_errors;
			$this->_errors=array();
			return $errors;
		}
		return false;
	}

	public function load($user) {
		$this->flushErrors();
		$this->user=$user;
		return $this->_loadUserData();
	}

	public function unload() {
		$this->_unloadData();
	}

	public function reload($user) {
		$this->user = mysql_escape_string($user);
		$this->_loadUserData();
	}
	/*Antes de hacer que recibiera parametros.
	 * Hice que los recibiera, pues al hacer RELOAD, this->user, es el USERNAME y como yo hice que los username
	 * fueran los emails. Esto provoca que $this->user contenga al USERNAME anterior. Es por ello q debo enviarlo 
	 * de nuevo
	 * public function reload() {
		if ($this->user) {
			$this->_loadUserData();
			return true;
		}
		return false;
	}*/

	public function getData($key=null) {
		if (isset($this->data[$key])) {
			return $this->data[$key];
		} else {
			return false;
		}
	}

	public function get($user) {
		return $this->_resource->reset()->setUsername($user)->getRow();
	}

	/**
	 * Preparamos metodo para crear al usuario en el sitio
	 *
	 * @param unknown_type $user
	 * @param unknown_type $passwd
	 * @param unknown_type $nombre
	 * @param unknown_type $email
	 * @param unknown_type $telefono
	 * @param unknown_type $direccion
	 * @param unknown_type $colonia
	 * @param unknown_type $cp
	 * @param unknown_type $ciudad
	 * @param unknown_type $estado
	 * @param unknown_type $pais
	 * @param unknown_type $privilege
	 * @param unknown_type $web
	 * @param unknown_type $giro
	 * @return unknown
	 */
	function create($user,$passwd,$nombre=false,$email,$telefono=false,$direccion=false,$colonia=false,$cp=false,$ciudad=false,$estado=false,$pais=false,$privilege=1,$web=false,$giro=false) {
		if(!$ciudad) { $ciudad = ':: missed ::'; }
		$user=mysql_escape_string($user);
		$passwd=mysql_escape_string($passwd);
		$email=mysql_escape_string($email);
		
		// Comprobamos si no existe el usuario
			if ( $this->_resource->reset()->setUsername($user)->userExist() >0 ) {
				$this->_addError('ERROR_USEREXISTS',array('user'=>$user));
				//App::module('Core')->getModel('Flashmsg')->error(App::xlat('El email ya se encuentra registrado en el sistema. Utilize otro.'));
				return false;
			}

		// Comprobamos si no existe el email
			if ( $this->_resource->reset()->setEmail($email)->userExist() >0) {
				$this->_addError('ERROR_EMAILEXISTS',array('email'=>$email));
				//App::module('Core')->getModel('Flashmsg')->error(App::xlat('El email ya se encuentra registrado en el sistema. Utilize otro.'));
				return false;
			}

		// Creamos
			$resp=$this->_resource->create($user,$nombre,$email,$telefono,$direccion,$colonia,$cp,$ciudad,$estado,$pais,$web,$giro);

		// Lanzamos evento para que Otros módulos puedan sincronizarse con éste
			//App::events()->dispatch('module_user_create',array('user'=>$user,'passwd'=>$passwd,'email'=>$email,'referer'=>$referer,'telefono'=>$telefono,'extension'=>$extension,'fax'=>$fax,'tipo'=>$privilege),false,Core_Model_Log::NOTICE );

		if($resp){

			// Segun el privilegio del usuario a crear, sera lo que lanzaremos
			switch ($privilege) {
				case 1: // Es un usuario comun
				default:
						// Creamos el registro que le pertenece en tabla ACL, por medio del EVENTO module_user_registrer
						// Se envia el EMAIL al usuario despues
						// Termina el proceso
						$core= App::module('Core')->getResource('World');
						$estado=$core->getState($estado);	// Sacamos el nombre del estado
						$pais=$core->getCountry($pais);		// Sacamos el nombre del pais
						App::events()->dispatch('module_user_register',array('user'=>$user,'passwd'=>$passwd,'email'=>$email,'telefono'=>$telefono,'nombre'=>$nombre,'direccion'=>$direccion,'colonia'=>$colonia,'cp'=>$cp,'pais'=>$pais['country'],'estado'=>$estado['state'],'active'=>0,'tipo'=>$privilege),false,Core_Model_Log::NOTICE );
						break;

				case 2: // Es una empresa
						// Creamos el registro que le pertenece en el tabla ACL por medio del evento de abajo
						App::events()->dispatch('module_user_business_register',array('user'=>$user,'passwd'=>$passwd,'email'=>$email,'active'=>0,'tipo'=>$privilege),false,Core_Model_Log::NOTICE );
						break;
				case 3: // Es un Admin
						break;
			}
			
			return true;
		}else{
			return false;
		}

	}

	public function update($nombre=false,$email=false,$telefono=false,$extension=false,$fax=false,$web=false,$direccion=false,$colonia=false,$cp=false,$ciudad=false,$estado=false,$pais=false) {
		$user=App::module('Acl')->getModel('Acl')->user;
		$this->_resource->setUsername( $user )
						->update($nombre,$email,$telefono,$extension,$fax,$web,$direccion,$colonia,$cp,$ciudad,$estado,$pais);

		// Si el usuario es el mismo que el referido, recargamos los datos del referido (para actualizar el bloque En asociación con...)
			/*if ($user==$referer) {
				$this->_module->getModelSingleton('user/referer')->reload();
			}*/

		// Lanzamos evento para que Otros módulos puedan sincronizarse con éste (ACL)
			App::events()->dispatch('module_user_update',array('user'=>$user,'email'=>$email),false,Core_Model_Log::NOTICE);
		return true;
	}

	// Como el EMAIL es utilizado como USERNAME para esta aplicacion, debemos validar si fue modificado
	// Si fue modificado, debemos actualizar TODOS los username de la base de datos que correspondan al anterior
	public function updateUsername($actual_username=false,$nuevo_username=false){
		if(!$actual_username || !$nuevo_username) return false;

		// Todas las tablas que utilicen USERNAME
		$tablas = array('acl','acl_account_activate','acl_forgotpwd','articles_details','categories','comments','user','user_privileges','user_reminder','user_support');
		$fields = array('username','email');
		$this->_module->getResource('User')->updateUsername($actual_username, $nuevo_username, $tablas, $fields);
	}

	function pdf($clientes=false){
		if(!$clientes) return false;
			// Buscamos obtener los datos de los clientes 1x1(direccion,calle,cp,estado,ciudad,pais)
			foreach($clientes AS $key=>$cliente){
				// Sacamos los datos de los clientes
				$datos=$this->get($cliente);

				$tmp['username']=$cliente;
				$tmp['nombre']=$datos['nombre'];
				$tmp['direccion']=$datos['direccion'];
				$tmp['colonia']=$datos['colonia'];
				$tmp['cp']=$datos['cp'];
				$tmp['ciudad']=$datos['ciudad'];
				$tmp['estado']=$datos['estado_'.App::locale()->getLang()];
				$tmp['pais']=$datos['pais_'.App::locale()->getLang()];
				$clientes[$key]=$tmp;
			}

		if(sizeof($clientes)<1){
			return false;
		}

		// Ya con los datos, iniciamos la creacion del PDF etiquetero
			return $this->_module->getResource('User')->pdf($clientes);
	}

	/** Cambiamos el password de 1 usuario
	 */
	public function passwordReset($cliente,$password){
		return App::module('Acl')->getResource('Acl')->reset()->changePasswd($cliente,$password);
	}

}