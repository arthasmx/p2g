<?php

class Module_Blog_Repository_Resource_Admin_Blog extends Core_Model_Repository_Model {

	protected $_db 				= false;

	protected $id						= false;
	protected $dpublish					= false;
	protected $archivo_sufijo			= false;
	protected $archivo_tipo				= false;
	protected $fecha					= false;

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
					$this->{$var}=$args[0];
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

	public function get(
							$row=false,
							array $fields=array("*"),
							array $where=array(),
							array $group=array(),
							array $order=array(),
							$limit=false
						) {

		// Preparamos datos
				// Wheres
					if ($this->id!==false) $where[]="id='".mysql_escape_string($this->id)."'";
					if ($this->dpublish!==false) $where[]="date_publish <=NOW()";
					if ($this->archivo_sufijo!==false) $where[]="archivo_sufijo LIKE '$this->archivo_tipo'";
					if ($this->archivo_tipo!==false) $where[]="archivo_tipo='$this->archivo_tipo'";
					if ($this->fecha!==false) $where[]="fecha='$this->fecha'";

				// Agrupado
					if ($this->grouping) {
						foreach ( $this->grouping as $value ):
							switch ($value) {
								//case "referer": $group[] = "user.referer"; break;
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
					//if (true===$field->getActive() && true===Xplora_Datafilter_Sql::getFieldCondition($field)) {
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
						FROM blog
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

			return $result;
		}
	}

	function getRow() {
		return $this->get(true);
	}

/* INSERTS ***************/

	function getById($id) {
		return $this->_db->query('SELECT * FROM blog WHERE id="'.mysql_escape_string($id).'" AND date_publish<=NOW() LIMIT 0,1')->fetchAll();
	}

}