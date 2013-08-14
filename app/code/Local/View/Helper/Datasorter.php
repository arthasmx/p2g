<?php

class Local_View_Helper_Datasorter {

	protected $datasorter		= false;

	protected $sort				= false;

	protected $title			= "list_column_sort";

	public $view;

    public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }

    public function datasorter() {
	// CSS
			App::header()->addLink(App::skin('/css/dataset.css'),array('rel'=>'stylesheet','type'=>'text/css'));
		return $this;
    }

    protected function getSort() {
	if (false===$this->sort) {
			$this->sort=$this->datasorter->getSort();
	}
	return $this->sort;
    }

    protected function getUrl() {
		return $this->datasorter->getUrl();
	}

    /**
     * Getter y Setter
     *
     * @param unknown_type $function
     * @param unknown_type $args
     * @return unknown
     */
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

	public function renderField($id) {
		$sort=$this->getSort(); // Array con campos que estan ordenando el listado
		$field=$this->datasorter->field($id);
		if ($field->getActive()) {
			// Debemos determinar si es ascendente o descendente para mostarr el botón correspondiente y el link contrario
				$url = $this->getUrl()."/".App::xlat('route_sort_field')."/".$field->getId();
				if ($field->getSort_type()==='ASC') {
					$url.="/".App::xlat('route_sort_direction')."/DESC";
				} else {
					$url.="/".App::xlat('route_sort_direction')."/ASC";
				}
			printf(
					"<a href='%s' class='datasorter datasorter-active datasorter-type-%s' title='%s'><span>%s</span></a>",
					$url,
					strtolower($field->getSort_type()),
					App::xlat($this->title),
					$field->getSort_type()
			);
		} else {
			printf(
					"<a href='%s' class='datasorter datasorter-inactive' title='%s'><span>%s</span></a>",
					$this->getUrl()."/".App::xlat('route_sort_field')."/".$field->getId(), // Aqui no añadimos el tipo de ordenación, se usará el por defecto al hacer click en el link
					App::xlat($this->title),
					"SORT"
			);
		}
		//
	}


}
?>