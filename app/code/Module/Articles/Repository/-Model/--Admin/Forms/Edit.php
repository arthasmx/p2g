<?php

class Module_Blog_Repository_Model_Admin_Forms_Edit extends Core_Model_Repository_Model {


	public function get( $idScase ) {

		// Sacamos los datos del caso de exito
			$blog=$this->_module->getModelSingleton('blog')->get( $idScase,false );
			$blog = $blog[0];
			//echo '<pre>'; print_r($blog); echo '</pre>'; exit;
			if (!$blog) $this->_module->exception( App::xlat(' No tenemos registrado el id <b>%s</b> como un caso de éxito. ',$idScase),404 );

		require_once "Local/Form.php";
		$form=new Local_Form;
		$form->setAttribs(array('autocomplete'=>'off'));

		// Revisamos si ID es valido
			//$this->view->entidad  = mysql_escape_string($this->getRequest()->getParam('id'));
			//if ( !App::module('Entidad')->getModelSingleton('Entidad')->setPropietario( $this->view->city )->get( $this->view->entidad ) ) $this->_module->exception( App::xlat('%s no existe. Utiliza nuestros links por favor',$this->view->entidad),404 );

		// Editando Articulo
				$form->addElement(
					'text',
					'date_publish',
					array(
						'label' => App::xlat('Fecha de publicación'),
						'description' => App::xlat('Fecha a publicar este caso. DD / MM / AAAA'),
						'required' => true,
						'validators' => array(
				            	array('stringLength', true, array(4)),
				        ),
				        'size'=>15,
						'maxLength'=>10,
						'value'=> App::locale()->toDate($blog['date_publish'],'medium'),
				  	)
				);
				$form->getElement('date_publish')->getDecorator('Element')->addClass('bg');

			// Editando el titulo del articulo
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
				        'value' => $blog['title'],
				  	)
				);
				$form->getElement('title')->getDecorator('Element')->addClass('bg');
				
				
				$form->addElement(
					'text',
					'logo',
					array(
						'label' => App::xlat('Logotipo'),
						'description' => App::xlat('Nombre del archivo que contiene el logotipo, incluya la extensión. (Sólo archivos gif de 150x60).<br/>Ejemplo: pcmod.gif, nutrigym.gif...'),
						//'onChange' => 'ajaxUpload("'.App::base("/entidad/ajax/goup").'")',
						//generateimage(this.options[this.selectedIndex].value)
						'required' => true,
						'validators' => array(
				            	array('stringLength', true, array(4))
				        ),
				        'size'=>20,
				        'value' => $blog['logo'],
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
				$form->addElement('tinyMce', 'article', array(	'label' => App::xlat('Contenido del caso:'),
															'cols' => 80,
															'rows' => 16,
															'required' => true,
															'validators' => array(array('stringLength', false, array(6))),
															'value' =>$blog['article'],
															'etc' => $tinyMceTpl
														));

                $form->getElement('article')->getDecorator('Element')->addClass('bg');


		// Cambiar tipo
			$url=App::base("/articulos/blog/index");
			$form->addElement('button','back',array(
														'label' => "<< ".App::xlat("Cancelar"),
														'onclick' => 'document.location.href="'.$url.'"',
			 										)
			);
			$form->getElement('back')->getDecorator('Element')->addClass('float-left back');


		// Submit
			$form->addElement('submit','submit',array( 'label' => App::xlat(App::xlat('Guardar cambios'))." >>" ));
			//$form->getElement('submit')->getDecorator('Element')->addClass('clear-left');


			$form->addDisplayGroup(
				array(
					'back','submit',
				),
				'buttons'
			);
			$form->getDisplayGroup('buttons')->getDecorator('Group')->addClass('clear-left');

			$form->addElement(
				'hidden',
				'id',
				array('value'=>$blog['id'])
			);


		// Si hago populate, no puedo poner la fecha a modificar....
		//		$form->populate($blog);

		return $form;
	}


}