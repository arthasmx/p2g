<?php
class Module_Articles_Repository_Model_Forms_Areas_Admin_DraftParams extends Core_Model_Repository_Model {

	public function get($post=false) {

	require_once "Local/Form.php";
	$form=new Local_Form;
	$form->setAttribs(array('autocomplete'=>'off','id'=>'create-params-form'));

		// La foto actual
		$form->addElement('hidden', 'picture', array(
				'class'=>'hidden',
				'value'=> 'no-avatar.gif'
		));

/******************************************************************************************************/
// Parametros

		// Idioma en el cual sera escrito el articulo
			$lang=App::module('Core')->getResource('Languages')->sessionLanguages();
			$form->addElement('select', 'languages'	,array(	
						'label' => App::xlat('FORM_LABEL_lang'),
						'description'=>App::xlat('FORM_LABEL_lang_desc'),
						'multiOptions' => $lang,
						'required' => true,
						'class'=>'required'
			));

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

		// Seccion a mostrar el articulo (TYPE)
			$lang=App::module('Core')->getResource('Languages')->sessionLanguageByPrefix( App::locale()->getLang() );
			$tmp= App::module('Categories')->getResource('Categories')->getChildren(2,$lang['id']);
			$tmp= App::module('Core')->getResource('Arrays')->toCombo($tmp,'id','name');
			$form->addElement('select', 'article_type_id'	,array(	
					'label' => App::xlat('FORM_LABEL_type'),
					'description' => App::xlat('FORM_LABEL_type_desc'),
					'multiOptions' => $tmp,
					'required' => true,
					'class'=>'required',
					'onChange'=>'jsForms.loadSelectList(this.value,"category_id","/categories/ajax-combo")',
			));
			$tmp=false;
			if(isset($post['type']) && @$post['type']>0){
				$tmp= App::module('Categories')->getResource('Categories')->getChildren(@$post['type'],$post['lang_id']);
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

		// Boton CANCELAR
			$form->addElement('fxbutton', 'close', array(
					'label' => App::xlat("FORM_LABEL_cancel")
					,'class' => 'fxBtn'
					,'icon' => 'bclose'
					,'onClick' => 'articles.closeDetail("artCreate");return false;'
			));
			$form->getElement('close')->getDecorator('Element')->addClass('to-left rpad-10');	

		// Boton NEXT
			$form->addElement('fxbutton', 'next', array(
					'label' => App::xlat("FORM_LABEL_next")
					,'class' => 'fxBtn'
					,'icon' => 'check'
					,'onClick' => 'articles.createParams("ok");return false;'
			));

$form->addDisplayGroup(
    array('languages','title','seo','publicated','article_type_id','category_id','close','next'),
    'params'
);

		// Si no es POST, pondremos la fecha actual para el campo PUBLICATED
			$form->populate(array(
				'languages'=>$post['lang_id'],
				'title'=>$post['title'],
				'seo'=>$post['seo'],
				'publicated' => App::module('Core')->getResource('Dates')->toDate(2,$post['publicated']),
				'article_type_id'=>$post['type'],
				'category_id'=>$post['category'] 
			));

		return $form;
	}	

}