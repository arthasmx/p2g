<?php
class Module_User_Repository_Model_User_Forms_Areas_Business_Entidad extends Core_Model_Repository_Model {

	public function get($post=false) {
	$idioma = App::locale()->getLang();
	require_once "Local/Form.php";
	$form=new Local_Form;
	$form->setAttrib('id','perfil-entidad-form');

	if(!$post){
		
/**
 * OJO!!!
 * NECESITAMOS CARGAR LA ENTIDAD DE LA CUENTA
 * 
 * Para hacerlo generico, deberia:
 * 		- Cargar todas las entidades que pertenezcan al USERNAME
 * 		- Listarlas y que el usuario elija cual de ellas desea modificar
 * 		- Ahora si, a modificar.....
 * 		- Lo anterior puede hacerse en unos TABS tipo WIZZARD, donde primero selecciones la entidad y luego la puedas modificar
 * 		- O tambien puede hacerse un simple listado y cargar en otra MOCHAwin el formulario para modificarlo....
 * 		- Asi podras tener N entidades para un usuario, se podran borrar y/o modificar
 * 
 */		
		
		$cliente=App::module("Acl")->getModel('acl')->data;
	}else{
		$cliente=$post;
		$cliente['username']=$post['cliente'];
	}

		// Username
		$form->addElement('hidden', 'cliente', array(
		        'value'=>$cliente['username'],
				'class'=>'hidden'
		));
        $form->addElement('text', 'e_nombre', array(
            'description' => App::xlat("PUBLIC_SIGNUP_name"),
            'required' => true,
            'validators' => array(
                    array('stringLength', true, array(6))
            ),
            'class'=>'required'
        ));
        $form->addElement('text', 'e_telefono', array(
            'description' => App::xlat("PUBLIC_SIGNUP_BUSINESS_phone"),
        	'required' => true,
            'class'=>'required'
        ));
        $form->addElement('text', 'e_extension', array(
            'description' => App::xlat("PUBLIC_SIGNUP_BUSINESS_extension"),
        ));
        $form->addElement('text', 'e_fax', array(
            'description' => App::xlat("PUBLIC_SIGNUP_BUSINESS_fax"),
        ));
        $form->addElement('text', 'e_movil', array(
            'description' => App::xlat("PUBLIC_SIGNUP_BUSINESS_movil"),
        ));
        
$form->addDisplayGroup(
    array('e_nombre','e_telefono','e_extension','e_fax','e_movil'),
    'econtacto',
    array(
        'legend' => App::xlat('PUBLIC_SIGNUP_BUSINESS_title_contact'),
    )
);
$form->getDisplayGroup('econtacto')->getDecorator('Group')->addClass('no-border');
		

		$form->populate($cliente);
		return $form;
	}


}