<?php
class Module_Articles_Repository_Model_Forms_Areas_Admin_Edit extends Core_Model_Repository_Model {

	public function get($parent,$articulo,$lang,$article) {

	require_once "Local/Form.php";
	$form=new Local_Form;
	$form->setAttrib('id','article-edit-form');

		// Reemplazamos el path de las imagenes del articulo
//			if(!$post){
				//$detalle_articulo['article']=str_replace("%url%",'../media/articulos/',$detalle_articulo['article']);
//				$detalle_articulo['article']=str_replace("% agregar mas informacion %",'% agregar mas informacion %',$detalle_articulo['article']);
//			}else{
//				$detalle_articulo['article']=str_replace("../media/articulos/",'%url%',$detalle_articulo['article']);
//			}

		require_once 'Xplora/Form/Element/Ckeditor/Template/EditArticle.php';
		$form->addElement('CkEditor', 'article', array(	
													'required' => true,
													'validators' => array(array('stringLength', false, array(6)))
													,'style' => new Xplora_Form_Element_Ckeditor_Template_EditArticle()
												));

		// Boton PREV
			$form->addElement('fxbutton', 'prev', array(
					'label' => App::xlat("FORM_LABEL_prev")
					,'class' => 'fxBtn top-margin-10'
					,'icon' => 'arrow-1-w'
					,'onClick' => 'articles.Prev();return false;'
			));
			$form->getElement('prev')->getDecorator('Element')->addClass('to-left rpad-10');

		// Boton CANCEL
			$form->addElement('fxbutton', 'close', array(
					'label' => App::xlat("FORM_LABEL_close")
					,'class' => 'fxBtn top-margin-10'
					,'icon' => 'bclose'
					,'onClick' => 'articles.closeDetail("artEdit");return false;'
			));
			$form->getElement('close')->getDecorator('Element')->addClass('to-left rpad-10');

		$form->addDisplayGroup(
		    array('article','prev','close'),
		    'editarticle_fieldset'
		);

		$form->populate($article);
		return $form;
	}

}