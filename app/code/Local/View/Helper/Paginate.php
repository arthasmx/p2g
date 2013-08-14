<?php

class Local_View_Helper_Paginate {
  public $paginator_page_name = false;
  
	protected $page_render		= 9; // Número de páginas a mostrar
	protected $url				= false; // Url base para los links
	protected $page_param_tpl	= null;

	protected $page_current		= false;
	protected $page_total		= false;
	protected $items_total		= false;
	protected $items_per_page	= false;

	public $view;

    public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }

    public function paginate() {

      // CSS
			//App::header()->addLink(App::skin('/css/blocks/dataset.css'),array('rel'=>'stylesheet','type'=>'text/css'));
			//App::header()->addLink(App::skin('/css/blocks/paginate.css'),array('rel'=>'stylesheet','type'=>'text/css'));

    	// Intentamos añadir las traducciones de la paginación
	    	if (class_exists("App") && method_exists('App',"translate")) {
	    		App::translate()->addGlobalTranslate('paginate');
	    	}
		return $this;
    }

    protected function getUrl() {
		if (!$this->url) {
			// Intentamos calcular el base url de forma automática
				$this->url=str_replace("/page/".$this->page_current,null,@$_SERVER["REQUEST_URI"]);
		}
		return $this->url;
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

  public function renderPages() {
    if (!$this->page_render) {
    	return null;
    }
    if ($this->items_total<1) {
      return App::xlat('PAGINATOR_no_pagination_links');
    }

    $this->page_param_tpl = $this->paginator_page_name . "/:page";
    $pages=$this->getPages();
    $pages_to_render = '<div class="pagination pagination-centered"><ul>';

    foreach ($pages as $key=>$page) {
      if ('pages'===$key) {
        foreach ($page as $numeric_page) {
          if (!$numeric_page['current']) {
            $pages_to_render .=  sprintf(
              "<li><a href='%s' class='paginate-link'>%s</a></li>",
              $this->getUrl()."/".str_replace(":page",$numeric_page['page'],$this->page_param_tpl),
              $numeric_page['title']
            );
          } else {
            $pages_to_render .= sprintf( "<li class='active'><a>%s</a></li>", $numeric_page['title'] );
          }
        }
      } else {
      	// Procesamos página de texto
      	$pages_to_render .= sprintf( "<li><a href='%s'>%s</a></li>" , $this->getUrl()."/".str_replace(":page",$page['page'],$this->page_param_tpl) , $page['title'] );
      }
    }

    return $pages_to_render . "</ul></div>";
  }

    /**
     * Muestra el detalle del rowset
     *
     */
    public function renderRowset() {
    	if ($this->items_total<1) {echo App::xlat('No se han encontrado resultados');return;}
    	$item_first=(($this->page_current-1)*$this->items_per_page)+1;
    	$item_last=($item_first+$this->items_per_page)-1;
    	if ($item_last>$this->items_total) $item_last=$this->items_total;
			echo str_replace(
				array(
					":item_first",
					":item_last",
					":item_total",
					":page_current",
					":page_total",
				),
				array(
					$this->view->toInteger($item_first),
					$this->view->toInteger($item_last),
					$this->view->toInteger($this->items_total),
					$this->view->toInteger($this->page_current),
					$this->view->toInteger($this->page_total),
				),
				App::xlat('page_rowset')
			);
    }

    /**
     * Devuelve array con las páginas que deben ser mostradas.
     *
     * Ésta funcion puede utilizarse conjuntamente con el método render o puede ser llamada directamente desde la vista
     * para implementar una rutina personalizada para mostrar las páginas.
     *
     * @return array
     */
    public function getPages() {
    	// Comprobaciones
    		if (!$this->page_render) {
    			echo "# Error: No se ha especificado page_render.";return;
    		}
    		if (false===$this->page_current) {
    			echo "# Error: No se ha especificado page_current.";return;
    		}
    		if (false===$this->page_total) {
    			echo "# Error: No se ha especificado page_total.";return;
    		}
    		if (false===$this->items_total) {
    			echo "# Error: No se ha especificado items_total.";return;
    		}
    		if (false===$this->items_per_page) {
    			echo "# Error: No se ha especificado items_per_page.";return;
    		}
    	// Generamos array de páginas a incluir
    		$pages=array();

  				// Si no estamos en la primera página, habilitamos el retroceso
  					if ($this->page_current!=1) {
  						$pages['first']=array(
  							'title'		=> App::xlat("page_first"),
  							'page'		=> 1,
  						);
  						$pages['prev']=array(
  							'title'		=> App::xlat("page_previous"),
  							'page'		=> $this->page_current-1,
  						);
  					}

  				// Cálculo de páginas intermedias
  					$page_render_first=$this->page_current-floor($this->page_render/2); // Calculamos la página en la que se comenzará.
  					// Forzamos que la página en la que se comenzará esté dentro del rango de páginas disponibles
  						if ( $page_render_first + $this->page_render > $this->page_total ) $page_render_first=$this->page_total-$this->page_render;
  						if ( $page_render_first < 1 ) $page_render_first=1;
					// Realizamos bucle para añadir las páginas
						$pages['pages']=array();
	  					for ( $i=$page_render_first; ($i <= $this->page_total ) && ( $i<=($page_render_first+$this->page_render) ); $i++ ) {
	  						$pages['pages'][$i]=array(
	  							'title'		=> $i,
	  							'page'		=> $i,
	  							'current'	=> $i == $this->page_current ? true:false,
	  						);
	  					}

  				// Si no estamos en la última, habilitamos la siguiente
  					if ($this->page_current!=$this->page_total) {
  						$pages['next']=array(
  							'title'		=> App::xlat("page_next"),
  							'page'		=> $this->page_current+1,
  						);
  						$pages['last']=array(
  							'title'		=> App::xlat("page_last",array($this->page_total)),
  							'page'		=> $this->page_total,
  						);
  					}

  		return $pages;
    }

}
?>