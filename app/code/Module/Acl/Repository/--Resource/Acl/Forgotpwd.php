<?php
class Module_Acl_Repository_Resource_Acl_Forgotpwd extends Core_Model_Repository_Model {

	protected $_db 				= false;

	protected $username			= false;
	protected $id				= false;
	protected $hash				= false;
	protected $used				= false;
	protected $check_expires	= 1;

	protected $grouping			= false;

	protected $order			= false;

	protected $limit			= false;

	public function init() {
		$this->_db=&App::module('Core')->getResourceSingleton('db')->get();
	}

	public function reset() {
		foreach ($this as $var=>$value) {
			if ($var[0]!="_") $this->{$var}=false;
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
					if (isset($args[0])) $this->{$var}=$args[0];
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
							$fields=array("*"),
							$where=array(),
							$group=array(),
							$order=array(),
							$limit=false
						) {

		// Preparamos datos
					if (false!==$this->username) $where[]="username='$this->username'";
					if (false!==$this->id) $where[]="id='$this->id'";
					if (false!==$this->hash) $where[]="hash='$this->hash'";
					if (false!==$this->used) $where[]="used='$this->used'";
					if (false!==$this->check_expires) $where[]="expires>UNIX_TIMESTAMP()";


				// Agrupado
					if ($this->grouping) {}

				// Ordenación
					if ($this->order) $order=$this->order;

				// Limite
					if ($this->limit) $limit=$this->limit;


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
						FROM `acl_forgotpwd`
						$where
						$group
						$order
						$limit
				";
		if ($row) {
			return $this->_db->fetchRow($query);
		} else {
			return $this->_db->query($query)->fetchAll();
		}
	}

	function getRow() {
		return $this->get(true);
	}

/* Actualizaciones ************************/

	function douse($access_ip) {
		if (!$this->hash) return false;
		$query=sprintf("UPDATE acl_forgotpwd set lastlogin=NOW(), access_ip='%s', used='1' where hash='%s'", $access_ip, $this->hash);
		$this->_db->query($query);
		return true;
	}

/* Inserciones ************************/

	/**
	 * Guardamos el HASH para la recuperacion de una contraseña
	 *
	 * @param unknown_type $hash
	 * @param unknown_type $username
	 * @param unknown_type $request_ip
	 * @param unknown_type $expires
	 * @return unknown
	 */
	function create($hash,$username,$request_ip,$expires) {
		$query=sprintf("INSERT INTO acl_forgotpwd set hash='$hash',username='$username',request_ip='$request_ip',request_date=NOW(),expires='$expires'");
		$this->_db->query($query);
		return $this->_db->lastInsertId();
	}
	
}