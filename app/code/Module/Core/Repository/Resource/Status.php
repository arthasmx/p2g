<?php
require_once('Module/Core/Repository/Resource/Abstract.php');
class Module_Core_Repository_Resource_Status extends Module_Core_Repository_Resource_Abstract {

	protected $name = false;
	protected $maxid = false;

	function setWhere(){
		if ($this->id!==false)  	$where[] = "id= '$this->id'";
		if ($this->maxid!==false)  	$where[] = "id<= '$this->maxid'";
		if ($this->name!==false)  	$where[] = "name= '$this->name'";
		

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
			$this->_query="
						SELECT *
							FROM status
							$this->where
							$this->group
							$this->order
							$this->limit";
		}else{
			$this->_query .= " ". $this->where ." ". $this->group ." ". $this->order ." ". $this->limit;
		}

		if (!$this->_query) return false;

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

	
	/**
	 * Regresa los estados habilitados para la aplicacion
	 */
	function status($maxid=4) {
		return $this->reset()->setMaxid($maxid)->get(true);
	}

	/**
	 * Regresa los estados para un combo
	 */
	function statusCombo($maxid=4) {
		$tmp=$this->status($maxid);
		return App::module('Core')->getResource('Arrays')->toCombo($tmp);
	}
	
	/**
	 * Regresa los status en Json
	 */
	function languagesJson() {
		// Antes, escribimos la clasica ELIJA UNA OPCION para el jSon, esto en el indice 0
		$status[0]=array(
			"a"=>'',
			"b"=>App::xlat('FORM_LABEL_choose_option'),
		);

		// Sacamos los idiomas disponibles en el sitio
			$siteStat = $this->status();

		// Formateamos el array para que no muestre toda la información y devolver los parametros necesarios para el select
			foreach ((array)$siteStat as $key=>$sl) {
				$key++;
				$status[$key]=array(
					"a"=>$sl['id'],
					"b"=>$sl['name'],
				);
			}

		// Codificamos a un objeto Json
			return $this->_json()->encode($status);
	}

	/**
	 * Detalle de un status por su ID
	 */
	function detail($id=false) {
		if(!$id) return false;
		return $this->reset()->setId($id)->get(true);
	}

	protected function _json() {
		require_once("Zend/Json.php");
		return new Zend_Json;
	}	

}