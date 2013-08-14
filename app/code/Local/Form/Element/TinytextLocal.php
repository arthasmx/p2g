<?php

/**
 * @ruben:
 * @todo comentar ésta clase y todos los métodos que intervienen, incluir toda la información posible para usarla como referencia cuando necesitemos crear nuevos tipos de elementos
 */

require_once 'Xplora/Form/Element.php';

/**
 * Tinytex form element
 *
 */
class Local_Form_Element_TinytextLocal extends Xplora_Form_Element
{
    /**
     * Use formTinytext view helper by default
     * @var string
     */
    public $helper = 'formTinytextLocal';
}
