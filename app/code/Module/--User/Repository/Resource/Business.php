<?php
require_once 'Module/User/Repository/Resource/Abstract.php';
class Module_User_Repository_Resource_Business extends Module_User_Repository_Resource_Abstract {

/* MAIN ***************************************************************************************************/
	protected $status		= false;
	protected $maxstatus= false;
	protected $privilege= false;
	protected $maxprivilege= false;
	protected $total_privilegios = false;
	protected $activity= false;

	/**
	 * Filtros, ordena, limites
	 */
	function setWhere(){
		//Propiedades
			if ($this->username!==false)  			$where[] = "username = '$this->username'";
			if ($this->email!==false) 				$where[] = "email='$this->email'";
			if ($this->maxstatus!==false)  			$where[] = "status <= '$this->maxstatus'";
			if ($this->privilege!==false)  			$where[] = "privileges= '$this->privilege'";
			if ($this->maxprivilege!==false)  		$where[] = "privileges<= '$this->maxprivilege'";
			if ($this->total_privilegios!==false)  	$where[] = "total_privileges= '$this->total_privilegios'";
			if ($this->language!==false)  			$where[] = "language = '$this->language'";

		$order = array();
        $group = array();

		// Limite
			if ($this->limit) $this->limit = 'LIMIT '.$this->limit;
		// Ordenación
			if ($this->order) $order=$this->order;
		// Agrupacion
			if ($this->group) $group=$this->group;

		// DATAFILTER ===========================
			// Cargamos todos los campos activos y añadimos sus condiciones al where para la consulta
			if ( $this->datafilter && $this->datafilter->isActive() ) {
				require_once('Xplora/Datafilter/Sql.php');
				foreach ($this->datafilter->getFields() as $id=>$field) {
					if (true===$field->getActive() && strtolower($field->gettype())!='attribute') {
						$where[]=Xplora_Datafilter_Sql::getFieldCondition($field);
					}
				}
			}
		// DATAFILTER ===========================

		// DATASORTER ===========================
			// Cargamos la ordenación asignada y la añadimos a la ordenación para la consulta
			if ((false===$order || !count($order)) && $this->datasorter) {
				if (is_array($sort=$this->datasorter->getSort())) {
					foreach ($sort as $field) {
						$order[$field->getFieldname()]=$field->getSort_type();
					}
				}
			}
		// DATASORTER ===========================

		// WHERE
			if (count(@$where)) {
				$this->where="WHERE ".implode(" AND ",$where);
			} else {
				$this->where=false;
			}
		// GROUP BY
			if (count(@$group)) {
				$this->group="GROUP BY ".implode(", ",$group);
			} else $this->group=false;
		// ORDER BY
			if (count(@$order)) {
				foreach((array)$order as $key=>$value) {
					$order[$key]=$key." ".$value;
				}
				$this->order="ORDER BY ".implode(", ",$order);
			} else $this->order=false;

		return $this;
	}

	public function get($todos_los_campos=false) {
		$this->setWhere();

		if($todos_los_campos){
			$this->_query="	SELECT * 
							FROM vista_user_detalle
							$this->where
							$this->group
							$this->order
							$this->limit";
		}else{
			$this->_query .= " ". $this->where ." ". $this->group ." ". $this->order ." ". $this->limit;
		}

		if (!$this->_query) return false;

//echo '<pre>'; print_r($this->_query); echo '</pre>';//exit;

		if($this->row){
			$result=$this->_db->query($this->_query)->fetch();
		}else{

			$result=false;
			// Si existe una página y no se ha solicitado un solo registro y no existe límite, intentamos obtener el rowset
				if (false!==$this->page && false===$this->limit) {
					// PAGINATOR ===========================
						// Pasamos el control al paginador para que realize los cálculos y la consulta
						// Devolverá un array con las claves 'paginator' e 'items'
						require_once('Xplora/Paginate/Sql.php');
						$paginator=new Xplora_Paginate_Sql();
						$result=$paginator->setItems_per_page((int)$this->items_per_page)
									->setPage_current((int)$this->page)
									->setDb_adapter($this->_db)
									->setQuery($this->_query)
									->paginate();
					// PAGINATOR ===========================
				} else {
					$result=$this->_db->query($this->_query)->fetchAll();
				}
		}

		return $result;
	}

//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA EL FRONTEND
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

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
	function userContactAdress($direccion,$colonia,$cp,$estado,$pais){
		$query = "INSERT INTO user_contact_adress(adress,section,zip,state,country) VALUES('".mysql_escape_string($direccion)."','".mysql_escape_string($colonia)."','".mysql_escape_string($cp)."','".mysql_escape_string($estado)."','".mysql_escape_string($pais)."')";
		$this->_db->query($query);
		return $this->_db->lastInsertId();
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
	function userContactData($nombre,$email,$telefono,$extension,$fax,$movil){
		$query = "INSERT INTO user_contact_data(name,email,phone,ext,fax,celphone) VALUES('".mysql_escape_string($nombre)."','".mysql_escape_string($email)."','".mysql_escape_string($telefono)."','".mysql_escape_string($extension)."','".mysql_escape_string($fax)."','".mysql_escape_string($movil)."')";
		$this->_db->query($query);
		return $this->_db->lastInsertId();
	}

	/**
	 * Creamos los datos de Entidad 
	 *
	 * @param unknown_type $direccion_id
	 * @param unknown_type $contacto_id
	 * @param unknown_type $userid
	 * @return unknown
	 */
	function userContact($direccion_id,$contacto_id,$userid){
		$query = "INSERT INTO user_contact (adress_id,data_id,owner,created) VALUES('".mysql_escape_string($direccion_id)."','".mysql_escape_string($contacto_id)."','".mysql_escape_string($userid)."',now())";
		$this->_db->query($query);
		return true;
	}
	
	/**
	 * Sacamos los giros de las empresas para el formulario de registro
	 * Aqui importa el status, pues es para el frontEND
	 */
	function businessGiros(){
		$this->_query="SELECT id,activity FROM vista_business_activities";
		return $this->setLanguage(App::locale()->getLang())->get();
	}

	/**
	 * Sacamos el nombre del giro por su ID
	 */
	function getGiroById(){
		if(!$this->id) return false;
		$this->_query="SELECT activity FROM vista_business_activities";
		return $this->setRow(true)->get();
	}

//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA LAS EMPRESAS (usuarios que son empresas)
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

	/**
	 * Metodo para actualizar los datos de la empresa
	 */
	function companyUpdate(){
			$request=$this->asArray();
		// Parámetros
			$params=array();
			foreach($this->asArray() as $key=>$value) $params[]=sprintf("%s='%s'",$key,mysql_escape_string((string)$value));
			if (!count($params)) {
				$this->_module->exception(App::xlat("Se necesitan establecer las propiedades para poder actualizar una empresa"));
			}
		// Consulta
			$query="UPDATE user SET ".implode(",".PHP_EOL,$params)." WHERE username='".$this->username."'";

			try{
				$this->_db->query($query);
				return true;
			}catch (Exception $e){
				return false;
			}	
	}

//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA LOS ADMIN
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

	/**
	 * GIRO DE EMPRESA AGRUPADOS que se muestran en el listado para filtrar
	 * @param INT $status | Maximo STATUS a buscar
	 * @param INT $maxprivileges | Privilegios MAXIMOS a buscar
	 * @param INT $totalPrivilegios | Total de privilegios que puede tener
	 */
	function getActivities($status=4){
		$this->reset();
		$this->_query="SELECT activity,activity_name FROM vista_user_detalle";
		$tipos=$this->setGroup(array('0'=>"activity"))
					->setMaxstatus($status)
					->setPrivilege(2)
					->setLanguage(App::locale()->getLang())
					->get(false);
		return App::module('Core')->getResource('Arrays')->toAssociative($tipos,'activity','activity_name');
	}

	/**
	 * Obtenemos los diferentes REMINDS que tiene la tabla. Esto para el filtrado
	 */
	function getReminds(){
		$this->reset();
		$this->_query="SELECT reminds AS id, reminds FROM vista_user_reminder";
		$tipos=$this->setGroup(array('0'=>"reminds"))
					->setPrivilege(2)
					->setLanguage(App::locale()->getLang())
					->get(false);
		return App::module('Core')->getResource('Arrays')->toAssociative($tipos,'id',"reminds");
	}

	/**
	 * REMINDER de usuarios
	 * Es un listado de usuarios para elejir manualmente a quienes se les recordara que regresen al sitio
	 */
	function reminder(){
		$this->_query="SELECT * FROM vista_user_reminder";
		return $this->get();
	}

	/**
	 * Sacamos los datos de la entidad
	 */
	function entidadDetail(){
		$this->_query="SELECT * FROM vista_entidad";
		return $this->get();
	}

//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA LOS ROOT
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//


//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS GENERICOS que no validan STATUS, USERNAME
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
	
	
	
	



	
}
