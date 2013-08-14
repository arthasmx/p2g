<?php
class Module_Debug_Repository_Model_Tracert extends Core_Model_Repository_Model {

	var $disabled = false;

	public function disable() {
		$this->disabled=true;
	}
	public function enable() {
		$this->disabled=false;
	}

  	/**
	 * Metodo para almacenar el path de los archivos procesados por pantalla
	 *
	 * @param string $path | URL del archivo
	 * @param string $metodo | Metodo que se esta ejecutando
	 */
	public function setScriptPathApp($path=false,$metodo='') {
		if (empty($path) || ($this->_module->getConfig('core','scriptpath') <= 0) ) return;

        $path = str_replace("/","\\", $path);

		// Limpiamos el PATH
			if( stristr($path, 'files')) {
				preg_match_all('(files.*)',$path,$path);
				$path = str_replace("\\",$this->_module->getConfig('core','scriptchar'), $path[0][0]);
			}
			elseif( stristr($path, 'Module')) {
				preg_match_all('(Module.*)',$path,$path);
				$path = str_replace("\\",$this->_module->getConfig('core','scriptchar'), $path[0][0]);
			}
			elseif( stristr($path, 'template')) {
				preg_match_all('(template.*)',$path,$path);
				$path = str_replace("\\",$this->_module->getConfig('core','scriptchar'), $path[0][0]);
			  }

		// Aplicamos CSS si es un CODE\MODULE
			if( stristr($path, 'file')) {
				$path = '<b style="color:'.$this->_module->getConfig('core','fileColor').'">'.$path.'</b>';
			}
			elseif( stristr($path, 'template')) {
				// Obtenemos el tipo de vista
					if( stristr($path, 'view')) {
						$path = '<b style="color:'.$this->_module->getConfig('core','viewColor').'">'.$path.'</b>';
						$metodo = 'View';
					  }
					elseif( stristr($path, 'block')) {
						$path = '<b style="color:'.$this->_module->getConfig('core','blockColor').'">'.$path.'</b>';
						$metodo = 'Block';
					  }
					elseif( stristr($path, 'layout')) {
						$path = '<b style="color:'.$this->_module->getConfig('core','layoutColor').'">'.$path.'</b>';
						$metodo = 'Layout';
					  }
			}
			else{ 	$path = '<b style="color:'.$this->_module->getConfig('core','moduleColor').'">'.$path.'</b>';	  }

		// Obtenemos las URL previas y se almacenan en variable temporal
			$urls = @App::registry('ScriptPathApp');
			foreach((array)$urls as $url){
				if ( !is_array($url) ){
					$_urls[] = array(
										'script' => $url,
										'metodo' => $metodo
									);
				}else{
					$_urls[] = $url;
				}
			}

		// Agregamos la nueva url
			$_urls[] = array(	'script' => $path,
								'metodo' => $metodo
							);

		// Guardamos urls al registro para mostrarlas al final del sitio
			App::registry()->set("ScriptPathApp", $_urls);
	}

	/**
	 * Metodo para devolver el path de los archivos procesados por pantalla. Se eliminan para no sobrecargar el array
	 *
	 * @return String
	 */
	public function getScriptPathApp() {
			if ( $this->_module->getConfig('core','scriptpath') <= 0 ) return;

			return App::registry('ScriptPathApp');
	}


}