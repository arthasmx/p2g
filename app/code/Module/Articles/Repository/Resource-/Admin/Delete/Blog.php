<?php
class Module_Blog_Repository_Resource_Admin_Delete_Blog extends Core_Model_Repository_Model {

	protected $_db 				= false;
	protected $id				= false;
	protected $username			= false;	

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

	public function delete() {
		if (!$this->id) {
			$this->_module->exception("Debe especificar el id del caso a eliminar. | Blog\Repository\Resource\Update\Scase.php");
		}
		if ($this->username) {$username=' AND username="'.$this->username.'"';}
		 		
		// Consulta
			$query="DELETE FROM blog WHERE id='".$this->id."'".$username;
		
			try{
				$this->_db->query($query);
				return true;
			}catch (Exception $e){
				return false;
			}
	}


}