<?php
class Module_Core_Repository_Resource_Abstract extends Core_Model_Repository_Resource {

	// Acceso a la base da datos
		protected $_db				= false;
		protected $_query			= false;

		protected $id				=false;
		protected $country			=false;
		protected $state			=false;
		protected $city				=false;
		protected $status			=false;
		protected $language			=false;
		

		protected $where			   = false;
		protected $limit    		   = false;
		protected $order			   = false;
		protected $group			   = false;
		protected $row			   		= false;

	/* PAGINACIÓN ======================= */
		protected $page				   = false;
		protected $items_per_page	   = 10;

	/* FILTRADO ======================= */
		protected $datafilter		   = false; // Se indicarán los filtros a utilizar.

	/* ORDENACIÓN ======================= */
		protected $datasorter		   = false; // Se indicará la ordenación a utilizar.

// ACCESSORS #######################################################################################################################

	public function __construct($id) {
		$this->_db=App::module('Core')->getResource('Db')->get();
	}

    public function asArray() {
        $array=array();
        foreach ($this as $var=>$value) {
            if ($var[0]!="_" && $value!==false) $array[$var]=$value;
        }
        return $array;
    }

	public function reset() {
		foreach ($this as $var=>$value) {
			if ($var[0]!="_") { $this->{$var}=false; }
		}
		return $this;
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

}