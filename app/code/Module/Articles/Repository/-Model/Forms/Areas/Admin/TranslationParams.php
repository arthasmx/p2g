<?php
class Module_Articles_Repository_Model_Forms_Areas_Admin_TranslationParams extends Core_Model_Repository_Model {

	public function get($parent,$idioma,$post=false) {

	require_once "Local/Form.php";
	$form=new Local_Form;
	$form->setAttribs(array('autocomplete'=>'off','id'=>'art-tran-params'));

		// Padre
		$form->addElement('hidden', 'article_id', array(
				'class'=>'hidden'
				,'value'=>$parent
		));
		// Articulo
		$lang=App::module('Core')->getResource('Languages')->sessionLanguageByPrefix($idioma);
		$form->addElement('hidden', 'lang', array(
				'class'=>'hidden'
				,'value'=>$idioma
		));

		$form->addElement('hidden', 'lang_id', array(
				'class'=>'hidden'
				,'value'=>$lang['id']
		));
		
/******************************************************************************************************/
// Parametros

		// Titulo
			$form->addElement('text', 'title', array(
					'label' => App::xlat('FORM_LABEL_title'),
					'description' => App::xlat('FORM_LABEL_title_desc'),
					'required' => true,
					'validators' => array(
			            	array('stringLength', true, array(3))
			        ),
			        'onkeyup'=>"jQuery('input[name=seo]').val(genericos.seoCorrect(this.value))",
			        'class'=>'required largeInput',
			));

		// Seo
			$form->addElement('text', 'seo', array(
					'label' => App::xlat("FORM_LABEL_seo"),
					'description' => App::xlat('FORM_LABEL_seo_desc'),
					'required' => true,
		            'validators' => array(
		                	array('stringLength', true, array(3))
		            ),
		            'class'=>'required largeInput',
			));

		// Fecha de Publicacion
			$form->addElement('text', 'publicated', array(
					'label' => App::xlat("FORM_LABEL_publicated"),
					'description' => App::xlat('FORM_LABEL_publicated_desc'),
					'required' => true,
					'readonly' => true,
					'class'=>'required',
			));			

		// Seccion
			$lang=App::module('Core')->getResource('Languages')->sessionLanguageByPrefix(App::locale()->getLang());
			$tmp= App::module('Categories')->getResource('Categories')->getChildren(2,$lang['id']);
			$tmp= App::module('Core')->getResource('Arrays')->toCombo($tmp,'id','name');
			$form->addElement('select', 'article_type_id'	,array(	'label' => App::xlat('FORM_LABEL_type'),
														'description' => App::xlat('FORM_LABEL_type_desc'),
								        				'multiOptions' => $tmp,
														'required' => true,
														'class'=>'required',
														'onChange'=>'jsForms.loadSelectList(this.value,"category_id","/categories/ajax-combo","'.$lang['id'].'")',
			));
			$tmp=false;

			// Si no se ha hecho post, debemos cargar el TIPO y CATEGORIA utilizados por el articulo PARENT, sin importar el idioma, pues recuerda
			// que al modificar TIPO y CATEGORIA, afecta a TODO el articulo, no solo al del idioma actual
			if(!$post){
				$parentData=$this->_module->getResource('Article')->getParentData($parent);
			}

			if(isset($post['article_type_id']) && $post['article_type_id']>0){
				$tmp= App::module('Categories')->getResource('Categories')->getChildren($post['article_type_id'],$lang['id']);
				$tmp= App::module('Core')->getResource('Arrays')->toCombo($tmp,'id','name');
			}else{
				//$tmp = array('0'=>App::xlat('FORM_LABEL_choose_section'));
				$tmp= App::module('Categories')->getResource('Categories')->getChildren($parentData['type'],$lang['id']);
				$tmp= App::module('Core')->getResource('Arrays')->toCombo($tmp,'id','name');
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

		// Boton CANCEL
			$form->addElement('fxbutton', 'close', array(
					'label' => App::xlat("FORM_LABEL_close")
					,'class' => 'fxBtn'
					,'icon' => 'bclose'
					,'onClick' => 'articles.closeDetail("artTrans");return false;'
			));
			$form->getElement('close')->getDecorator('Element')->addClass('to-left rpad-10');	

		// Boton SAVE
			$form->addElement('fxbutton', 'save', array(
					'label' => App::xlat("FORM_LABEL_savenext")
					,'class' => 'fxBtn'
					,'icon' => 'arrow-1-e'
					,'onClick' => 'articles.transParams("true");return false;'
			));
			$form->getElement('save')->getDecorator('Element')->addClass('to-left rpad-10');

$form->addDisplayGroup(
    array('title','seo','publicated','article_type_id','category_id','status','close','save','next'),
    'artTranParams'
);
$form->getDisplayGroup('artTranParams')->getDecorator('Group')->addClass('float-left');

		// Si no se ha hecho post, debemos cargar el TIPO y CATEGORIA utilizados por el articulo PARENT, sin importar el idioma, pues recuerda
		// que al modificar TIPO y CATEGORIA, afecta a TODO el articulo, no solo al del idioma actual
		if(!$post){
			$form->populate(array(
				'article_type_id' => $parentData['type'],
				'category_id' => $parentData['category'],
				'publicated' => App::module('Core')->getResource('Dates')->toDate(2,date('Y-m-j')),
				'status' => 1
			));
		}

		return $form;
	}	

}