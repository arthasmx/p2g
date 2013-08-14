<?php
class Module_User_Repository_Resource_Abstract extends Core_Model_Repository_Resource {

	// Acceso a la base da datos
		protected $_db				   = false;
		protected $id				   = false;
		protected $_filter			   = false;
		protected $language			   = false;

		protected $username         = false;
		protected $password	= false;
		protected $nombre = false;
		protected $email = false;
		protected $telefono = false;
		protected $extension = false;
		protected $fax = false;
		protected $web = false;
		protected $direccion = false;
		protected $colonia = false;
		protected $cp = false;
		protected $ciudad = false;
		protected $estado = false;
		protected $pais = false;
		protected $admin = false;
		protected $usuario = false;
		protected $pais_es = false;
		protected $pais_en = false;
		protected $clue = false;
		protected $estado_es = false;
		protected $estado_en = false;
		protected $clientes = false;

		protected $not_admin    = false;
		protected $not_user	   	= false;

		protected $eliminado    = false;
		protected $banneado	   	= false;
        protected $activo 		= false;

        protected $fecha_creado        = false;
        protected $fecha_actualizado   = false;
        protected $ultimo_ingreso      = false;
        protected $usuario_actualizado = false;

        protected $row				   = false;
		protected $where			   = false;
		protected $limit    		   = false;
		protected $order			   = false;
		protected $group			   = false;
		protected $_query			   = false;

	/* PAGINACIÓN ======================= */
		protected $page				   = false;
		protected $items_per_page	   = 10;

	/* FILTRADO ======================= */
		protected $datafilter		   = false; // Se indicarán los filtros a utilizar.

	/* ORDENACIÓN ======================= */
		protected $datasorter		   = false; // Se indicará la ordenación a utilizar.

// ACCESSORS #######################################################################################################################

	public function __construct($id) {
		$this->_db=App::module('Core')->getResourceSingleton('Db')->get();
		$this->_filter=App::module('Core')->getResourceSingleton('Filter');
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