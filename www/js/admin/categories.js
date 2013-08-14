var categories = {
  parent : false,
  msg:{
    saving       : "Guardando categoría, espere por favor...",
    added        : "Categoría creada correctamente",
    updated      : "Categoría actualizada correctamente",
    tree_reload  : "No fue posible cargar el árbol de categorías",

    error_saving : "No fue posible guardar la categoría",
    error_update : "No se pudo guardar sus cambios"
  },
  dom:{
    list_grid     : "#categories-listing",
    list_toolbar  : "div.custom-cbox-bar",

    fieldset_parent : "fieldset#f_cat_parent",

    parent_id     : "div#subcategory-form form input#parent",
    dialog_sub    : "div#subcategory-form",
    del_warning   : "div#del_warning",

    create_resp   : "span#create_response",
    update_resp   : "span#update_response",

    tree          : "div.cat_tree"
  },
  button:{
    update       : "span#update",
    add          : "span#add",
    del          : "span#delete",

    tmpl_close   : "<span class='tmpl_close ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-secondary'> <span class='ui-button-text'>Cerrar</span> <span class='ui-button-icon-secondary ui-icon ui-icon-close'></span> </span>"
  },
  form:{
    parent_cat  : "form#parent_category",
    subcategory : "div#subcategory-form form"
  },
  url:{
    update         : baseUrl + '/categories/update',
    add            : baseUrl + '/categories/add',
    edit           : baseUrl + '/categories/edit/',
    del            : baseUrl + '/categories/delete',
    tree           : baseUrl + '/categories/tree-reload',
    listing        : baseUrl + '/categories/list',

    listing_status : baseUrl + '/categories/listing-status',
    listing_lang   : baseUrl + '/categories/listing-language'
  },

  status:function(value){
    var self=this;
    self.listing_action_shared('status',value,self.url.listing_status);
  },
  language:function(value){
    var self=this;
    self.listing_action_shared('language',value,self.url.listing_lang);
  },
  listing_action_shared:function(type,value,url){
    var self=this;
    if( ! validate_param(value) ){
      return false;
    }
    var ids = jQuery( self.dom.list_grid ).getGridParam("selarrrow");
    jQuery(self.dom.listing_action_shared).empty();

    jQuery.ajax({
      type: 'post',
      data: {ids:JSON.stringify(ids),type:type,value:value},
      dataType: 'json',
      url:  url,
      success: function(response){

        if( response.status==true ){
          jQuery( self.dom.list_grid ).trigger( 'reloadGrid' );
          jQuery( self.dom.list_toolbar ).remove();
          self.tree_reload();
        }else{
          jQuery.blockUI({ message: response.message + "<br />" + self.button.tmpl_close, css:{'padding':'20px', borderColor:'#FF0000'},overlayCSS:{ backgroundColor: '#FDD0D0', opacity: 0.8, cursor: 'wait' } });
        }

      },
      beforeSend: function() {
        self.parent = jQuery( self.dom.parent_id ).val();
        jQuery("body section article").block({
          message: "<h1 style='padding:10px;'>" + self.msg.saving + "</h1>",
          css        : { border  : '3px solid #94B52C', width: '90%' },
          overlayCSS : { opacity : 0.9, background:'#fff' }
        });
      },
      complete: function() {
        blockUI_ajax_saving("body section article","off");
      },
      error: function(jqXHR, exception){
        jQuery.blockUI({ message: "<h2>No fue posible modificar la(s) categoría(s) [ERR94]</h2><br />" + self.button.tmpl_close, css:{'padding':'20px', borderColor:'#000000'},overlayCSS:{ backgroundColor: '#E54930', opacity: 1, cursor: 'wait' } });
      }
    });
  },



  add:function(){
    var self   = this;
    var params = jQuery(self.form.subcategory).serialize();

    jQuery.ajax({
      type: 'post',
      data: params,
      cache: false,
      dataType: 'json',
      url:  self.url.add,
      success: function(response){

        if( ! is_number(response) ){
          if( response.status==false ){
            jQuery( self.dom.create_resp ).addClass('error').html( response.message ).addClass('success');
          }else{
            jQuery.each(response, function(i,item) {
              jQuery( self.form.subcategory + " dl.field-" + response[i].field + " dt label").addClass("missing-field");
            });
          }
        }else{
          jQuery( self.dom.create_resp ).html( self.msg.added ).addClass('success');
          jQuery('div.ui-dialog input#name,div.ui-dialog input#seo').val('');
          jQuery( self.dom.list_grid ).trigger( 'reloadGrid' );
          self.tree_reload();
        }

      },
      beforeSend: function() {
        self.parent = jQuery( self.dom.parent_id ).val();
        jQuery("div.ui-dialog").block({
          message: "<h2 style='padding:10px;'>" + self.msg.saving + "</h2>",
          css        : { border  : '3px solid #94B52C', width: '90%' },
          overlayCSS : { opacity : 0.9, background:'#fff' }
        });
        jQuery('div.ui-dialog dt label').removeClass('missing-field');

      },
      complete: function() {
        blockUI_ajax_saving("div.ui-dialog","off");
      },
      error: function(jqXHR, exception){
        jQuery( self.dom.create_resp ).addClass('error').html( response.message );
      }
    });

  },
  update:function(){
    var self=this;
    self.parent = jQuery( self.dom.parent_id ).val();

    var params = jQuery(self.form.parent_cat).serialize() + "&parent=" + self.parent;

    jQuery.ajax({
      type: 'post',
      data: params,
      cache: false,
      dataType: 'json',
      url:  self.url.update,
      success: function(response){

        if( response.status==false ){
          jQuery( self.dom.update_resp ).addClass('error').html( response.message );
          jQuery.each(response, function(i,item) {
            jQuery( self.dom.fieldset_parent + " dl.field-" + response[i].field + " dt label").addClass("missing-field");
          });
        }else{
          jQuery( self.dom.update_resp ).html( self.msg.updated );
          jQuery( self.dom.list_grid ).trigger( 'reloadGrid' );
          self.tree_reload();
        }

      },
      beforeSend: function() {
        jQuery("body section article").block({
          message: "<h1 style='padding:10px;'>" + self.msg.saving + "</h1>",
          css        : { border  : '3px solid #94B52C', width: '90%' },
          overlayCSS : { opacity : 0.9, background:'#fff' }
        });
        jQuery( self.dom.fieldset_parent + ' dt label').removeClass('missing-field');

      },
      complete: function() {
        blockUI_ajax_saving("body section article","off");
      },
      error: function(jqXHR, exception){
        jQuery( self.dom.update_resp ).addClass('error').html( response.message );
      }
    });

  },

  tree_reload:function(){
    var self=this;

    jQuery.ajax({
      type: 'get',
      data:{parent:self.parent},
      url:  self.url.tree,
      success: function(response){
        jQuery( self.dom.tree ).html( response );
        $("li a[data-id="+ self.parent +"]").css({color:'red'});
      },
      error: function(jqXHR, exception){
        jQuery( self.dom.tree ).html( self.msg.error_tree_reload + " [TREER]" );
      }
    });
  },



  del:function(){
    var self=this;
    self.parent = jQuery( self.dom.parent_id ).val();

    jQuery.ajax({
      type: 'post',
      data: {category:self.parent},
      cache: false,
      dataType: 'json',
      url:  self.url.del,
      success: function(response){

        if( response.status==false ){
          jQuery.blockUI({ message: response.message + "<br />" + self.button.tmpl_close , css:{'padding':'20px', borderColor:'#FF0000'},overlayCSS:{ backgroundColor: '#FDD0D0', opacity: 0.8, cursor: 'wait' } }); 
        }else{
          redirect( self.url.edit + response.parent_id );
          return false;
        }

      },
      error: function(jqXHR, exception){
        jQuery.blockUI({ message: "<h2>No fue posible eliminar la(s) categoría(s) [ERR210]</h2><br />" + self.button.tmpl_close, css:{'padding':'20px', borderColor:'#000000'},overlayCSS:{ backgroundColor: '#E54930', opacity: 1, cursor: 'wait' } });
      }
    });

  }

};

jQuery(document).ready(function(){

  jQuery( categories.button.update ).click(function(){
    categories.update();
  });
  jQuery( categories.button.add ).click(function(){
    jQuery( categories.dom.dialog_sub ).dialog( "open" );
  });
  jQuery( categories.button.del).click(function(){
    jQuery.blockUI({ message: jQuery( categories.dom.del_warning )
                    , css:{ width: '450px', borderColor:'#FF0000'}
                    ,overlayCSS:{ 
                      backgroundColor: '#000', 
                      opacity:         0.8, 
                      cursor:          'wait' 
                    }
    });
  });
    jQuery('#yes').click(function() { 
      jQuery.blockUI({ message: "<h1>Eliminando categoría...</h1>", css:{'padding':'20px', borderColor:'#FF0000'},overlayCSS:{ backgroundColor: '#000', opacity: 0.8, cursor: 'wait' } }); 
      categories.del();
    }); 
    jQuery('#no').click(function() { 
      jQuery.unblockUI(); 
      return false; 
    }); 
    jQuery(document).on('click', 'span.tmpl_close', function(){
      jQuery.unblockUI(); 
      return false;
    });


  jQuery( categories.form.subcategory + ' input#name').keypress(function (e) {
    if (e.which == 13) {
      categories.add();
    }
  });

});