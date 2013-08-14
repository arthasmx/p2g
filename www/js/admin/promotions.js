var promotions = {
  msg:{
    saving        : "Guardando promoción...",
    saving_error  : "No fue posible guardar la promoción",
    preview_error : "No fue posible cargar la promoción",
    error_update  : "No se pudo guardar sus cambios. ",
    deleting      : "Eliminando promocion(es)"
  },
  dom:{
    preview : "div.uploaded_images",
    list_grid     : "#promotions-listing",
    list_toolbar  : "div.custom-cbox-bar",
    listing_action_shared  : "span#listing_action"
  },
  button:{
    save   : "span#save",
    del    : "span#del"
  },
  form:{
    basic_data : "form#promotions"
  },
  tabs:{
    id      : "#add-promotions-tabs",
    ul_tab  : ".tabs_title"
  },
  url:{
    listing : baseUrl + '/promotions/list',
    save    : baseUrl + '/promotions/save',
    preview : baseUrl + '/promotions/preview',
    listing_status : baseUrl + '/promotions/listing-status'
  },

  save:function(button){
    var self   = this;

    jQuery( self.form.basic_data + " dl dt label").removeClass("missing-field");
    blockUI_ajax_saving(self.tabs.id,"on",self.msg.saving);

    var params = jQuery(self.form.basic_data).serialize();
    jQuery.ajax({
      type: 'POST',
      data: params,
      dataType: 'json',
      url:  self.url.save,
      success: function(response){
        self.process_save_response(response);
      },
      error: function(request, status, error){
        alert(self.msg.saving_error);
        blockUI_ajax_saving(self.tabs.id,"off");
      }
    });

  },

  process_save_response:function(response){
    var self = this;

    if( is_number(response) ){
      redirect( self.url.listing );
      return true;
    }else{
      jQuery.each(response, function(i,item) {
        jQuery( self.form.basic_data + " dl.field-" + response[i].field + " dt label").addClass("missing-field");
      });
      blockUI_ajax_saving(self.tabs.id,"off");
    }

  },

  preview:function(){
    var self=this;

    jQuery.ajax({
      type: 'get',
      url:  self.url.preview,
      success: function(response){
        jQuery( self.dom.preview ).html( response );
      },
      error: function(jqXHR, exception){
        jQuery( self.dom.preview ).html( self.msg.preview_error + "[" + exception + "]" );
      }
    });
  },

  del:function(button){
    var self   = this;
    self.listing_action_shared('status','deleted',self.url.listing_status);
  },

  status:function(value){
    var self=this;
    self.listing_action_shared('status',value,self.url.listing_status);
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
        jQuery( self.dom.list_grid ).trigger( 'reloadGrid' );
        jQuery( self.dom.list_toolbar ).remove();
      },
      error: function(jqXHR, exception){
        jQuery(self.dom.listing_action_shared).html( self.msg.error_update + "[LST]" );
      }
    });
  },
  linker:function(value){
    var self=this;
    if( value=='link' ){
      jQuery("dl.field-onclick_url").removeClass('hidden');
    }else{
      jQuery("dl.field-onclick_url").addClass('hidden');
      jQuery("input#onclick_url").val('');
    }
  }

};


jQuery(document).ready(function(){

  jQuery( promotions.button.save ).click(function(){
    promotions.save();
  });
  jQuery(document).on('click', promotions.button.del, function(){
    promotions.del();
  });


});