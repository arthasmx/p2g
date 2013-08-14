<?php
require_once 'Module/User/Repository/Resource/Abstract.php';
class Module_User_Repository_Resource_User extends Module_User_Repository_Resource_Abstract {

/* MAIN ***************************************************************************************************/
        protected $status		= false;
        protected $maxstatus= false;
        protected $maxprivilege= false;
        protected $total_privilegios = false;
        protected $privilege= false;
        protected $avatar= false;

	/**
	 * Filtros, ordena, limites
	 */
	function setWhere(){
		//Propiedades
			if ($this->username!==false)  			$where[] = "username = '$this->username'";
			if ($this->email) 						$where[] = "email='$this->email'";
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

	/**
	 * Obtenemos todos los usuarios sin importar al usuario del que pertenezcan (admin)
	 * Modificamos este metodo simplemente cambiando la propiedad $this->_query.
	 */
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


//echo '<pre>'; print_r($this->_query); echo '</pre>';
//exit;

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


	
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA LOS USUARIOS
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

	/**
	 * Creamos al usuario en el sitio
	 *
	 * @param unknown_type $user
	 * @param unknown_type $nombre
	 * @param unknown_type $email
	 * @param unknown_type $telefono
	 * @param unknown_type $direccion
	 * @param unknown_type $colonia
	 * @param unknown_type $cp
	 * @param unknown_type $ciudad
	 * @param unknown_type $estado
	 * @param unknown_type $pais
	 * @param unknown_type $web
	 * @param unknown_type $giro
	 * @return unknown
	 */
	function create($user,$nombre,$email,$telefono,$direccion,$colonia,$cp,$ciudad,$estado,$pais,$web=false,$giro=0) {
		$query='INSERT INTO user
				SET
					username="'.$user.'",
					name="'.$nombre.'",
					email="'.$email.'",
					phone="'.$telefono.'",
					adress="'.$direccion.'",
					section="'.$colonia.'",
					zip="'.$cp.'",
					activity="'.$giro.'",
					web="'.$web.'",
					city="'.$ciudad.'",
					country="'.$pais.'",
					state="'.$estado.'"';
		$this->_db->query($query);
		return true;
	}

	/**
	 * Actualizamos datos del usuario
	 *
	 * @param  $nombre
	 * @param  $email
	 * @param  $telefono
	 * @param  $extension
	 * @param  $fax
	 * @param  $web
	 * @param  $direccion
	 * @param  $colonia
	 * @param  $cp
	 * @param  $ciudad
	 * @param  $estado
	 * @param  $pais
	 * @return unknown
	 */
	function update($nombre=false,$email=false,$telefono=false,$extension=false,$fax=false,$web=false,$direccion=false,$colonia=false,$cp=false,$ciudad=false,$estado=false,$pais=false) {
		if (!$this->username) $this->_module->exception("ERROR_NO_USER_SPECIFIED");

		$fields=array();
			if ($nombre!==false) $fields[]='name="'.$nombre.'"';
			if ($email!==false) $fields[]='email="'.$email.'"';
			if ($telefono!==false) $fields[]='phone="'.$telefono.'"';
			if ($extension!==false) $fields[]='ext="'.$extension.'"';
			if ($fax!==false) $fields[]='fax="'.$fax.'"';
			if ($web!==false) $fields[]='web="'.$web.'"';
			if ($direccion!==false) $fields[]='adress="'.$direccion.'"';
			if ($colonia!==false) $fields[]='section="'.$colonia.'"';
			if ($cp!==false) $fields[]='zip="'.$cp.'"';
			if ($ciudad!==false) $fields[]='city="'.$ciudad.'"';
			if ($estado!==false) $fields[]='state="'.$estado.'"';
			if ($pais!==false) $fields[]='country="'.$pais.'"';

		if (count($fields)) {
			$query='UPDATE user
					SET
						'.implode(",",$fields).'
					WHERE
						username="'.$this->username.'"
			';

			$this->_db->query($query);
		}
		return true;
	}
	
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA LOS ADMIN
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

	/**
	 * Obtenemos los diferentes REMINDS que tiene la tabla. Esto para el filtrado
	 */
	function getReminds(){
		$this->reset();
		$this->_query="SELECT reminds AS id, reminds FROM vista_user_reminder";
		$tipos=$this->setGroup(array('0'=>"reminds"))
					->setPrivilege(1)
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

	
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA LOS ROOT
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//


//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS GENERICOS que no validan STATUS, USERNAME
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

	/**
	 * Guardamos la referencia del avatar en tabla
	 */
	function avatar(){
		$query='UPDATE user SET avatar="'.mysql_escape_string($this->avatar).'" WHERE username="'.mysql_escape_string($this->username).'"';
		$this->_db->query($query);
	}
	
	// Como el EMAIL es utilizado como USERNAME para esta aplicacion, debemos validar si fue modificado
	// Si fue modificado, debemos actualizar TODOS los username de la base de datos que correspondan al anterior
	function updateUsername($actual_username, $nuevo_username, $tablas, $fields){
		if(!$actual_username || !$nuevo_username || !$tablas || !$fields) return false;
		$campos='';
		$query='';

		// Recorremos los campos q se requieren actualizar. No todas las tablas los tienen, pero como es un UPDATE, al parecer no da error
		foreach ($fields as $field) {
			$campos.= $field . "='".mysql_escape_string($nuevo_username)."'," ;
		}
		$campos=rtrim($campos,',');

		// Recorremos las tablas que se actualizaran
		foreach ($tablas as $tabla) {
			$query='UPDATE ' . $tabla . ' SET ' . $campos . ' WHERE username="'.$actual_username.'"';
			$this->_db->query($query);
		}

	}

	/**
	 * Verificamos la existencia del usuario
	 * 
	 * @param unknown_type $username
	 */
	function userExist(){
		$this->_query="SELECT COUNT(*) AS resp FROM user";
		$x= $this->setRow(true)->get();
		return $x['resp'];
	}
	
	/**
	 * STATUS AGRUPADOS de usuarios que se muestran en el listado
	 * @param INT $status | Maximo STATUS a buscar
	 * @param INT $maxprivileges | Privilegios MAXIMOS a buscar
	 * @param INT $totalPrivilegios | Total de privilegios que puede tener
	 */
	function getStatus($status=4,$privileges=1,$totalPrivilegios=1){
		$this->reset();
		$this->_query="SELECT status,status_desc AS name FROM vista_user_detalle";
		$tipos=$this->setGroup(array('0'=>"status"))
					->setMaxstatus($status)
					->setPrivilege($privileges)
					->setTotal_privilegios($totalPrivilegios)
					->setLanguage(App::locale()->getLang())
					->get(false);
		return App::module('Core')->getResource('Arrays')->toAssociative($tipos,'status','name');
	}

	/**
	 * PRIVILEGIOS AGRUPADOS de usuarios que se muestran en el listado
	 * @param INT $status | Maximo STATUS a buscar
	 * @param INT $maxprivileges | Privilegios MAXIMOS a buscar
	 * @param INT $totalPrivilegios | Total de privilegios que puede tener
	 */
	function getPrivilegios($status=4,$maxprivileges=1,$totalPrivilegios=1){
		$this->reset();
		$lang=App::locale()->getLang();
		$this->_query="SELECT privilegios, p.privilege,p.title FROM vista_user_detalle JOIN privileges_$lang AS p ON p.privilege=privilegios";
		$tipos=$this->setGroup(array('0'=>"privileges"))
					->setMaxstatus($status)
					->setMaxprivilege($maxprivileges)
					->setTotal_privilegios($totalPrivilegios)
					->setLanguage(App::locale()->getLang())
					->get(false);
		return App::module('Core')->getResource('Arrays')->toAssociative($tipos,'privilege','title');
	}
	
	/**
	 * ESTADOS AGRUPADOS de usuarios que se muestran en el listado
	 * @param INT $status | Maximo STATUS a buscar
	 * @param INT $maxprivileges | Privilegios MAXIMOS a buscar
	 * @param INT $totalPrivilegios | Total de privilegios que puede tener
	 */
	function getState($status=4,$maxprivileges=1,$totalPrivilegios=1){
		$this->reset();
		$this->_query="SELECT state,state_name FROM vista_user_detalle";
		$tipos=$this->setGroup(array('0'=>"state"))
					->setMaxstatus($status)
					->setMaxprivilege($maxprivileges)
					->setTotal_privilegios($totalPrivilegios)
					->setLanguage(App::locale()->getLang())
					->get(false);
		return App::module('Core')->getResource('Arrays')->toAssociative($tipos,'state',"state_name");
	}

	/**
	 * PAISES AGRUPADOS de usuarios que se muestran en el listado
	 * @param INT $status | Maximo STATUS a buscar
	 * @param INT $maxprivileges | Privilegios MAXIMOS a buscar
	 * @param INT $totalPrivilegios | Total de privilegios que puede tener
	 */
	function getCountry($status=4,$maxprivileges=1,$totalPrivilegios=1){
		$this->reset();
		$this->_query="SELECT country,country_name FROM vista_user_detalle";
		$tipos=$this->setGroup(array('0'=>"country"))
					->setMaxstatus($status)
					->setMaxprivilege($maxprivileges)
					->setTotal_privilegios($totalPrivilegios)
					->setLanguage(App::locale()->getLang())
					->get(false);
		return App::module('Core')->getResource('Arrays')->toAssociative($tipos,'country',"country_name");
	}
	
	/**
	 * Sacamos los ultimos usuarios registrados en el sitio
	 */
	function getLastUsers($limit=3){
		$this->reset();
		$language = App::module('Core')->getResource('Languages')->sessionLanguageByPrefix( App::locale()->getLang() );
		$this->_query = "	SELECT
								a.username
								,a.created
								,a.status
								,u.name
								,u.email
								,u.activity AS activity_id
								,act.name AS activity
								,vup.area
								,vup.title								
							FROM
								acl AS a
								JOIN user AS u ON u.username = a.username
								LEFT JOIN activities_lang AS act ON act.activity_id = u.activity AND act.lang_id = ".$language['id']."
								LEFT JOIN languages AS lang ON lang.id = act.lang_id AND lang.status = 1
								LEFT JOIN view_user_privileges AS vup ON vup.username = a.username AND vup.lang_id = lang.id

							ORDER BY a.created DESC 
							LIMIT $limit";
		return $this->_db->query( $this->_query )->fetchAll();
	}
	
}
