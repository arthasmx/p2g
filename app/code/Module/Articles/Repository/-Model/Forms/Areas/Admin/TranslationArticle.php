<?php
class Module_Articles_Repository_Model_Forms_Areas_Admin_TranslationArticle extends Core_Model_Repository_Model {

	public function get($parent,$articulo,$lang,$article) {

	require_once "Local/Form.php";
	$form=new Local_Form;
	$form->setAttrib('id','article-edit-form');

		require_once 'Xplora/Form/Element/Ckeditor/Template/TranslateArticle.php';
		$form->addElement('CkEditor', 'article', array(	
													'required' => true,
													'validators' => array(array('stringLength', false, array(6)))
													,'style' => new Xplora_Form_Element_Ckeditor_Template_TranslateArticle()
												));

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
												
		$form->addDisplayGroup(
		    array('article','prev','close'),
		    'newarticle_parametros'
		);

		//$form->populate($article);
		return $form;
	}

}