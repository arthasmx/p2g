<?php

require_once 'Local/Controller/Action.php';

class Core_CaptchaController extends Local_Controller_Action   {

  function getAction() {
  require_once('Xplora/Captcha.php');

  $font_size = 20;
  $captcha=new Xplora_Captcha(
  array(
		"chars"    => 5,
		"width"    => 140,
		"height" 	 => 45,
		"font"     => array("avenir"   => $font_size,
                        "bluehighway"  => $font_size,
                        "continuum"    => $font_size,
                        "intrepid"     => $font_size,
                        "micro"        => $font_size ),
		"chamaleon"  => false,
		"color"      => array( array(140,158,195) ),
		"bgimage"    => array( $this->getSkinPath("/art/bks/captcha.gif") ),
		"outline"    => array(255,255,255), // Color del outline de las letras, si no se quiere outline, se puede poner false, puede ponerse "auto" y usará el color más claro que haya disponible en el array de colores indicado o en el array de colores "camaleonicos"
		"size_jitter"    => array(0,30),    // Rango de tamaño array min,max (en porcentaje)
		"word"        => true,
		"shuffle"      => true,
	"rotation_jitter"  => array(-5,5)   // Rango de rotación de las letras array min,max (en grados)
  ));
$captcha->render();
exit;
  }

}