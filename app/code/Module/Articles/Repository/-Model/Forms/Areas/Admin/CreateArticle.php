<?php
class Module_Articles_Repository_Model_Forms_Areas_Admin_CreateArticle extends Core_Model_Repository_Model {

	public function get($post=false) {

	require_once "Local/Form.php";
	$form=new Local_Form;
	$form->setAttrib('id','create-article-form');

		// Draft actual. Al inicio es = FALSE, cuando se guarda por primera vez, se actualiza su valor al ID 
		// que le corresponde en tabla "articles_drafts"
		$form->addElement('hidden', 'draft', array(
				'class'=>'hidden',
				'value'=> 0 //!$post? '0' :$post['id']
		));

		// La foto actual
		$form->addElement('hidden', 'picture', array(
				'class'=>'hidden',
				'value'=>'no-avatar.gif' //!$post? 'no-avatar.gif' :$post['picture']
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
			$form->getElement('title')->getDecorator('Element')->addClass('float-left rpad-10');
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
			$form->getElement('seo')->getDecorator('Element')->addClass('float-left');

		// Seccion a mostrar el articulo (TYPE)
			$tmp= App::module('Categories')->getResource('Categories')->getChildren(2,1);
			$tmp= App::module('Core')->getResource('Arrays')->toCombo($tmp,'id','name');
			$form->addElement('select', 'article_type_id'	,array(	
					'label' => App::xlat('FORM_LABEL_type'),
					// 'description' => App::xlat('FORM_LABEL_type_desc'),
					'multiOptions' => $tmp,
					'required' => true,
					'class'=>'required',
					'onChange'=>'jsForms.loadSelectList(this.value,"category_id","/categories/ajax-combo")',
			));
			$form->getElement('article_type_id')->getDecorator('Element')->addClass('float-left');
			$tmp=false;
			if(isset($post['article_type_id']) && @$post['article_type_id']>0){
				$tmp= App::module('Categories')->getResource('Categories')->getChildren(@$post['article_type_id'],$post['languages']);
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
			$form->getElement('category_id')->getDecorator('Element')->addClass('float-left');

		// Fecha de Publicacion
			$form->addElement('text', 'publicated', array(
					'label' => App::xlat("FORM_LABEL_publicated"),
					'description' => App::xlat('FORM_LABEL_publicated_desc'),
					'required' => true,
					'readonly' => true,
					'class'=>'required',
			));

/******************************************************************************************************/
// El articulo
			
			
		require_once 'Xplora/Form/Element/Ckeditor/Template/CreateArticle.php';
		$form->addElement('CkEditor', 'article', array(	
													'required' => true,
													'validators' => array(array('stringLength', false, array(6)))
													,'style' => new Xplora_Form_Element_Ckeditor_Template_CreateArticle()
												));

		// Boton RESET
			$form->addElement('reset', 'resetBtn', array(
					'label' => App::xlat("FORM_LABEL_reset")
					,'class' => 'fxBtn'
					//,'icon' => 'check'
					//,'onClick' => 'articles.createArticleReset();return false;'
			));
			$form->getElement('resetBtn')->getDecorator('Element')->addClass('float-left');

		// Boton GRABAR
			$form->addElement('fxbutton', 'saving', array(
					'label' => App::xlat("FORM_LABEL_save")
					,'class' => 'fxBtn'
					,'icon' => 'check'
					,'onClick' => 'articles.createArticle("ok");return false;'
			));
			$form->getElement('saving')->getDecorator('Element')->addClass('float-left');
			
		$form->addDisplayGroup(
		    array('languages','title','seo','article_type_id','category_id','publicated','article','prev','resetBtn','saving'),
		    'create_articles'
		);

		if($post){
			$form->populate(array(
				'publicated' => App::module('Core')->getResource('Dates')->toDate(2,$post['publicated']),
				'draft'=>$post['id'],
				'article_type_id'=>$post['type'],
				'category_id'=>$post['category'],
				'languages'=>$post['lang_id'],
				'title'=>$post['title'],
				'seo'=>$post['seo'],
				'article'=>$post['article']
			));
		}

		return $form;
	}

}