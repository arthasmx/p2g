<?php
require_once 'Module/Articles/Repository/Resource/Abstract.php';
class Module_Articles_Repository_Resource_Article extends Module_Articles_Repository_Resource_Abstract {

/* MAIN ***************************************************************************************************/
	protected $type			= false;
	protected $article		= false;

	protected $publicated	= false;
	protected $maxpublicated	= false;
	protected $minpublicated	= false;
	protected $created		= false;

	protected $status		= false;
	protected $maxstatus	= false;

	/**
	 * Filtros, ordena, limites
	 */
	function setWhere(){
		//Propiedades
			if ($this->id!==false)  			$where[] = "id= '$this->id'";
			if ($this->type!==false)  			$where[] = "type = '$this->type'";
			if ($this->article!==false)  		$where[] = "article = '$this->article'";
			if ($this->username!==false)		$where[] = "username= '$this->username'";

			if ($this->publicated!==false)  	$where[] = "publicated = '$this->publicated'";
			if ($this->maxpublicated!==false)  	$where[] = "publicated <= '$this->maxpublicated'";
			if ($this->minpublicated!==false)  	$where[] = "publicated >= '$this->minpublicated'";

			if ($this->created!==false)  		$where[] = "created = '$this->created'";

			if ($this->status!==false)  		$where[] = "status = '$this->status'";
			if ($this->maxstatus!==false)  		$where[] = "status <= '$this->maxstatus'";
			
			if ($this->language!==false)  		$where[] = "language = '$this->language'";

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

	/**
	 * Obtenemos todos los usuarios sin importar al usuario del que pertenezcan (admin)
	 * Modificamos este metodo simplemente cambiando la propiedad $this->_query.
	 */
	public function get($todos_los_campos=false) {
		$this->setWhere();

		if($todos_los_campos){
			$this->_query="	SELECT * 
							FROM view_articles
							$this->where
							$this->group
							$this->order
							$this->limit";
		}else{
			$this->_query .= " ". $this->where ." ". $this->group ." ". $this->order ." ". $this->limit;
		}

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


	
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA LOS USUARIOS
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA LOS ADMIN
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

	
	/**
	 * TIPOS de articulos que se muestran en el listado
	 * @param INT $status | Maximo STATUS del articulo a buscar
	 */
	function getTipos($status=4){
		$this->reset();
		$this->_query="SELECT type,type_name AS name FROM view_articles";
		$tipos=$this->setGroup(array('0'=>"type"))
					->setLanguage(App::locale()->getLang())
					->setMaxstatus($status)
					->get(false);
		return App::module('Core')->getResource('Arrays')->toAssociative($tipos,'type','name');
	}

	/**
	 * AUTORES de articulos que se muestran en el listado
	 * @param INT $status | Maximo STATUS del articulo a buscar
	 */
	function getAuthor($status=4){
		$this->reset();
		$this->_query="SELECT username,autor FROM view_articles";
		$tipos=$this->setGroup(array('0'=>"username"))
					->setLanguage(App::locale()->getLang())
					->setMaxstatus($status)
					->get(false);
		return App::module('Core')->getResource('Arrays')->toAssociative($tipos,'username','autor');
	}

	/**
	 * STATUS AGRUPADOS de usuarios que se muestran en el listado
	 * @param INT $status | Maximo STATUS a buscar
	 */
	function getStatus($status=4){
		$this->reset();
		$this->_query="SELECT status,status_name AS name FROM view_articles";
		$tipos=$this->setGroup(array('0'=>"status"))
					->setLanguage(App::locale()->getLang())
					->setMaxstatus($status)
					->get(false);
		return App::module('Core')->getResource('Arrays')->toAssociative($tipos,'status','name');
	}
	
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA LOS ROOT
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//


//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS GENERICOS que no validan STATUS, USERNAME
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

	/**
	 * Obtiene los "otros" idiomas en los cuales existe un articulo
	 * Indica si el articulo ha sido escrito o no
	 * 
	 * @param int $article | Id de articulo
	 * @return mixed array
	 */
	function getArticleLanguages($article=false){
	if(!$article) return false;
	
		$this->reset();
		$this->_query="	SELECT
							ad.id AS article
							,if( LENGTH(ad.title)>0, 1,0 ) AS written
							,lang.prefix AS language
						FROM 
							articles_details AS ad
							JOIN languages AS lang ON lang.id = ad.lang_id
						WHERE
							ad.article_id = '".mysql_escape_string($article)."'";		
		return $this->get();
	}
	
	/**
	 * Detalle de articulo
	 */
	function detail($articulo=false,$idioma){
		if(!$articulo || !$idioma) return false;

		// Preparamos el query
			$this->reset()->setLanguage($idioma)->setId($articulo);
		// Admin puede revisar cualquier articulo ?
			if( App::getConfig('core', 'admin_modifica_cualquier_articulo')<1 ){
				$this->setUsername(App::module('Acl')->getModel('acl')->user);
			}
		$datosArticle = $this->setRow(true)->get(true);
		// Sacamos el articulo (el texto)
		$textoArticle = $this->getArticle($datosArticle['id']);
		return array_merge($datosArticle, $textoArticle );
	}

	/**
	 * Sacamos el SOLAMENTE EL ARTICULO segun el ID
	 *
	 * @param unknown_type $id
	 * @return unknown
	 */
	function getArticle($id=false){
		if(!$id) return false;
		
		$this->reset();
		$this->_query=" SELECT 
							ad.article 
						FROM 
							articles_details AS ad
							JOIN languages AS lang ON lang.id = ad.lang_id
						WHERE
							ad.id = '".mysql_escape_string($id)."'
							AND lang.prefix = '".App::locale()->getLang()."'";
		return $this->setRow(true)->get();
	}

}
