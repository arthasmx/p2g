<?php
require_once 'Module/Articles/Repository/Resource/Abstract.php';
class Module_Articles_Repository_Resource_Article extends Module_Articles_Repository_Resource_Abstract {

/* MAIN ***************************************************************************************************/
	protected $type				= false;
	protected $article			= false;
	protected $article_id		= false;

	protected $picture			= false;
	protected $title			= false;
	protected $seo				= false;
	protected $category			= false;
	protected $draft			= false;
	protected $draft_username	= false;
	
	protected $publicated		= false;
	protected $maxpublicated	= false;
	protected $minpublicated	= false;
	protected $created			= false;

	protected $status			= false;
	protected $maxstatus		= false;
	protected $lang				= false;

	/**
	 * Filtros, ordena, limites
	 */
	function setWhere(){
		//Propiedades
			if ($this->id!==false)  			$where[] = "id= '$this->id'";
			if ($this->article_id!==false)  	$where[] = "article_id= '$this->article_id'";
			if ($this->type!==false)  			$where[] = "article_type_id= '$this->type'";
			if ($this->article!==false)  		$where[] = "article = '$this->article'";
			if ($this->username!==false)		$where[] = "username= '$this->username'";
			if ($this->draft_username!==false)	$where[] = "ad.username= '$this->draft_username'";

			if ($this->publicated!==false)  	$where[] = "publicated = '$this->publicated'";
			if ($this->maxpublicated!==false)  	$where[] = "publicated <= '$this->maxpublicated'";
			if ($this->minpublicated!==false)  	$where[] = "publicated >= '$this->minpublicated'";

			if ($this->created!==false)  		$where[] = "created = '$this->created'";

			if ($this->status!==false)  		$where[] = "status = '$this->status'";
			if ($this->maxstatus!==false)  		$where[] = "status <= '$this->maxstatus'";

			if ($this->language!==false)  		$where[] = "language = '$this->language'";
			if ($this->lang!==false)  			$where[] = "lang_id= '$this->lang'";

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
		$this->_query="SELECT article_type_id AS type,article_type_name AS name FROM view_articles";
		$tipos=$this->setGroup(array('0'=>"article_type_id"))
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

	/**
	 * Actualizamos los parametros del articulo
	 * Es un UPDATE para 2 tablas ( articles y articles_details )
	 * @param $parent
	 * @param $article
	 * @param $language
	 * @param unknown_type $lang_id
	 * @param $title
	 * @param $seo
	 * @param $publicated
	 * @param $type
	 */
	function saveParams($parent,$article,$language,$lang_id,$title,$seo,$publicated,$type,$category,$status){
		$this->reset();
		// Actualizamos tabla ARTICLES
			$query1 = "UPDATE articles SET type='$type', category='$category' WHERE article='$parent'";
		// Actualizamos los parametros en tabla ARTICLES_DETAILS
			$publicated=App::module('Core')->getResource('Dates')->toDate(0,$publicated);
			$query2 = "UPDATE articles_details SET title='$title',seo='$seo',publicated='$publicated',status='$status' WHERE article_id='$parent' AND id='$article'";
		// Ejecutamos las queries
			$this->_db->query($query1);
			$this->_db->query($query2);
		return true;
	}

	function translateParams($parent,$lang_id,$title,$seo,$publicated,$type,$category,$status){
		$this->reset();
		// Actualizamos tabla ARTICLES
			$query1 = "UPDATE articles SET type='$type', category='$category' WHERE article='$parent'";
			$this->_db->query($query1);
		// Actualizamos los parametros en tabla ARTICLES_DETAILS
			$publicated=App::module('Core')->getResource('Dates')->toDate(0,$publicated);
			$username=App::module('Acl')->getModel('Acl')->user;
			$query2 = "INSERT INTO articles_details(article_id,lang_id,title,seo,username,created,publicated,status) VALUES('$parent','$lang_id','$title','$seo','$username',now(),'$publicated','$status')";
		// Ejecutamos las queries
			$this->_db->query($query2);
			return $this->_db->lastInsertId();
	}

	/**
	 * Actualizamos articulo
	 *
	 * @param unknown_type $article
	 * @param unknown_type $parent
	 * @param unknown_type $id
	 * @param unknown_type $lang_id
	 */
	function edit($article=false,$parent=false,$id=false,$lang_id=false){
		if(!$article || !$parent || !$id || !$lang_id) return false;
		$this->reset();
		$article= str_replace('\"','"',$article);
		$query = "UPDATE articles_details SET article='".mysql_escape_string($article)."' WHERE article_id='$parent' AND id='$id' AND lang_id='$lang_id'";
		$this->_db->query($query);
		return true;
	}

	/**
	 * Creamos un articulo
	 */
	function create(){
		if( !$this->language || !$this->title || !$this->seo || !$this->publicated || !$this->type || !$this->category ) return false;

		// Guardamos referencia del articulo en ARTICLES
			$cue="INSERT INTO articles(picture,type,category) VALUES('".mysql_escape_string($this->picture)."','".mysql_escape_string($this->type)."','".mysql_escape_string($this->category)."')";
			$this->_db->query($cue);
			$parent=$this->_db->lastInsertId();
			if(!$parent || $parent<=0) return false;

		// Guardamos referencia del articulo en ARTICLES_DETAILS
			$cue="INSERT INTO articles_details(article_id,lang_id,title,seo,article,username,created,publicated) 
							VALUES(	'".mysql_escape_string($parent)."',
									'".mysql_escape_string($this->language)."',
									'".mysql_escape_string($this->title)."',
									'".mysql_escape_string($this->seo)."',
									'".mysql_escape_string($this->article)."',
									'".mysql_escape_string($this->username)."',
									'".mysql_escape_string($this->created)."',
									'".mysql_escape_string($this->publicated)."')";
			$this->_db->query($cue);

		// Revisamos si el DRAFT. Si trae valor, ELIMINAMOS dicha referencia de la tabla ARTICLES_DRAFT, pues dicho articulo YA fue creado
			if($this->draft!="false"){
				$cue="DELETE FROM articles_drafts WHERE id='$this->draft'";
				$this->_db->query($cue);
			}

	return true;
	}

	/**
	 * Creamos el DRAFT del articulo que se esta escribiendo actualmente
	 */
	function draft(){
		if( !$this->language || !$this->title || !$this->seo || !$this->publicated || !$this->type || !$this->category ) return false;

		$this->article = str_replace('\"','"',$this->article);

		// Revisamos el DRAFT. Si NO trae valor, INSERTAMOS contenido
			if($this->draft=="false" || $this->draft=="0"){
				$cue="	INSERT INTO 
						articles_drafts(type,category,lang_id,title,seo,article,username,created,publicated) 
						VALUES(	'".mysql_escape_string($this->type)."',
								'".mysql_escape_string($this->category)."',
								'".mysql_escape_string($this->language)."',
								'".mysql_escape_string($this->title)."',
								'".mysql_escape_string($this->seo)."',
								'".mysql_escape_string($this->article)."',
								'".mysql_escape_string($this->username)."',
								'".mysql_escape_string($this->created)."',
								'".mysql_escape_string($this->publicated)."')";

				$this->_db->query($cue);
				$parent=$this->_db->lastInsertId();
				if(!$parent || $parent<=0) return false;

			}else{
				$cue="	UPDATE 
						articles_drafts SET 
								type='".mysql_escape_string($this->type)."',
								category='".mysql_escape_string($this->category)."',
								lang_id='".mysql_escape_string($this->language)."',
								title='".mysql_escape_string($this->title)."',
								seo='".mysql_escape_string($this->seo)."',
								article='".mysql_escape_string($this->article)."',
								publicated='".mysql_escape_string($this->publicated)."',
								created='".mysql_escape_string($this->created)."'
						WHERE
							id='$this->draft'";

				$this->_db->query($cue);
				$parent=$this->draft;
			}
	// Regresamos el DRAFT
	return $parent;
	}

	/**
	 * Obtenemos los Drafts del sitio
	 * Como varios admin pudieran trabajar el sitio, al crear el query, se valida si ADMIN puede editar cualquier articulo
	 * Dependiendo de la configuracion del sitio, sera la carga de estos articulos (drafts)
	 */
	function getDrafts(){
		$this->_query="	SELECT
							ad.id
							,ad.title
							,ad.created
							,lang.id AS lang_id
							,lang.prefix AS language
							,u.name AS autor
						FROM
							articles_drafts AS ad
							JOIN user AS u ON u.username = ad.username
							JOIN languages AS lang ON lang.id = ad.lang_id";
		return $this->get();
	}

	/**
	 * Obtenemos el detalle de un draft para cargarlo y continuar con su edicion
	 * Como varios admin pudieran trabajar el sitio, al crear el query, se valida si ADMIN puede editar cualquier articulo
	 * Dependiendo de la configuracion del sitio, sera la carga de estos articulos (drafts)
	 */
	function getDraft($draft=false){
		if(!$draft || $draft=="false") return false;

		$this->_query="	SELECT * FROM articles_drafts";
		return $this->setId( mysql_escape_string($draft) )->setRow(true)->get();
	}

	/**
	 * Borramos un articulo del draft
	 */
	function deleteDraft($drafts=false){
		if(!$drafts || $drafts=="false" || sizeof($drafts)<=0 ) return false;

		foreach ($drafts AS $id){
			$cue="DELETE FROM articles_drafts WHERE id='".mysql_escape_string($id)."'";
			$this->_db->query($cue);
		}
		return "true";
	}


//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS PARA LOS ROOT
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//


//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//
// METODOS GENERICOS que no validan STATUS, USERNAME
//°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°//

	/**
	 * Obtenemos los datos de tabla ARTICLES solamente
	 * Utilizado para sacar IMAGEN,TIPO,CATEGORIA de un articulo (parent)
	 *
	 * @param int $parent
	 */
	function getParentData($parent){
		if(!$parent) return false;
		
		$this->reset();
		$this->_query="SELECT * FROM articles WHERE article='$parent'";
		return $this->setRow(true)->get();
	}
	
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
							ad.article_id AS parent_article
							,ad.id AS article
							,if( LENGTH(ad.title)>0, 1,0 ) AS written
							,lang.name AS language
							,lang.id AS id
							,lang.prefix
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

		return $datosArticle;
		// MODIFICADO el 15 ENERO 2010 para evitar la carga de cosas sin sentido

		// Sacamos el articulo (el texto)
		/*
		$textoArticle = $this->getArticle($datosArticle['id']);
		if(sizeof($textoArticle)<=0) {$textoArticle=array('article'=>'');}
		return array_merge($datosArticle, $textoArticle );
		*/
	}

	 /**
     * Detalle de articulo cargado segun su IDIOMA
     * Es cuando recargas el articulo en otro idioma para editarlo mas rapido
     */
	function reload($parent=false,$idioma){
		if(!$parent || !$idioma) return false;

		// Preparamos el query
			$this->reset()->setLanguage($idioma)->setArticle_id($parent);
		// Admin puede revisar cualquier articulo ?
			if( App::getConfig('core', 'admin_modifica_cualquier_articulo')<1 ){
				$this->setUsername(App::module('Acl')->getModel('acl')->user);
			}
		$datosArticle = $this->setRow(true)->get(true);

		return $datosArticle;
	}
	
	
	/**
	 * Sacamos SOLAMENTE EL ARTICULO segun el ID
	 *
	 * @param $id
	 * @param $parent
	 * @return unknown
	 */
	function getArticle($id=false,$parent=false){
		if(!$id || !$parent) return false;

		$this->reset();
		$this->_query=" SELECT 
							article 
						FROM 
							articles_details
						WHERE
							id = '".mysql_escape_string($id)."' 
							AND article_id = '".mysql_escape_string($parent)."'";
		return $this->setRow(true)->get();
	}

	/**
	 * Guardamos la referencia de la imagen representativa del articulo
	 * Es aquella imagen que mostramos en los listados, en bloques, en resultados de busquedas
	 */
	function setImageListing($article_id=false, $picture=false){
		if(!$article_id || !$picture) return false;
		$query='UPDATE articles SET picture="'.mysql_escape_string($picture).'" WHERE article="'.mysql_escape_string($article_id).'"';
		$this->_db->query($query);
		return true;
	}

}