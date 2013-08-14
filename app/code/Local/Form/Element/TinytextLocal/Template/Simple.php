<?php
require_once 'Local/Form/Element/TinytextLocal/Template.php';

class Local_Form_Element_TinytextLocal_Template_Simple extends Local_Form_Element_TinytextLocal_Template {

	public function get() {

		if  (	is_array($this->attribs)	){
			return $this->getJs();
		}
		else {
			return '
				theme 					: "advanced",
				theme_advanced_buttons1 : "bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,undo,redo,link,unlink",
				theme_advanced_buttons2 : "",

				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true
			';
		}
	}

}

?>