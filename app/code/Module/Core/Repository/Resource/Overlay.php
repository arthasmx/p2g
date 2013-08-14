<?php
class Module_Core_Repository_Resource_Overlay extends Core_Model_Repository_Resource {

	/**
	 * Regresa los templates para los overlays y optimizar el renderizado de la vista
	 */
	public function overlay($overlays=false){
		if (!is_array($overlays) || !count($overlays)>0) return false;

		// Por cada configuracion, creamos un overlay
		foreach($overlays AS $key=>$overlay){

			echo '<div id="fxOverlay-'.$key.'" style="display:none;">'
					.'<div id="overlay" style="width:'.$overlay['width'].'px; height:'.$overlay['height'].'px;">'
						.'<div class="topbar-close">'
							.'<span class="overlay-topbar" id="'.$key.'-topbar">' 
								.'<span id="'.$key.'-topbar-title" class="topbar-title" style="width:'.($overlay['width']-66).'px;">'.$overlay['winTitle'].'</span>'
								.'<div id="'.$key.'-miniwin" class="mini" alt="'.App::xlat('OVERLAY_WIN_CONTROL_MIN').'" title="'.App::xlat('OVERLAY_WIN_CONTROL_MIN').'"></div>'
								.'<div id="'.$key.'-hiderwin" class="hider" alt="'.App::xlat('OVERLAY_WIN_CONTROL_HIDE').'" title="'.App::xlat('OVERLAY_WIN_CONTROL_HIDE').'"></div>'
								.'<div class="close" alt="'.App::xlat('OVERLAY_WIN_CONTROL_CLOSE').'" title="'.App::xlat('OVERLAY_WIN_CONTROL_CLOSE').'"></div>' 
							.'</span>'
						.'</div>'
						.'<div class="content-close">'
							.'<div id="'.$key.'-content" class="overlay-content">'
								.'<div class="ajax-temp-image">'
								 	.'<img src="'.App::skin('/art/generic/icons/ajax-loading-015.gif').'" alt="" title="" />'
								.'</div>'
							.'</div>'
						.'</div>'
						.'<div class="bottombar-close">'
							.'<span id="'.$key.'-bottombar" class="overlay-bottombar">'
							.'</span>'
						.'</div>'
					.'</div>'
				.'</div>';

		}

	}

}