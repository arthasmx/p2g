<?php

require_once "Core/Form.php";

/**
 * Clase Local_Form
 *
 * Extiende a Core_Form, Xplora_Form y Zend_Form y modifica su constructor para añadir personalizaciones locales
 * (como rutas adicionales para Elementos y Decoradores)
 *
 * Adicionalmente, para poder hacer flotar algunos campos del formulario necesitamos realizar modificaciones a los decoradores del formulario y de los elementos.
 * Por defecto, el formulario renderiza una lista de definiciones (dl) con un (dt) para las etiquetas de los campos y un (dd) para el campo en sí.
 * Vamos a modificar el constructor para que
 *
 */
class Local_Form extends Core_Form {

    /**
     * Constructor para formatear los formularios de forma personalizada
     *
     * ---------------------------------------------------
     *
     * @param mixed $options
     * @return void
     */
	public function __construct($options = null)  {
    	// Cosntruye recursivamente Core_Form, Xplora_Form y Zend_Form
		// Añade las rutas /Local/Form, /lib/Xplora/Form y lib/Zend/Form
    		parent::__construct($options);

    	// Añadimos el traductor por defecto que usará el locale /locale/[lang]/form.csv
    		$this->setTranslator(App::translate()->getFormTranslator());

    	// Modificamos el decorador HtmlTag del formulario para cambiar el tag a ul (en lugar de dl)
    		$this->getDecorator('FormElements')->setOption('separator',PHP_EOL.PHP_EOL);
    		$this->addDecorator('Form', array('class' => 'local_form'));
    		$this->removeDecorator('HtmlTag');

		// Aqui podemos añadir configuración común del formulario para todos los de la aplicación,
		// opciones de los decoradores, elementos, etc.

		/*
			echo "<pre>";
				print_r($this->getPluginLoader('Decorator')->getPaths());
			echo "</pre>";
		*/

		//App::header()->addLink(App::skin('/css/pages/form.css'),array('rel'=>'stylesheet','type'=>'text/css')); // Ya no carga dinamicamente, se agregará siempre
    }

    /**
     *
     * Modificaciones para formatear los formularios de forma personalizada.
     *
     *
     * ------------------------------------------------------------
     *
     * Add a new element
     *
     * $element may be either a string element type, or an object of type
     * Zend_Form_Element. If a string element type is provided, $name must be
     * provided, and $options may be optionally provided for configuring the
     * element.
     *
     * If a Zend_Form_Element is provided, $name may be optionally provided,
     * and any provided $options will be ignored.
     *
     * @param  string|Zend_Form_Element $element
     * @param  string $name
     * @param  array|Zend_Config $options
     * @return Zend_Form
     */
    public function addElement($element, $name = null, $options = null) {

    	// Creamos el elemento con el método addElement de Zend_Form
    		if (is_object($element)) {
	        	$name=$element->getName();
    		}
        	parent::addElement($element,$name,$options);
        	$currentElement=$this->getElement($name);
       		$currentElementType=strtolower(str_replace(array('Local_Form_Element_','Xplora_Form_Element_','Zend_Form_Element_'),'',$currentElement->getType()));

       	// Añadimos el validador custom para poder enviar mensajes de error personalizados
       		$currentElement->addValidator('custom');

       	// Añadimos el filtro stringTrim para eliminar espacios innecesarios
       		$currentElement->addFilter('stringTrim');

       	// Añadimos decoradores o establecemos los decoradores por defecto (En este caso no eliminamos los decoradores que ya existen
       	// debido a que algunos elementos no tienen los mismos decoradores que otros
       		// Opciones de decoradores que deben tener todos los elementos

       			$currentElement->getDecorators(); // Increible, pero si no pongo este FIX el decorador de las unidades sale fuera del dl.

	       		$currentElement->addDecorator('ViewHelper', array('separator'=>''));

	       		$currentElement->addDecorator('Unit', array('tag' => 'small', 'placement' => "APPEND" ));

	       		$currentElement->addDecorator('MergeElement', array('placement' => "APPEND", 'form'=>&$this));

	       		$currentElement->addDecorator('AddHtml', array('tag' => 'span', 'placement' => "APPEND" ));

	       		if ($decorator=$currentElement->getDecorator('Errors')) {
	       			// Sólo realizamos ésta acción si existe el decorador Errors
	       				$currentElement->removeDecorator('Errors');
	       				$currentElement->addDecorator('Errors');
	       		}

	       		if ($decorator=$currentElement->getDecorator('HtmlTag')) {
	       			// Sólo realizamos ésta acción si existe el decorador HtmlTag
	       			// Lo quitamos porque vamos a meter antes el decorador de unidades. Despues añadimos nuevamente el decorador
		       			$currentElement->removeDecorator('HtmlTag');
	       				$currentElement->addDecorator('HtmlTag', array('tag' => 'dd', 'class' => 'field','separator'=>PHP_EOL ));
	       		}

	       		if ($decorator=$currentElement->getDecorator('Label')) {
	       			// Sólo realizamos ésta acción si existe el decorador Label, hay elementos (como los botones) que no lo tienen.
	       				$currentElement->removeDecorator('Label');
	       				$currentElement->addDecorator('Label',array( 'tag'=> 'dt', 'escape'=>false, 'tagclass'=> 'label', 'separator'=>PHP_EOL ));
	       		}



	       		$currentElement->addDecorator('Description' , array('tag'=>'dd', 'escape'=>false));

	       		$currentElement->addDecorator(array('Element'=>'HtmlTag'), array( 'tag' => 'dl', 'separator'=>PHP_EOL, 'class' => 'element field-'.$currentElement->getName().' type-'.$currentElementType ));

        return $this;
    }

    /**
     *
     * Modificaciones para formatear los formularios de forma personalizada.
     *
     * ------------------------------------------------------------
     *
     * Add a display group
     *
     * Groups named elements for display purposes.
     *
     * If a referenced element does not yet exist in the form, it is omitted.
     *
     * @param  array $elements
     * @param  string $name
     * @param  int $order
     * @return Zend_Form
     * @throws Zend_Form_Exception if no valid elements provided
     */
    public function addDisplayGroup(array $elements, $name, $options = null) {

	// Creamos el elemento con el método addDisplayGroup de Zend_Form
		parent::addDisplayGroup($elements,$name,$options);

	// Opciones de decoradores que debe tener el grupo
		$displayGroup=$this->getDisplayGroup($name);
		if ($displayGroup) {
			$displayGroup->clearDecorators();
				$displayGroup->addDecorator('FormElements')
				//->addDecorator('HtmlTag', array('tag' => 'ul','separator'=>PHP_EOL))
				->addDecorator('Description' , array('tag'=>'div', 'escape'=>false, 'placement'=>'PREPEND'))
				->addDecorator(array('Group'=>'Fieldset'), array('class' => 'group '.$displayGroup->getName(), 'separator'=>PHP_EOL) );
				//->addDecorator(array('Element'=>'HtmlTag'), array( 'tag' => 'li', 'class' => 'group '.$displayGroup->getName(), 'separator'=>PHP_EOL ) );
		}


        return $this;
    }


public function nested_fieldset($element, $name, $options = null) {

  // Creamos el elemento con el método addDisplayGroup de Zend_Form
  // parent::addDisplayGroup($elements,$name,$options);
/*
  // Opciones de decoradores que debe tener el grupo
  $displayGroup=$this->getDisplayGroup($name);
  if ($displayGroup) {
    $displayGroup->clearDecorators();
    $displayGroup->addDecorator('FormElements')
    //->addDecorator('HtmlTag', array('tag' => 'ul','separator'=>PHP_EOL))
    ->addDecorator('Description' , array('tag'=>'div', 'escape'=>false, 'placement'=>'PREPEND'))
    ->addDecorator(array('Group'=>'Fieldset'), array('class' => 'group '.$displayGroup->getName(), 'separator'=>PHP_EOL) );
    //->addDecorator(array('Element'=>'HtmlTag'), array( 'tag' => 'li', 'class' => 'group '.$displayGroup->getName(), 'separator'=>PHP_EOL ) );
  }
*/

  $displayGroup = $this->getDisplayGroup($element);
  if ($displayGroup) {
    // $displayGroup->clearDecorators();
    $displayGroup->addDecorator(array('Group'=>'Fieldset'), array('class' => 'group '.$element.'-nested', 'separator'=>PHP_EOL) );
  }

  return $this;
}

    /**
     *
     * Copia exácta de Zend_Form::isValid
     *
     * Realizamos modificaciones para añadir la clase de error a los elementos que tengan errores
     *
     * ------------------------------------------
     *
     * Validate the form
     *
     * @param  array $data
     * @return boolean
     */
    public function isValid($data)
    {
	$valid=parent::isValid($data);

        foreach ($this->getElements() as $element) {
        	if ($decorator=$element->getDecorator('Element')) {
	        	if ($errors=$element->getErrors()) {
	        		$decorator->setOption('class',trim(@$decorator->getOption('class')." error"));
	        	} else {
	        		// No necesitamos mandar el ok
	        		//$decorator->setOption('class',trim(@$decorator->getOption('class')." ok"));
	        	}
        	}
        }
        return $valid;
    }

}
