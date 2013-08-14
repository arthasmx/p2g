<?php
class Module_Acl_Repository_Resource_Privileges extends Core_Model_Repository_Model {

	protected $_db 				= false;
	
	protected $grouping			= false;
	protected $order			= false;
	protected $limit			= false;
	
	protected $username			= false;
	
	/* PAGINACIÓN ======================= */
		protected $page					= false;
		protected $items_per_page		= 10;

	/* FILTRADO ======================= */
		protected $datafilter			= false; // Se indicarán los filtros a utilizar.

	/* ORDENACIÓN ======================= */
		protected $datasorter			= false; // Se indicará la ordenación a utilizar.

	public function init() {
		$this->_db=App::module('Core')->getResource('db')->get();
	}

	public function reset() {
		foreach ($this as $var=>$value) {
			if ($var[0]!="_") {
				switch($var) {
					case "page":
					case "items_per_page":
					case "datafilter":
					case "datasorter":
						break;
					default:
						$this->{$var}=false;
				}
			}
		}
		return $this;
	}

/* ACCESSORS ***************************************************************************************************/

	public function __call($function, $args) {
		// Comprueba SET
			preg_match("/^set([a-zA-Z\_]+)$/",$function,$matches);
			if (isset($matches[1])) {
				$var=strtolower($matches[1]);
				if (isset($this->{$var}) || @$this->{$var}===false) {
					if (is_string($args)) {
						$this->{$var}=mysql_escape_string($args[0]);
					} else {
						$this->{$var}=$args[0];
					}
				}
				return $this;
			}
		// Comprueba GET
			preg_match("/^get([a-zA-Z\_]+)$/",$function,$matches);
			if (isset($matches[1])) {
				$var=strtolower($matches[1]);
				if (isset($this->{$var})) {
					return $this->{$var};
				}
				return false;
			}
	}

/* MAIN ***************************************************************************************************/



/* Consultas ************************/

	public function get(
							$row=false,
							$fields=array("p.*,PDesc.title,PDesc.description"),
							$where=array(),
							$group=array(),
							$order=array(),
							$limit=false
						) {

		// Preparamos datos
					if (false!==$this->username) $where[]="up.username='$this->username'";

				// Ordenación
					if ($this->order) $order=$this->order;

				// Limite
					if ($this->limit) $limit=$this->limit;

		// DATAFILTER ===========================
			// Cargamos todos los campos activos y añadimos sus condiciones al where para la consulta
			if ( $this->datafilter && $this->datafilter->isActive() ) {
				require_once('Xplora/Datafilter/Sql.php');
				foreach ($this->datafilter->getFields() as $id=>$field) {
					if (true===$field->getActive()) {
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

		// Generamos cláusula del SQL
			// FIELDS
				$fields=implode(", ",$fields);
			// WHERE
				if (count($where)) {
					$where="WHERE ".implode(" AND ",$where);
				} else $where=false;
			// GROUP BY
				if (count($group)) {
					$group="GROUP BY ".implode(", ",$group);
				} else $group=false;
			// ORDER BY
				if (count($order)) {
					foreach($order as $key=>$value) {
						$order[$key]=$key." ".$value;
					}
					$order="ORDER BY ".implode(", ",$order);
				} else $order=false;
			// LIMIT
				if ($limit) {
					$limit = "LIMIT 0,".(int)$limit;
				} else $limit=false;
			// SQL
				$query="
					SELECT $fields
						FROM `user_privileges` AS up
						JOIN `privileges` AS p ON p.privilege=up.privilege
						JOIN `privileges_".App::locale()->getLang()."` AS PDesc ON PDesc.privilege=p.privilege
						$where
						$group
						$order
						$limit
				";

		if ($row) {
			return $this->_db->fetchRow($query);
		} else {

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
									->setQuery($query)
									->paginate();
					// PAGINATOR ===========================
				} else {
					$result=$this->_db->query($query)->fetchAll();
				}
//echo '<pre>'; print_r($query); echo '</pre>';exit;
			return $result;
		}
	}

	function getRow() {
		return $this->get(true);
	}

	/**
	 * Sacamos los privilegios que tiene el sitio
	 */
	function getSitePrivileges(){
		$query="SELECT p.*, PDesc.*
				FROM `privileges` AS p
				JOIN `privileges_".App::locale()->getLang()."` AS PDesc ON PDesc.privilege=p.privilege
				ORDER BY p.privilege ASC";
		return $this->_db->query($query)->fetchAll();
	}

	/**
	 * Insertamos los privilegios designados para X user
	 */
	function saveUserPrivileges($privilegios=false){
		if(!$privilegios || !$this->username) return false;
		foreach ($privilegios AS $privilegio){
			$query = "INSERT INTO user_privileges (privilege,username) VALUES ('".mysql_escape_string($privilegio)."','".mysql_escape_string($this->username)."')";
			$this->_db->query($query);
			$query=false;
		}
		return true;
	}
	
	/**
	 * Eliminamos los privilegios de X user
	 */
	function deleteByUser(){
		if(!$this->username) return false;
		$query = "DELETE FROM user_privileges WHERE username='".mysql_escape_string($this->username)."'";
		$this->_db->query($query);
		return $this;
	}
	
/* Actualizaciones ************************/

}