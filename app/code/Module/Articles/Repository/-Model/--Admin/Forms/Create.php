<?php

class Module_Blog_Repository_Model_Admin_Forms_Create extends Core_Model_Repository_Model {

	public function get() {

		require_once "Local/Form.php";
		$form=new Local_Form;
		$form->setAttribs(array('autocomplete'=>'off'));

		// Nuevo Scase

				$form->addElement(
					'text',
					'id',
					array(
						'label' => App::xlat('Código del caso'),
						'description' => App::xlat('Id único para diferenciar el caso. (Ejemplos: pcmod, nutrigym...)'),
						'required' => true,
						'validators' => array(
				            	array('stringLength', true, array(4)),
				        ),
				        'size'=>15,
						'maxLength'=>10,
				  	)
				);
				$form->getElement('id')->getDecorator('Element')->addClass('bg');

				$form->addElement(
					'text',
					'date_publish',
					array(
						'label' => App::xlat('Fecha de publicación'),
						'description' => App::xlat('Fecha en la que se publicará el caso. DD/MM/AAAA'),
						'required' => true,
						'validators' => array(
				            	array('stringLength', true, array(4)),
				        ),
				        'size'=>15,
						'maxLength'=>10,
						'value'=>App::locale()->toDate(date('Y-m-d')),
				  	)
				);
				$form->getElement('date_publish')->getDecorator('Element')->addClass('bg');

			// Titulo del articulo
				$form->addElement(
					'text',
					'title',
					array(
						'label' => App::xlat('Titulo del articulo'),
						'description' => App::xlat('Describa los mejor posible a su articulo ya que este nombre sera utilizado como identificador.'),
						'required' => true,
						'validators' => array(
				            	array('stringLength', true, array(4))
				        ),
				        'size'=>40,
				  	)
				);
				$form->getElement('title')->getDecorator('Element')->addClass('bg');				
				
				$form->addElement(
					'text',
					'logo',
					array(
						'label' => App::xlat('Logotipo'),
						'description' => App::xlat('Nombre del archivo que contiene el logotipo, incluya la extensión. (Sólo archivos gif de 150x60).<br/>Ejemplo: pcmod.gif, nutrigym.gif...'),
						'required' => true,
						'validators' => array(
				            	array('stringLength', true, array(4))
				        ),
				        'size'=>30,
				  	)
				);
				$form->getElement('logo')->getDecorator('Element')->addClass('bg');

				 // Configuración del campo tipo TinyMCE                
				require_once 'Xplora/Form/Element/TinyMce/Template/Advanced.php';
				$tinyMceTpl=new Xplora_Form_Element_TinyMce_Template_Advanced(); // Crea la plantilla, acepta un array de propiedades y subpropiedades que sobreescribirá si las encuentra
				$tinyMceTpl->add("plugins","morebreak"); // Añade una subpropiedad de una propiedad
				$tinyMceTpl->add("theme_advanced_buttons1",array("separator","morebreak")); // Añade un grupo de subpropiedades a una propiedad
				$tinyMceTpl->addTableControls(); // Método de las plantillas que incluyen todos los controles necesarios para manejar tablas
				//$tinyMceTpl->del("theme"); // Elimina una propiedad
				//$tinyMceTpl->del("theme_advanced_buttons1","redo"); // Elimina una subpropiedad de una propiedad
				$form->addElement('tinyMce', 'article', array(	'label' => App::xlat('Articulo:'),
															'cols' => 80,
															'rows' => 16,
															'required' => true,
															'validators' => array(array('stringLength', false, array(6))),
															'etc' => $tinyMceTpl
														));
                $form->getElement('article')->getDecorator('Element')->addClass('bg');
                 
            require_once 'Xplora/Form/Element/TinyMce/Template/Simple.php';
            $form->addElement(    'tinyMce', 'article', array(    'label' => App::xlat("Añadir comentario"),
                                'description' => 'Escriba un comentario, recomendación o sugerencia sobre este pedido.',
                                'rows' => 8,
                                'cols' => 50,
                                'validators' => array(array('stringLength', false, array(6))),
                                'etc' => new Xplora_Form_Element_TinyMce_Template_Simple()
                            ));
                 

		// Cambiar tipo
			$url=App::base("/articulos/blog/index");
			$form->addElement('button','back',array(
														'label' => "<< ".App::xlat("Cancelar"),
														'onclick' => 'document.location.href="'.$url.'"',
			 										)
			);
			$form->getElement('back')->getDecorator('Element')->addClass('float-left back');


		// Submit
			$form->addElement('submit','submit',array( 'label' => App::xlat(App::xlat('Añadir articulo'))." >>" ));

			$form->addDisplayGroup(
				array(
					'back','submit',
				),
				'buttons'
			);
			$form->getDisplayGroup('buttons')->getDecorator('Group')->addClass('clear-left');

		return $form;
	}


}