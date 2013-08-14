<?php

class Module_Articles_Repository_Resource_Article extends Core_Model_Repository_Resource {

	protected $username         = false;
	protected $id = false;
	protected $seo = false;
	protected $maxstatus = false;
	protected $articulo= false;
	protected $articulos= false;
	protected $articulos_en= false;
	protected $articulos_es= false;
	protected $status= false;
	protected $languaje= false;

	protected $admin= false;
	protected $filter_by_username_es = false;
	protected $filter_by_username_en = false;
	
	protected $titulo = false;
	protected $fecha_creado = false;
	protected $fecha_publicado = false;
	protected $tipo = false;
	
	
	protected $_db = null;
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
	
	/**
	 * Auto inicializa el acceso a la base de datos
	 */
	public function init() {
		$this->_db=App::module('Core')->getResourceSingleton('Db')->get();
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


	/**
	 * Filtros, ordena, limites
	 */
	function setWhere(){
		//Propiedades

			// Revisamos la configuracion del modulo para ver si los admin PUEDEN o NO PUEDEN modificar los cualquier articulo
			// Esto es, articulos creados por el o por otro admin
			if($this->admin!==false){
				if( $this->_module->getConfig('core', 'admin_modifica_cualquier_articulo')>0 ){
					$this->filter_by_username_es = "";
					$this->filter_by_username_en = "";
				}else{
					$this->filter_by_username_es = "AND aes.usuario_creado = '$this->admin'";
					$this->filter_by_username_en = "AND aen.usuario_creado = '$this->admin'";
					
					$where[] = "aes.usuario_creado = '$this->admin' OR aen.usuario_creado = '$this->admin'";
				}

			}else{

				if ($this->username!==false)  			$where[] = "ae.usuario_creado = '$this->username'";
				if ($this->maxstatus!==false)  			$where[] = "ae.status <= '$this->maxstatus'";
			}

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

// Esta query media rara, trata de obtener los articulos segun el idioma indicado. Por default inicia con el idioma actual, el del locale.
// Si no hay articulos en ese idioma, pero si los hay en el otro idioma, los muestra. es decir, si estas en el locale español, pero tu articulo
// solamente esta escrito en ingles, te muestra el articulo en ingles, sin importar que el locale esta en español
/* OJO: Probar que sirva esto! */
			$this->_query="
							SELECT
								a.articulo
								,a.en_id
								,a.es_id
								,a.foto
								,a.fecha_publicado
								,a.tipo

								,if (
									( LENGTH(aes.article) < 12)
									, aen.titulo
									,aes.titulo
								) AS titulo
								
								,if (
									( LENGTH(aes.article) < 12)
									, aen.seo
									,aes.seo
								) AS seo
							
								,if (
									( LENGTH(aes.article) < 12)
									, aen.usuario_creado
									,aes.usuario_creado
								) AS usuario_creado
							
								,if (
									( LENGTH(aes.article) < 12)
									, aen.fecha_creado
									,aes.fecha_creado
								) AS fecha_creado

								,if (
									( LENGTH(aes.article) < 12)
									, aen.status
									,aes.status
								) AS status								

								,if (
									( LENGTH(aes.article) > 0)
									, 1
									, 0
								) AS idioma_es
							
								,if (
									( LENGTH(aen.article) > 0)
									, 1
									, 0
								) AS idioma_en

							FROM 
								articulos AS a
								LEFT JOIN articulos_es AS aes ON aes.id = a.es_id $this->filter_by_username_es
								LEFT JOIN articulos_en AS aen ON aen.id = a.en_id $this->filter_by_username_en
							$this->where
							$this->group
							$this->order
							$this->limit";

		}
		
		if (!$this->_query) return false;

//echo '<pre>'; print_r($this->_query); echo '</pre>'; exit;

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
	 * Crea articulo nuevo
	 */
	function create(){
		// Primero insertamos en la tabla del idioma para obtener el ID del articulo
			$query_1 = "INSERT INTO articulos_".$this->languaje." (titulo,seo,article,usuario_creado,fecha_creado) VALUES('".$this->titulo."','".$this->seo."','".$this->articulo."','".$this->username."','".$this->fecha_creado."')";
			$this->_db->query($query_1);
			if($this->languaje=='es'){ 
				$leng='en';
				$es_id=$this->_db->lastInsertId(); 
			}else{ 
				$leng='es'; 
				$en_id=$this->_db->lastInsertId();
			}

			$query_2 = "INSERT INTO articulos_".$leng." (titulo,seo,article,usuario_creado,fecha_creado) VALUES('','','','".$this->username."','".$this->fecha_creado."')";
			$this->_db->query($query_2);
			if(isset($es_id)){ 
				$en_id=$this->_db->lastInsertId(); 
			}else{ 
				$es_id=$this->_db->lastInsertId();
			}

		// Segundo: Insertamos en la tabla ARTICULOS, poniendo los datos que le corresponden a los ID's
			$query_3 = "INSERT INTO articulos (en_id,es_id,tipo,fecha_publicado) VALUES('".$en_id."','".$es_id."','".$this->tipo."','".$this->fecha_publicado."')";
			$this->_db->query($query_3);
			
		return true;
	}

	/**
	 * Devolvemos detalle de articulo
	 */
	function detail($idioma=false){
		if(!$this->articulo || !$idioma) return false;
		//$this->_query="SELECT * FROM vista_detalle_articulo_".App::locale()->getLang()." WHERE articulo='".mysql_escape_string($this->articulo)."'";
		$this->_query="SELECT * FROM vista_detalle_articulo_".$idioma." WHERE articulo='".mysql_escape_string($this->articulo)."'";
		// Revisamos si el admin tiene permiso de modificar cualquier articulo
		// Si es MENOR QUE 1, es que NO TIENE PERMISOS; por lo que debemos filtrar los datos por el autor del articulo
			if( $this->_module->getConfig('core', 'admin_modifica_cualquier_articulo')<1 ){
				$this->_query .= " AND usuario_creado='".mysql_escape_string(App::module('Acl')->getModel('acl')->user)."'";
			}
		return $this->setRow(true)->get();
	}

	/**
	 * Devolvemos los datos del parametros
	 */
	function params($idioma=false){
		if(!$this->articulo || !$idioma) return false;
		$this->_query="SELECT * FROM vista_detalle_articulo_".$idioma." WHERE id='".mysql_escape_string($this->articulo)."'";
		// Revisamos si el admin tiene permiso de modificar cualquier articulo
		// Si es MENOR QUE 1, es que NO TIENE PERMISOS; por lo que debemos filtrar los datos por el autor del articulo
			if( $this->_module->getConfig('core', 'admin_modifica_cualquier_articulo')<1 ){
				$this->_query .= " AND usuario_creado='".mysql_escape_string(App::module('Acl')->getModel('acl')->user)."'";
			}
		return $this->get();
	}

	/**
	 * Devolvemos campo ARTICULO para el idioma INVERSO al actual
	 * De momento solamente manejamos 2 idiomas, ya si se necesita agregar mas, pues agregariamos al INI un array para el manejo de los idiomas
	 */
	function detailInverseLanguaje(){
		if(!$this->articulo) return false;
		
		if(App::locale()->getLang()=="es"){
			$lang="en";
		}else{
			$lang="es";
		}

		$this->_query="SELECT * FROM vista_detalle_articulo_".$lang." WHERE articulo='".mysql_escape_string($this->articulo)."'";
		return $this->get();
	}

	/**
	 * Editamos un articulo
	 */
	function edit($id,$articulo,$article,$titulo,$seo,$fecha_publicado,$tipo,$idioma){
		if (!$id || !$articulo || !$article || !$titulo || !$seo || !$fecha_publicado || !$tipo || !$idioma) return false;

		// TABLA ARTICULOS
		$query="UPDATE articulos SET fecha_publicado='".$fecha_publicado."',tipo='".$tipo."' WHERE articulo='".$articulo."'";
		$this->_db->query($query);
//echo '<pre>'; print_r($query); echo '</pre>';
		// Editamos tabla del articulo segun el idioma
		$query="UPDATE articulos_".$idioma." SET titulo='".$titulo."',seo='".$seo."',article='".$article."' WHERE id='".$id."'";
		$this->_db->query($query);

//echo '<pre>'; print_r($query); echo '</pre>';
//exit;		
		
		return "true";
	}
	
	/**
	 * Modificamos el status de uno o mas articulos: POR EL LISTADO
	 */
	function editArticle($id=false, $articulo, $idioma) {
		if (!$id || !$articulo) return false;

		$query="UPDATE articulos_".$idioma." SET article='".$articulo."' WHERE id='".$id."'";
		$this->_db->query($query);
		return "true";
	}

	/**
	 * Modificamos los parametros de un articulo
	 */
	function editParams($articulo=false, $id=false, $idioma=false, $titulo=false,$seo=false,$fecha=false,$tipo=false) {
		if (!$articulo || !$id || !$idioma ||  !$titulo || !$seo || !$fecha || !$tipo) return false;

		// TABLA ARTICULOS
		$query="UPDATE articulos SET fecha_publicado='".$fecha."',tipo='".$tipo."' WHERE articulo='".$articulo."'";
		$this->_db->query($query);

		// Tabla ARTICULOS_ES
		$query="UPDATE articulos_".$idioma." SET titulo='".$titulo."',seo='".$seo."' WHERE id='".$id."'";
		$this->_db->query($query);

		return "true";
	}

	/**
	 * Modificamos el status de uno o mas articulos: POR EL LISTADO
	 */
	function statusUpdate() {
		if (sizeof($this->articulos)>0) {
			
			// Modificamos el status de los articulos en español
			foreach((array)$this->articulos_es AS $articulo){
				$query='UPDATE articulos_es SET status="'.$this->status.'" WHERE id="'.$articulo.'"';
				$this->_db->query($query);
			}
			
			// Modificamos el status de los articulos en ingles
			foreach((array)$this->articulos_en AS $articulo){
				$query='UPDATE articulos_en SET status="'.$this->status.'" WHERE id="'.$articulo.'"';
				$this->_db->query($query);
			}
			
		}
		return "true";
	}
	
	/**
	 * Modificamos el status de un articulo. Esto al ver el detalle de 1 articulo
	 * @return unknown
	 */
	function statusByArticleUpdate(){
		$query='UPDATE articulos_'.$this->languaje.' SET status="'.$this->status.'" WHERE id="'.$this->articulo.'"';
		$this->_db->query($query);
		return "true";		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
    function getArchives() {
		return $this->_db->query('SELECT
											count(*) as total,
											DATE_FORMAT(date_publish,"%Y/%m/01") as date_publish,
											DATE_FORMAT(date_publish,"%Y") as date_publish_year,
											DATE_FORMAT(date_publish,"%m") as date_publish_month
									FROM blog WHERE date_publish<=NOW() GROUP BY DATE_FORMAT(date_publish,"%Y/%m/01") ORDER BY date_publish DESC')->fetchAll();
    }

    /**
     * Devuelve todas las casos de éxito
     *
     * @return array
     */
    function getAll() {
		return $this->_db->query('SELECT * FROM blog WHERE date_publish<=NOW() ORDER BY date_publish DESC')->fetchAll();
    }

    /**
     * Devuelve una actividad
     *
     * @return array
     */
    function getArticulo() {
    	$query ='SELECT
    				idioma.*
    				,rel.*
    			FROM
    				articulos_'.App::locale()->getLang().' AS idioma
    				LEFT JOIN articulos AS rel ON rel.'.App::locale()->getLang().'_id=idioma.id
    			WHERE
    				idioma.seo="'.mysql_escape_string($this->seo).'"
    				AND idioma.estado_eliminado="'.mysql_escape_string($this->estado_eliminado).'"
    				AND idioma.estado_visible="'.mysql_escape_string($this->estado_visible).'"
    			ORDER BY idioma.id ASC';

		return $this->_db->query($query)->fetch();
    }

    /**
    * Devuelve todos los articulos (actividades)
    * @return array
    */
    function getAllArticulos() {
    	$query='SELECT
    				idioma.*
    				,rel.*
    			FROM
    				articulos_'.App::locale()->getLang().' AS idioma
    				LEFT JOIN articulos AS rel ON rel.'.App::locale()->getLang().'_id=idioma.id
    			WHERE
    				idioma.estado_eliminado= '.mysql_escape_string($this->estado_eliminado).'
    				AND idioma.estado_visible='.mysql_escape_string($this->estado_visible).'
    				AND rel.tipo="actividad"
    			ORDER BY idioma.id ASC';
		return $this->_db->query($query)->fetchAll();
    }
    
/*
    function getAllArticulosOLD() {
    	$query='SELECT
    				idioma.*
    				,rel.*
    			FROM
    				articulos_'.App::locale()->getLang().' AS idioma
    				LEFT JOIN articulos AS rel ON rel.'.App::locale()->getLang().'_id=idioma.id
    			WHERE
    				idioma.estado_eliminado= '.mysql_escape_string($this->estado_eliminado).'
    				AND idioma.estado_visible='.mysql_escape_string($this->estado_visible).'
    				AND rel.tipo="actividad"
    			ORDER BY idioma.id ASC';
		return $this->_db->query($query)->fetchAll();
    }
*/
    
    /**
     * Devuelve todas las casos de éxito de un año y mes determinados.
     *
     * Si no se especifican, se devuelven las del año y mes actual
     *
     * @return array
     */
    function getAllByDate($year=null,$month=null) {
    	if (!$year || !$month) {
    		// No se ha especificado el año o el mes, cargamos el último mes disponible
    		$latestMonth=$this->getLatestMonth();
    		$year=@$latestMonth[0]['date_publish_year'];
    		$month=@$latestMonth[0]['date_publish_month'];
    	}
		return $this->_db->query('SELECT * FROM blog
									WHERE
										date_publish<=NOW()
										AND DATE_FORMAT(date_publish,"%Y")="'.mysql_escape_string($year).'"
										AND DATE_FORMAT(date_publish,"%m")="'.mysql_escape_string($month).'"
									ORDER BY date_publish DESC')->fetchAll();
    }

    /**
     * Devuelve las últimas casos de éxito
     *
     * @param int $total
     * @return array
     */
    function getLatest($total=1) {
		//return $this->_db->query('SELECT * FROM blog WHERE date_publish<=NOW() ORDER BY date_publish DESC LIMIT 0,'.(int)$total)->fetchAll();

		$query = 'SELECT
						b.*
						,count(c.id) AS comentarios
						,truncate( r.points / r.votes,0 ) AS rating
                  FROM
						blog AS b
						LEFT JOIN comentarios AS c ON c.subcategoria = b.id
						LEFT JOIN rate AS r ON r.id = b.id
                  WHERE
						b.date_publish<=NOW()
                  GROUP BY b.id
                  ORDER BY b.date_publish
                  DESC LIMIT 0,'.(int)$total;

		return $this->_db->query($query)->fetchAll();
    }

    /**
     * Devuelve el último mes con casos de éxito
     *
     * @param int $total
     * @return array
     */
    function getLatestMonth() {
		return $this->_db->query('SELECT
										DATE_FORMAT(date_publish,"%Y") as date_publish_year,
										DATE_FORMAT(date_publish,"%m") as date_publish_month
								FROM blog WHERE date_publish<=NOW() ORDER BY date_publish DESC LIMIT 0,1')->fetchAll();
	}

	/**
	 * Devuelve la noticia con el id indicado
	 *
	 * @param string $id
	 * @return array
	 */
	function getById($id) {
		return $this->_db->query('SELECT * FROM blog WHERE id="'.mysql_escape_string($id).'" AND date_publish<=NOW() LIMIT 0,1')->fetchAll();
	}



}