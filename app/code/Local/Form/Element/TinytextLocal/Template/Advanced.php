<?php
require_once 'Local/Form/Element/TinytextLocal/Template.php';

class Local_Form_Element_TinytextLocal_Template_Advanced extends Local_Form_Element_TinytextLocal_Template {

	private $_advanced = Array (
								'theme' => 'advanced',
								'plugins' => 'morebreak,insertdatetime,preview,media,searchreplace,visualchars,nonbreaking,xhtmlxtras,template',
								'theme_advanced_buttons1' => 'bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,undo,redo,link,unlink',
								'theme_advanced_buttons1_add' => 'morebreak',
								'theme_advanced_buttons2' => 'cut,copy,paste|,search,replace,|,blockquote,|,insertdate,inserttime,preview,|,forecolor,backcolor,|,code',

								'theme_advanced_buttons3' => '',
								'theme_advanced_toolbar_location' => 'top',
								'theme_advanced_toolbar_align' => 'left',
								'theme_advanced_statusbar_location' => 'bottom',
								'theme_advanced_resizing' => false
								);

	public function get() {

		if  (	is_array($this->attribs)	){
			return $this->getJs();
		}
		else {
			$this->attribs  = $this->_advanced;
			return $this->getJs();
		}
	}

}

?>