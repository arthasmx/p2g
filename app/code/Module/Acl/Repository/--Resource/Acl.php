<?php
class Module_Acl_Repository_Resource_Acl extends Core_Model_Repository_Model {

	protected $_db 				= false;

	protected $username			= false;
	protected $passwd			= false;
	protected $date_created		= false;
	protected $date_created_mes	= false;
/*
	protected $locked			= false;
	protected $active			= false;
	protected $deleted			= false;
	protected $referer			= false;
	protected $priv_frontend	= false;
	protected $priv_root		= false;
	protected $priv_admin		= false;
	protected $priv_operador	= false;
*/
	protected $grouping			= false;

	protected $order			= false;

	protected $limit			= false;

	/* PAGINACIÓN ======================= */
		protected $page					= false;
		protected $items_per_page		= 10;

	/* FILTRADO ======================= */
		protected $datafilter			= false; // Se indicarán los filtros a utilizar.

	/* ORDENACIÓN ======================= */
		protected $datasorter			= false; // Se indicará la ordenación a utilizar.

	public function init() {
		$this->_db=App::module('Core')->getResourceSingleton('db')->get();
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
							//$fields=array("acl.*, user.referer"),
							$fields=array("acl.*, user.*"),
							$where=array(),
							$group=array(),
							$order=array(),
							$limit=false
						) {

		// Preparamos datos
					if (false!==$this->username) $where[]="acl.username='$this->username'";
					if (false!==$this->passwd) $where[]="acl.passwd='$this->passwd'";
/*					
					if (false!==$this->active) $where[]="acl.active='$this->active'";
					if (false!==$this->locked) $where[]="acl.locked='$this->locked'";
					if (false!==$this->deleted) $where[]="acl.deleted='$this->deleted'";
					if (false!==$this->priv_root) $where[]="acl.priv_root='$this->priv_root'";
					if (false!==$this->priv_admin) $where[]="acl.priv_admin='$this->priv_admin'";
					if (false!==$this->priv_frontend) $where[]="acl.priv_frontend='$this->priv_frontend'";
					if (false!==$this->priv_operador) $where[]="acl.priv_operador='$this->priv_operador'";
*/
					if (false!==$this->date_created) $where[]="date_format(acl.created,'%Y-%m-%d')='$this->date_created'";
					if (false!==$this->date_created_mes) $where[]="date_format(acl.created,'%Y-%m')='$this->date_created_mes'";

//					if (false!==$this->referer) $where[]="user.referer='$this->referer'";

				// Agrupado
					if ($this->grouping) {
						foreach ( $this->grouping as $value ):
							switch ($value) {
								case "user": $group[] = "acl.username"; break;
							}
						endforeach;
					}

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
						FROM `acl`
						LEFT JOIN `user` on acl.username=user.username
						$where
						$group
						$order
						$limit
				";
//echo '<pre>'; print_r($query); echo '</pre>';
//exit;
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
//echo '<pre>'; print_r($query); echo '</pre>';
//exit;
			return $result;
		}
	}

	function getRow() {
		return $this->get(true);
	}

	function count() {
		$count=$this->get(true,array("count(*) as total"));
		if ($count) {
			return (int)@$count['total'];
		}
		return 0;
	}

/* Actualizaciones ************************/

	function updateAccess() {
		if (!$this->username) return false;
		$query=sprintf("UPDATE acl set lastlogin=NOW(), access_ip='%s' where username='%s'", App::module('Core')->getResourceSingleton('ip')->get(), $this->username);
		$this->_db->query($query);
		return true;
	}

	function updatePasswd() {
		if (!$this->username) return false;
		if (!$this->passwd) return false;
		$query=sprintf("UPDATE acl set updatedd=NOW(), passwd='%s' where username='%s'", $this->passwd, $this->username);
		$this->_db->query($query);
		return true;
	}

	function activate() {
		if (!$this->username) return false;
		$query=sprintf("UPDATE acl set updated=NOW(), status='1' where username='%s'", $this->username);
		$this->_db->query($query);
		return true;
	}

	function deactivate() {
		if (!$this->username) return false;
		$query=sprintf("UPDATE acl set updated=NOW(), status='0' where username='%s'", $this->username);
		$this->_db->query($query);
		return true;
	}
	
	function lock() {
		if (!$this->username) return false;
		$query=sprintf("UPDATE acl set updated=NOW(), status='2' where username='%s'", $this->username);
		$this->_db->query($query);
		return true;
	}

	function unlock() {
		if (!$this->username) return false;
		$query=sprintf("UPDATE acl set updated=NOW(), status='2' where username='%s'", $this->username);
		$this->_db->query($query);
		return true;
	}
	
/*


	function grant($privileges) {
		if (!$this->username) return false;
		$query=sprintf("UPDATE acl set updated=NOW(), priv_%s='1' where username='%s'", $privileges, $this->username);
		$this->_db->query($query);
		return true;
	}

	function ungrant($privileges) {
		if (!$this->username) return false;
		$query=sprintf("UPDATE acl set updated=NOW(), priv_%s='0' where username='%s'", $privileges, $this->username);
		$this->_db->query($query);
		return true;
	}

	function delete() {
		if (!$this->username) return false;
		$query=sprintf("UPDATE acl set updated=NOW(), deleted='1' where username='%s'", $this->username);
		$this->_db->query($query);
		return true;
	}

	function undelete() {
		if (!$this->username) return false;
		$query=sprintf("UPDATE acl set updated=NOW(), deleted='0' where username='%s'", $this->username);
		$this->_db->query($query);
		return true;
	}
*/
/* Inserciones ************************/

	function create($user,$passwd,$email) {
		$query='INSERT INTO acl
				SET
					username="'.$user.'",
					passwd="'.md5($passwd).'",
					status=0,
					email="'.$email.'",
					created=NOW(),
					updated=NOW()';
		$this->_db->query($query);
		return true;
	}

	function changePasswd($user,$passwd) {
		$query='UPDATE acl
				SET
					passwd="'.md5($passwd).'",
					updated=NOW()
				WHERE
					username="'.$user.'"
		';
		$this->_db->query($query);
		return true;
	}

	function changeEmail($user,$email) {
		$query='UPDATE acl
				SET
					email="'.$email.'",
					updated=NOW()
				WHERE
					username="'.$user.'"
		';
		$this->_db->query($query);
		return true;
	}

}