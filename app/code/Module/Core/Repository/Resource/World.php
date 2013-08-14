<?php
require_once('Module/Core/Repository/Resource/Abstract.php');
class Module_Core_Repository_Resource_World extends Module_Core_Repository_Resource_Abstract {

	/**
	 * Filtros, ordena, limites
	 */
	function setWhere(){
		//Propiedades
			if ($this->country!==false)  	$where[] = "country = '$this->country'";
			if ($this->id!==false)  		$where[] = "id= '$this->id'";
			if ($this->language!==false)  	$where[] = "language = '$this->language'";
			if ($this->status!==false)  	$where[] = "status = '$this->status'";
			
//			if ($this->city!=false)			$where[] = "id = '$this->city'";
			
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
							FROM vista_world_countries
							$this->where
							$this->group
							$this->order
							$this->limit";
		}else{
			$this->_query .= " ". $this->where ." ". $this->group ." ". $this->order ." ". $this->limit;
		}
//echo "<pre>"; print_r($this->_query); echo "</pre>";
//exit;
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
	 * Detalle de 1 pais
	 *
	 * @param unknown_type $id
	 * @return unknown
	 */
	public function getCountry($id=false){
		//return $this->reset()->setLanguage(App::locale()->getLang())->setCountry($id)->setRow(true)->get(true);
		$this->reset();
		$this->_query="SELECT country FROM vista_world_countries";
		return $this->setLanguage(App::locale()->getLang())->setId(mysql_escape_string($id))->setRow(true)->get();
	}

	/**
	 * Detalle de 1 estado
	 *
	 * @param unknown_type $id
	 * @return unknown
	 */
	public function getState($id=false){
		$this->reset();
		$this->_query="SELECT state FROM vista_world_states";
		return $this->setLanguage(App::locale()->getLang())->setId(mysql_escape_string($id))->setRow(true)->get();
	}

/**
 * Obtenemos los paises disponibles
 * @return Array
 */
public function countries($status=1){
	$resultado[0]=array('id'=>0,'country'=>App::xlat('FORM_LABEL_choose_option'),'status'=>'0' );		
	$mysql_resp=$this->setStatus($status)->setLanguage(App::locale()->getLang())->get(true);
	return array_merge($resultado,$mysql_resp);
}

/**
 * Obtenemos los estados q le corresponden a 1 pais
 * @return Array
 */
public function states(){
	if (!$this->country) return false;
	$this->_query="SELECT * FROM vista_world_states"; // WHERE country_id='".mysql_escape_string($this->country)."'";
	return $this->setLanguage(App::locale()->getLang())->get();
}

	/**
	 * Obtenemos las ciudades q le corresponden a 1 estado
	 * @return Array
	 */
	public function cities(){
		if (!$this->state) return false;
		return $cities;
	}

	/**
	 * Obtenemos las calles, colonias, chiqueros que pertenencen a una ciudad
	 * @return Array
	 */
	public function street(){
		if (!$this->city) return false;
		return $street;
	}

	/**
	 * Regresa los estados para un select
	 */
	function statesJson($pais=false) {
		if ( $pais ) {
			$estados= $this->setCountry($pais)->states();
		}
		// Antes, escribimos la clasica ELIJA UNA OPCION para el jSon, esto en el indice 0
		$states[0]=array(
			"a"=>'',
			"b"=>App::xlat('FORM_LABEL_choose_option'),
		);

		// Formateamos el array para que no muestre toda la información y devolver los parametros necesarios para el select
			foreach ((array)$estados as $key=>$estado) {
				$key++;
				$states[$key]=array(
					"a"=>$estado['id'],
					"b"=>$estado['state'],
				);
			}
		// Codificamos a un objeto Json
			return $this->_json()->encode($states);
	}

	protected function _json() {
//		$this->getResponse()->setHeader('Content-Type', 'text/javascript');
		require_once("Zend/Json.php");
		return new Zend_Json;
	}	

}