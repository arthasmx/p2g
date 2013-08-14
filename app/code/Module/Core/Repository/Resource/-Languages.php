<?php
require_once('Module/Core/Repository/Resource/Abstract.php');
class Module_Core_Repository_Resource_Languages extends Module_Core_Repository_Resource_Abstract {

	protected $name = false;
	protected $prefix = false;
	
	function setWhere(){
		if ($this->id!==false)  	$where[] = "id= '$this->id'";
		if ($this->name!==false)  	$where[] = "name= '$this->name'";
		if ($this->prefix!==false)  $where[] = "prefix= '$this->prefix'";
		if ($this->status!==false)  $where[] = "status = '$this->status'";

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
							FROM languages
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
	 * Regresa los idiomas del sitio
	 */
	function languages() {
		return $this->reset()->setStatus(1)->get(true);
	}
	
	/**
	 * Regresa los idiomas para un select en Json
	 */
	function languagesJson() {
		// Antes, escribimos la clasica ELIJA UNA OPCION para el jSon, esto en el indice 0
		$idiomas[0]=array(
			"a"=>'',
			"b"=>App::xlat('FORM_LABEL_choose_option'),
		);

		// Sacamos los idiomas disponibles en el sitio
			$siteLangs = $this->get(true);

		// Formateamos el array para que no muestre toda la información y devolver los parametros necesarios para el select
			foreach ((array)$siteLangs as $key=>$sl) {
				$key++;
				$idiomas[$key]=array(
					"a"=>$sl['id'],
					"b"=>$sl['name'],
				);
			}

		// Codificamos a un objeto Json
			return $this->_json()->encode($idiomas);
	}

	/**
	 * Detalle de un idioma por su LANG_ID
	 */
	function detailById($lang=false) {
		if(!$lang) return false;
		return $this->reset()->setId($lang)->get(true);
	}

	/**
	 * Detalle de un idioma por su LANG_ID
	 */
	function detailByPrefix($prefix=false) {
		if(!$prefix) return false;
		return $this->reset()->setPrefix($prefix)->get(true);
	}

	protected function _json() {
		require_once("Zend/Json.php");
		return new Zend_Json;
	}	
	
/**
 * Sacando los datos del idioma por medio de la sesion activa
 * Funciona solamente cuando estamos logeados
 */

	/**
	 * Sacamos los idiomas del sitio, desde la session
	 * OJO: Este funciona solo para usuarios logeados
	 */
	function sessionLanguages($index="id"){
		$acl=App::module('Acl')->getModel('acl');
		if(!isset($acl->data['languages']) || (sizeof($acl->data['languages'])<1) ) return false;
		return App::module('Core')->getResource('Arrays')->toCombo($acl->data['languages'],$index,'name');
	}

	/**
	 * Sacamos datos del idioma en la sesion, por medio de su prefijo
	 *
	 * @param unknown_type $prefix
	 * @return unknown
	 */
	function sessionLanguageByPrefix($prefix=false){
		$acl=App::module('Acl')->getModel('acl');
		if(!$prefix || !isset($acl->data['languages']) || (sizeof($acl->data['languages'])<1) ) return false;
		if( array_key_exists($prefix,$acl->data['languages'] ) ){
			return $acl->data['languages'][$prefix];
		}
		return FALSE;
	}

	/**
	 * Sacamos datos del idioma en la session por medio de su LANG_ID
	 *
	 * @param unknown_type $prefix
	 * @return unknown
	 */
	function sessionLanguageById($id=false){
		$acl=App::module('Acl')->getModel('acl');
		if(!$id || !isset($acl->data['languages']) || (sizeof($acl->data['languages'])<1) ) return false;
		foreach($acl->data['languages'] AS $key=>$lang ){
			if($lang['id']==$id){
				return $acl->data['languages'][$key];
			}
		}
		return FALSE;
	}

}