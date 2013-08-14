<?php
class Module_Blog_Repository_Resource_Admin_Update_Blog extends Core_Model_Repository_Model {

	protected $_db 				= false;
	protected $id				= false;
	protected $date_create		= false;
	protected $title			= false;
	protected $date_updated		= false;
	protected $date_publish		= false;
	protected $logo				= false;
	protected $body				= false;
	protected $username			= false;
	protected $seo				= false;

	public function init() {
		$this->_db=&App::module('Core')->getResourceSingleton('db')->get();
	}

	public function reset() {
		foreach ($this as $var=>$value) {
			if ($var[0]!="_") $var=false;
		}
		return $this;
	}

	public function asArray() {
		$array=array();
		foreach ($this as $var=>$value) {
			if ($var[0]!="_" && $value!==false) $array[$var]=$value;
		}
		return $array;
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

	public function update() {
		if (!$this->id) {
			$this->_module->exception("Debe especificar el id del articulo a modificar. | Blog\Repository\Resource\Update\Blog.php");
		}
		$request=$this->asArray();
		// Parámetros
			$params=array();

			foreach($this->asArray() as $key=>$value) $params[]=sprintf("%s='%s'",$key,mysql_escape_string((string)$value));
			if (!count($params)>1) { // El primero debe ser el id, así que deben de especificar más de 1
				$this->_module->exception("Debe especificar los atributos del tramite a modificar. | Blog\Repository\Resource\Update\Blog.php");
			}
			if ($this->username) {$username=' AND username="'.$this->username.'"';}

		// Consulta
			$query="UPDATE blog SET ".implode(",".PHP_EOL,$params).",date_updated=now() WHERE id='".$this->id."'".$username;

			try{
				$this->_db->query($query);              
				return true;
			}catch (Exception $e){
				return false;
			}
	}


}