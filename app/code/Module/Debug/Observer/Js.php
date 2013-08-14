<?php
class Module_Debug_Observer_Js extends Core_Model_Module_Observer {

	public function init() {}

	public function dispatch($options=array()) {
		if (App::$allowDebugRender) {
			App::header()->addScript(App::jslib('/debug.js'));
		}
	}

}