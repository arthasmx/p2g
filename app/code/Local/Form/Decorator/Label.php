<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Xplora_Form_Decorator_Label */
require_once 'Xplora/Form/Decorator/Label.php';

/**
 * Local_Form_Decorator_Label
 *
 * Extiende a Xplora_Form_Decorator_Label y a Zend_Form_Decorator_Label
 *
 * Permite especificar unas opciones por defecto al decorador para que se apliquen
 * a todas las etiquetas de la aplicación actual.
 *
 * -----------------------------------------------------------------------------
 *
 * Accepts the options:
 * - separator: separator to use between label and content (defaults to PHP_EOL)
 * - placement: whether to append or prepend label to content (defaults to prepend)
 * - tag: if set, used to wrap the label in an additional HTML tag
 * - opt(ional)Prefix: a prefix to the label to use when the element is optional
 * - opt(iona)lSuffix: a suffix to the label to use when the element is optional
 * - req(uired)Prefix: a prefix to the label to use when the element is required
 * - req(uired)Suffix: a suffix to the label to use when the element is required
 *
 * Any other options passed will be used as HTML attributes of the label tag.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Label.php 8164 2008-02-19 14:18:06Z matthew $
 */
class Local_Form_Decorator_Label extends Xplora_Form_Decorator_Label {

	/**
	 * Opciones por defecto
	 *
	 * @var array
	 */
	private $_defaultOptions = array(
		'placement'			=> "PREPEND",
		'tag'				=> "dt"
		/*,'optionalPrefix'	=> "form_optional_prefix",
		'optionalSuffix'	=> "form_optional_suffix",
		'requiredPrefix'	=> "form_required_prefix",
		'requiredSuffix'	=> "form_required_suffix",*/
	);

	/**
     * Constructor público
     *
     * @param  array|Zend_Config $options
     * @return void
     */
	public function __construct($options=null) {
		// Añadimos las opciones por defecto como opciones
			$this->setOptions($this->_defaultOptions);
		// Obligatorio para que el objeto se construya correctamente como marcan las clases extendidas.
			parent::__construct($options);
	}

}
