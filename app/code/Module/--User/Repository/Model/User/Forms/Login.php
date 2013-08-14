<?php
class Module_User_Repository_Model_User_Forms_Login extends Core_Model_Repository_Model {

var $form = null;

public function get() {
    require_once "Local/Form.php";
    $this->form = new Local_Form;
    $this->form->setAttribs( array('id'=>'login-form'
                       ,'action'=>'#'));

    $this->form->addElement(
        'text',
        'user',
        array(
            'label' => App::xlat('LOGIN_BLOCK_textbox_login'),
            'required' => true,
            'validators' => array(
                    array('stringLength', true, array(4))
            ),
            'class' => 'field',
            'value' => App::xlat('LOGIN_BLOCK_textbox_tmp_login')
          )
    );

    $this->form->addElement(
        'password',
        'password',
        array(
            'label'=>App::xlat('LOGIN_BLOCK_textbox_password'),
            'required' => true,
            'validators' => array(
                    array('stringLength', true, array(4))
            ),
            'class'=>'field',
          )
    );

    $this->form->addElement(
        'submit',
        'boton',
        array(
            'label'=>App::xlat('LOGIN_BLOCK_btn_login'),
            'class'=>'basicButton'
        )
    );

    $this->form->addDisplayGroup(
        array(
            'user','password','boton'
        ),
        'elfieldset'
    );
    $this->form->getDisplayGroup('elfieldset')->getDecorator('Group')->addClass('float-left');

    return $this->form;
}

}