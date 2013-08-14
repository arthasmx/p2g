<?php
require_once 'Module/Addons/Controller/Action/Admin.php';
class Addons_CategoryController extends Module_Addons_Controller_Action_Admin {

  function listAction() {
    $this->view->current_menu = array('menu'=>4,'sub'=>5);

    $this->view->main_category = $this->_module->getModel('Categories')->jqGrid_admin_main_categories();
  }

  function editAction(){
// Falta corregir el menu que corresponde aqui
    $this->view->current_menu = array('menu'=>1);
    $parent                   = $this->getRequest()->getParam('parent');

    $libraries = App::module('Core')->getModel('Libraries');
    $libraries->jquery_treeview("crumbs");
    $libraries->block_ui();
    $libraries->jquery_ui_dialog("subcategory-form","{autoOpen: false,height: 350,width: 410,modal: true,
                                    buttons:{
                                      '".App::xlat('BUTTON_save_new')."' : function(){
                                         categories.add();
                                       },
                                      '".App::xlat('BUTTON_close')."': function() {
                                         jQuery( this ).dialog('close');
                                       }
                                    },
                                    close: function() {
                                      jQuery('div.ui-dialog input#name,div.ui-dialog input#seo').val('');
                                      jQuery('div.ui-dialog dt label').removeClass('missing-field');
                                      jQuery(categories.dom.create_resp).empty();
                                    }
                                  }");

    $parent_data = $this->_module->getModel('Categories')->get_category( $parent );

    $this->view->treeview = App::module('Core')->getModel('Arrays')->array_to_tree( $this->_module->getModel('Categories')->get_family_back( $parent, true, $parent_data['izq'], $parent_data['der'] )
                                                                                   ,null
                                                                                   ,"ul"
                                                                                   ,"crumbs"
                                                                                   ,"/categories/edit/" );

    
    
    
    
    $form = $this->_module->getModel('Forms/Category')->get();    
    $form->populate($parent_data);
    $this->view->parent_form = $form;
    $this->view->parent_id   = $parent;
    $this->view->parent      = $parent_data;

    $this->_module->getModel('Categories')->grid_shared_code( $parent );

    App::header()->add_jquery_events("jQuery('li a[data-id=$parent]').css({color:'red'});");

  }

  function listDataLoaderAction(){
    $this->designManager()->setCurrentLayout('ajax');
    echo $this->_module->getModel('Categories')->jqGrid_admin_list( $this->getRequest()->getParams() );
    exit;
  }



  function listingStatusAction(){
    $this->_module->getModel('Cud/Categories')->update_field_value( 'status', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
  }

  function listingLanguageAction(){
    $this->_module->getModel('Cud/Categories')->update_field_value( 'language', $this->getRequest()->getParam('value'), $this->getRequest()->getParam('ids') );
  }



  function addAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $request = $this->getRequest();
    $form    = $this->_module->getModel('Forms/Category')->get();

    if ( $request->isPost() ){
      if( $form->isValid($_POST) ){
        $this->_module->getModel('Cud/Categories')->create($_POST);
      }else{
        App::module('Core')->getModel('Form')->get_json_error_fields($form);
      }
    }
    die('{"status":false}');
  }

  function updateAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $request = $this->getRequest();
    $form    = $this->_module->getModel('Forms/Category')->get( $request->getParam('parent') );

    if ( $request->isPost() ){
      if( $form->isValid($_POST) ){
        $this->_module->getModel('Cud/Categories')->update($_POST);
      }else{
        App::module('Core')->getModel('Form')->get_json_error_fields($form);
      }
    }
    die('{"status":false}');
  }

  function treeReloadAction(){
    $this->designManager()->setCurrentLayout('ajax');
    $parent      = $this->getRequest()->getParam('parent');
    $parent_data = $this->_module->getModel('Categories')->get_category( $parent );
    $this->view->treeview = App::module('Core')->getModel('Arrays')->array_to_tree( $this->_module->getModel('Categories')->get_family_back( $parent, true, $parent_data['izq'], $parent_data['der'] )
                                                                                   ,null
                                                                                   ,"ul"
                                                                                   ,"crumbs"
                                                                                   ,"/categories/edit/" );
    $this->view->parent = $parent_data;
  }

  function deleteAction(){
    $this->_module->getModel('Cud/Categories')->delete( $this->getRequest()->getParam('category') );
  }

}