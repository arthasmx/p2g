<?php

class Module_Core_Repository_Resource_Rate extends Core_Model_Repository_Resource {

	protected $id		= false;
	protected $type		= false;
	protected $ip		= false;
	protected $vote		= false;

	protected $_filter = null;
	protected $_db = null;

	function init(){
		$this->_filter	= $this->_module->GetResourceSingleton('Filter');
		$this->_db		= $this->_module->GetResourceSingleton('db')->get();
	}

	/*
		Esto es de prueba, va para modulo de Categorias

		- Se debe hacer un metodo para dar formato los registros que regresa esta funcion
		- Esto es para

	*/
	function getCat(){
		$cue = "
				SELECT
					c.cat_id AS Idpad
					,c.cat_name AS Padre
					,cl_1.cat_id AS Idhij
					,cl_1.cat_name AS Hijo
					,cl_2.cat_id AS Idsub
					,cl_2.cat_name AS SubHijo
					,cl_3.cat_id AS SubSubId
					,cl_3.cat_name AS SubSubHijo

				FROM
					categorias AS c
					LEFT JOIN categorias AS cl_1 ON cl_1.cat_parent = c.cat_id
					LEFT JOIN categorias AS cl_2 ON cl_2.cat_parent = cl_1.cat_id
					LEFT JOIN categorias AS cl_3 ON cl_3.cat_parent = cl_2.cat_id

				WHERE
					c.cat_id = '".$this->id."'
				ORDER BY c.cat_name
		";
		return $this->_db
				->query( $cue )
    			->fetchAll();
	}

	public function __call($function, $args) {
		// Comprueba SET
			preg_match("/^set([a-zA-Z]+)$/",$function,$matches);
			if (isset($matches[1])) {
				$var=strtolower($matches[1]);
				if (isset($this->{$var}) || @$this->{$var}===false) {
					$this->{$var}=$this->_filter->escape($args[0]);
				}
				return $this;
			}
		// Comprueba GET
			preg_match("/^get([a-zA-Z]+)$/",$function,$matches);
			if (isset($matches[1])) {
				$var=strtolower($matches[1]);
				if (isset($this->{$var})) {
					return $this->{$var};
				}
				return false;
			}
	}

// Metodos personalizados *******************************************************************************

	/**
	* Regresa el rating segun el Id
	*
	*/
	function get(){

		switch ($this->type){
			case 1: // Rating de imagenes
					$cue = "
						SELECT
							 r.*
							,rv.*
							, TRUNCATE((r.points / r.votes),0) AS rating

						FROM
							rate AS r

							LEFT JOIN rate_votes AS rv
							ON r.id = rv.rate_id AND rv.ip = '".$this->ip."' AND rv.daterate = '".date('Y-m-d')."'

						WHERE
							r.id = '".$this->id."'
							AND r.type = '".$this->type."'";
					break;

			case 2: // Poll
					$cue = "
						SELECT
							r.id AS id
							, r.question AS pregunta
							, ro.id AS opcion_id
							, ro.option AS opciones
							, (SELECT COUNT(*) FROM rate_votes AS rv WHERE rv.rate_id = ro.rate_id AND rv.rate = ro.id ) AS votos

						FROM rate AS r
							LEFT JOIN rate_options AS ro ON ro.rate_id = r.id

						WHERE
							r.stat = '1'
							AND r.type = '2'
							AND r.id = '".$this->id."'";

					break;

			default:
					return false;
		}

		try{
			$this->css();
			return $this->_db->query( $cue )->fetchAll();
		}catch(Exception $e){
			$this->_module->exception( App::xlat('Ha ocurrido un error en el query para obtener el Rating') ,501 );
		}
	}

	/**
	* Guarda una calificacion segun el Id
	*
	*/
	function rate(){
		if ( !$this->id || !$this->ip ) {
			$this->_module->exception( App::xlat('Se debe especificar el ID del Item, asi como la IP del usuario') ,501 );
		}

		try{
			// Verificamos si es el primer rating para este ITEM
				if ( !$this->get() ){
					$rate ="
							INSERT INTO rate (id,votes,points,type)
							VALUES('".$this->id."','1','".$this->vote."',1)";
				}else {
					$rate ="
							UPDATE rate SET
								votes = votes + 1,
								points = points + ".$this->vote."
							WHERE
								id = '".$this->id."'";
				}

			// Marcamos como VOTADO al ITEM
				$rate_votes = "
						INSERT INTO rate_votes(rate_id,ip,rate,daterate)
						VALUES('".$this->id."','".$this->ip."','".$this->vote."','".date('Y-m-d')."')";

			$this->_db->query( $rate );
			$this->_db->query( $rate_votes );

		}catch(Exception $e){
			$this->_module->exception( App::xlat('Ha ocurrido un error en el query para obtener el Rating') ,501 );
		}
		return $this;
	}

	/**
	* Carga el css que le corresponde al Rating
	*
	*/
	function css(){
		App::header()->addLink(App::skin('/css/pages/rate.css'),array(
			"rel"=>"stylesheet",
			"type"=>"text/css",
			"media"=>"all",
		));
	}

	/**
	* Descripcion de funcion
	*
	*/
	function results($fetch=false){
		if ( !$this->id ) $this->_module->exception( App::xlat('No se ha definido el ID para saber su rating') ,501 );

		$cue = "
				SELECT
					 r.*
					, TRUNCATE((r.points / r.votes),0) AS rating

				FROM
					rate AS r

				WHERE
					r.id = '".$this->getId()."'
					AND r.type = '1'";
		try{

			if ($fetch)
				return $this->_db->query( $cue )->fetch();
			else
				return $this->_db->query( $cue )->fetchAll();
		}catch(Exception $e){
			$this->_module->exception( App::xlat('Error al obtener el rating') ,501 );
		}

	}

}