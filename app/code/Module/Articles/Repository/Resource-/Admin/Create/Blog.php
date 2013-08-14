<?php
class Module_Blog_Repository_Resource_Admin_Create_Blog extends Core_Model_Repository_Model {

	protected $_db 				= false;
	protected $id				= false;
	protected $date_created		= false;
	protected $date_updated		= false;
	protected $date_publish		= false;
	protected $logo				= false;
	protected $article			= false;
	protected $username			= false;
	protected $title			= false;		
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

	public function create() {

		$request=$this->asArray();
		// Parámetros
			$params=array();
			foreach($this->asArray() as $key=>$value) $params[]=sprintf("%s='%s'",$key,mysql_escape_string((string)$value));
			if (!count($params)) {
				$this->_module->exception("Debe especificar los atributos del tramite a crear. | Blog\Repository\Resource\Create\Scase.php");
			}
		// Consulta
			$query="INSERT INTO blog SET ".implode(",".PHP_EOL,$params);
			
			try{
echo '<pre>'; print_r($query); echo '</pre>'; 
exit;
                
				$this->_db->query($query);
				return true;
			}catch (Exception $e){
echo '<pre>'; print_r($e); echo '</pre>'; 
exit;
				return false;
			}
	}

}