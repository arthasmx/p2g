<?php
require_once 'Module/Acl/Repository/Resource/Abstract.php';
class Module_Acl_Repository_Resource_Privileges extends Module_Acl_Repository_Resource_Abstract {

	/**
	 * Filtros, ordena, limites
	 */
	function setWhere(){
		//Propiedades
			if ($this->id!==false)  				$where[] = "id= '$this->id'";
			if ($this->username!==false)  			$where[] = "username = '$this->username'";
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
							FROM view_user_privileges
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
	
	function getRow() {
		return $this->get(true);
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

	/**
	 * Sacamos los privilegios del usuario en turno(Del que este logeado)
	 *
	 * @param unknown_type $user
	 * @return unknown
	 */
	function getLoggedUserPrivileges($privs=false){
		if(!$privs) return false;
		$privileges =0;
		foreach ($privs as $privilege) {
			$privileges += $privilege['privilege']; 
		}
		return $privileges;
	}

	/**
	 * Sacamos los privilegios que tiene el sitio
	 */
	function getSitePrivileges(){
		$query = "	SELECT
						p.privilege
						,p.area
						,p.picture
						,pl.name
						,pl.description
						,lang.prefix
					FROM
						privileges AS p
						JOIN privileges_lang AS pl ON pl.privilege_id = p.privilege
						JOIN languages AS lang ON lang.id = pl.lang_id
					WHERE
						lang.prefix = '".App::locale()->getLang()."'";		
		return $this->_db->query($query)->fetchAll();
	}

}