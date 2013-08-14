<?php
class Module_Debug_Observer_Tracert extends Core_Model_Module_Observer {

	public function init() {}

	/**
	* @desc Archivos procesados por la aplicacion
	*/
	public function dispatch($options=array()) {
		//App::header()->addScript(App::jslib('/debug.js'));
		if (App::$allowDebugRender==true) {
			if ( $this->_module->getConfig('core','scriptpath') > 0 && count($this->_module->getModelSingleton('Tracert')->getScriptPathApp())>0 ){
				echo "<div id='he' style='float:left; padding:20px;'><h3>Archivos procesados para esta pantalla</h3>";
				$i=0;
				echo '<table cellspacing="0" border="0">
				<tr><td colspan="4">&nbsp;<td>
				<tr><td>#</td><td><b>Script</b></td><td></td> <td><b>Descripción</b></td></tr>';
				foreach ((array)$this->_module->getModelSingleton('Tracert')->getScriptPathApp() as $url) {
					echo '<tr><td width="20">'.$i++.'</td><td>'.$url['script'].'</td><td width="40">&nbsp;</td><td>'.$url['metodo'].'</td></tr>';
				}
				echo '</table><div>';
			}
		}
	}

}