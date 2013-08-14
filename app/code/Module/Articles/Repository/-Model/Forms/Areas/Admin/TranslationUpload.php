<?php
class Module_Articles_Repository_Model_Forms_Areas_Admin_TranslationUpload extends Core_Model_Repository_Model {

	public function get($detalle,$isPost=false) {

	require_once "Local/Form.php";
	$form=new Local_Form;
	$form->setAttribs(array('autocomplete'=>'off','enctype'=>'multipart/form-data','id'=>'artTra-picture','target'=>'artTraPicUpload','action'=> App::base('/uploader/article-upload?nocache='.gmmktime())	));

		// Padre
		$form->addElement('hidden', 'article_id', array(
				'class'=>'hidden'
		));
		// Articulo
		$form->addElement('hidden', 'id', array(
				'class'=>'hidden'
		));
		// La foto actual
		$form->addElement('hidden', 'oldpicture', array(
				'class'=>'hidden',
				'value'=> !$isPost?$detalle['picture']:$detalle['oldpicture']
		));

/******************************************************************************************************/
// Parametros

		// Imagen representativa del articulo
			$form->addElement('file', 'picture', array(
					'label' => App::xlat("FORM_LABEL_image_art"),
					'description' => App::xlat("FORM_LABEL_image_art_desc")
			));

			$currentPix = false;
			if(isset($detalle['picture']) || isset($detalle['oldpicture']) ){
				if($isPost){$detalle['picture']=$detalle['oldpicture'];}
				$currentPix = "<img src='". App::www('/'.$this->_module->getConfig('core','article_imagelisting_folder').'/'.$detalle['picture']). "' alt='' title='' />";
			}else{
				$currentPix = "<img src='". App::www('/'.$this->_module->getConfig('core','article_imagelisting_folder').'/no-avatar.gif'). "' alt='' title='' />";
			}

		// Boton PREV
			$form->addElement('fxbutton', 'prev', array(
					'label' => App::xlat("FORM_LABEL_prev")
					,'class' => 'fxBtn'
					,'icon' => 'arrow-1-w'
					,'onClick' => 'articles.translationPrev();return false;'
			));
			$form->getElement('prev')->getDecorator('Element')->addClass('to-left rpad-10');

		// Boton CANCEL
			$form->addElement('fxbutton', 'close', array(
					'label' => App::xlat("FORM_LABEL_close")
					,'class' => 'fxBtn'
					,'icon' => 'bclose'
					,'onClick' => 'articles.closeDetail("artTrans");return false;'
			));
			$form->getElement('close')->getDecorator('Element')->addClass('to-left rpad-10');	

		// Boton SUBIR
			$form->addElement('fxbutton', 'upload', array(
					'label' => App::xlat("FORM_LABEL_upload")
					,'class' => 'fxBtn'
					,'icon' => 'arrowthick-1-ne'
					,'onClick' => 'articles.transUpload("justdoit");return false;'
			));
			$form->getElement('upload')->getDecorator('Element')->addClass('to-left rpad-10');		

		// Boton NEXT
			$form->addElement('fxbutton', 'next', array(
					'label' => App::xlat("FORM_LABEL_next")
					,'class' => 'fxBtn'
					,'icon' => 'arrow-1-e'
					,'onClick' => 'articles.translationNext();return false;'
			));

		$form->getElement('next')->getDecorator('AddHtml')->append('<div id="imageTra-preview"> <h1>'.App::xlat('PERFIL_avatar_preview').'</h1> '.$currentPix.' </div>');
			
$form->addDisplayGroup(
    array('picture','prev','close','upload','next'),
    'thefile'
);

		$form->populate($detalle);
		return $form;
	}	

}