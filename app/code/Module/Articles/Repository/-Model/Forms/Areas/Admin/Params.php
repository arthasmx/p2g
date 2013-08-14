<?php
class Module_Articles_Repository_Model_Forms_Areas_Admin_Params extends Core_Model_Repository_Model {

	public function get($detalle,$isPost=false) {

	require_once "Local/Form.php";
	$form=new Local_Form;
	$form->setAttribs(array('autocomplete'=>'off','enctype'=>'multipart/form-data','id'=>'art-edit-params','target'=>'artPicUpload','action'=> App::base('/uploader/article-upload')	));

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

		// Titulo
			$form->addElement('text', 'title', array(
					'label' => App::xlat('FORM_LABEL_title'),
					'required' => true,
					'validators' => array(
			            	array('stringLength', true, array(3))
			        ),
			        'onkeyup'=>"jQuery('input[name=seo]').val(genericos.seoCorrect(this.value))",
			        'class'=>'required largeInput',
			));
			//$form->getElement('title')->getDecorator('Element')->addClass('to-left rpad-10');

		// Seo
			$form->addElement('text', 'seo', array(
					'label' => App::xlat("FORM_LABEL_seo"),
					'required' => true,
		            'validators' => array(
		                	array('stringLength', true, array(3))
		            ),
		            'class'=>'required largeInput',
			));

		// Fecha de Publicacion
			$form->addElement('text', 'publicated', array(
					'label' => App::xlat("FORM_LABEL_publicated"),
					'description' => App::xlat("FORM_LABEL_publicated_desc"),
					'required' => true,
					'readonly' => true,
					'class'=>'required',
			));			

		// Seccion a mostrar el articulo (TYPE)
			$tmp= App::module('Categories')->getResource('Categories')->getChildren(2,$detalle['lang_id']);
			$tmp= App::module('Core')->getResource('Arrays')->toCombo($tmp,'id','name');
			$form->addElement('select', 'article_type_id'	,array(	'label' => App::xlat('FORM_LABEL_type'),
								        				'multiOptions' => $tmp,
														'required' => true,
														'class'=>'required',
														'onChange'=>'jsForms.loadSelectList(this.value,"category_id","/categories/ajax-combo")',
			));
			$tmp=false;
			if(isset($detalle['article_type_id']) && $detalle['article_type_id']>0){
				$tmp= App::module('Categories')->getResource('Categories')->getChildren($detalle['article_type_id'],$detalle['lang_id']);
				$tmp= App::module('Core')->getResource('Arrays')->toCombo($tmp,'id','name');
			}else{
				$tmp = array('0'=>App::xlat('FORM_LABEL_choose_section'));
			}
			// Categoria
			$form->addElement('select', 'category_id', array(
													'label' => App::xlat('FORM_LABEL_category'),
													'required' => true,
													'class'=>'required',
													'multiOptions' => $tmp,
			));

		// Seccion a mostrar el articulo (TYPE)
			$status= App::module('Core')->getResource('Status')->statusCombo();
			$form->addElement('select', 'status'	,array(	'label' => App::xlat('FORM_LABEL_status'),
														'description' => App::xlat('FORM_LABEL_status_desc'),
								        				'multiOptions' => $status,
														'required' => true,
														'class'=>'required'
			));

		// Boton fxBtn
			$form->addElement('fxbutton', 'close', array(
					'label' => App::xlat("FORM_LABEL_close")
					,'class' => 'fxBtn'
					,'icon' => 'bclose'
					,'onClick' => 'articles.closeDetail("artEdit");return false;'
			));
			$form->getElement('close')->getDecorator('Element')->addClass('to-left rpad-10');	

		// Boton GRABAR
			$form->addElement('fxbutton', 'saving', array(
					'label' => App::xlat("FORM_LABEL_save")
					,'class' => 'fxBtn'
					,'icon' => 'check'
					,'onClick' => 'articles.editParams("ok");return false;'
			));
			$form->getElement('saving')->getDecorator('Element')->addClass('to-left rpad-10');

		// Boton PREV
			$form->addElement('fxbutton', 'prev', array(
					'label' => App::xlat("FORM_LABEL_prev")
					,'class' => 'fxBtn'
					,'icon' => 'arrow-1-w'
					,'onClick' => 'articles.Prev();return false;'
			));
			$form->getElement('prev')->getDecorator('Element')->addClass('to-left rpad-10');

		// Boton NEXT
			$form->addElement('fxbutton', 'next', array(
					'label' => App::xlat("FORM_LABEL_next")
					,'class' => 'fxBtn'
					,'icon' => 'arrow-1-e'
					,'onClick' => 'articles.Next();return false;'
			));
			
$form->addDisplayGroup(
    array('title','seo','publicated','article_type_id','category_id','status','prev', 'close','saving','next'),
    'params'
);
$form->getDisplayGroup('params')->getDecorator('Group')->addClass('float-left');

		// Imagen representativa del articulo
			$form->addElement('file', 'picture', array(
					'label' => App::xlat("FORM_LABEL_image_art"),
					'description' => App::xlat("FORM_LABEL_image_art_desc")
			));

		// Boton SUBIR
			$form->addElement('fxbutton', 'upload', array(
					'label' => App::xlat("FORM_LABEL_upload")
					,'class' => 'fxBtn'
					,'icon' => 'arrowthick-1-ne'
					,'onClick' => 'articles.picture("imageTra-preview","art-edit-params","ok");return false;'
			));		

			$currentPix = false;
			//if(isset($detalle['picture']) && strlen($detalle['picture'])>0 ){
			if(isset($detalle['picture']) || isset($detalle['oldpicture']) ){
				if($isPost){$detalle['picture']=$detalle['oldpicture'];}
				$currentPix = "<img src='". App::www('/'.$this->_module->getConfig('core','article_imagelisting_folder').'/'.$detalle['picture']). "' alt='' title='' />";
			}else{
				$currentPix = "<img src='". App::www('/'.$this->_module->getConfig('core','article_imagelisting_folder').'/no-avatar.gif'). "' alt='' title='' />";
			}
			$form->getElement('upload')->getDecorator('AddHtml')->append('<div id="imageTra-preview"> <h1>'.App::xlat('PERFIL_avatar_preview').'</h1> '.$currentPix.' </div>');

$form->addDisplayGroup(
    array('picture','upload'),
    'pic'
);

		$form->populate($detalle);
		if(!$isPost){
			$form->populate(array('publicated' => App::module('Core')->getResource('Dates')->toDate(2,$detalle['publicated'])));
		}
		return $form;
	}	

}