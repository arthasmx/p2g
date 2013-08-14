<?php

require_once "Local/View/Helper/Datasorter.php";
class Local_View_Helper_Ajaxdatasorter extends Local_View_Helper_Datasorter {

	var $jscallback=null;

	public function setJscallback($jscallback) {
		$this->jscallback=str_replace("'",'"',$jscallback);
		return $this;
	}

	public function ajaxdatasorter() {
	// CSS
			App::header()->addLink(App::skin('/css/dataset.css'),array('rel'=>'stylesheet','type'=>'text/css'));
		return $this;
    }

	public function renderField($id) {
		$sort=$this->getSort(); // Array con campos que estan ordenando el listado
		$field=$this->datasorter->field($id);
		if ($field->getActive()) {
			// Debemos determinar si es ascendente o descendente para mostarr el botón correspondiente y el link contrario
				$url = $this->getUrl()."/".App::xlat('route_sort_field')."/".$field->getId();
				if ($field->getSort_type()==='ASC') {
					$url.= "/".App::xlat('route_sort_direction')."/DESC";
				} else {
					$url.="/".App::xlat('route_sort_direction')."/ASC";
				}
			printf(
					"<a href='' onclick='%s' class='datasorter datasorter-active datasorter-type-%s' title='%s'><span>%s</span></a>",
					str_replace( "url", $url , $this->jscallback),
					strtolower($field->getSort_type()),
					App::xlat($this->title),
					$field->getSort_type()
			);
		} else {
			printf(
					"<a href='' onclick='%s' class='datasorter datasorter-inactive' title='%s'><span>%s</span></a>",
					str_replace( "url", $this->getUrl()."/".App::xlat('route_sort_field')."/".$field->getId() , $this->jscallback), // Aqui no añadimos el tipo de ordenación, se usará el por defecto al hacer click en el link
					App::xlat($this->title),
					"SORT"
			);
		}
		//
	}


}
?>