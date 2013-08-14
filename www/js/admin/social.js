var social = {
  msg:{
    saving        : "Guardando evento social...",
    saving_error  : "No fue posible guardar el evento social",

    error_update  : "No se pudo guardar sus cambios. ",
    deleting      : "Eliminando el evento",

    response_seo         : "Link de municipio esta duplicado (SEO)!",
    response_username    : "Este correo ya ha sido utilizado!",
    response_pass        : "Contraseña no coincide!",

    img_delete_error   : "Imposible eliminar la imágen. Favor de reportar este error lo mas pronto posible",

    gallery_reload_error : "No fue posible cargar las imágenes"
  },
  dom:{
    list_grid     : "#social-listing",
    list_toolbar  : "div.custom-cbox-bar",

    del_image_response     : "span.gp_response",
    del_image_span         : "span.iha_del",

    listing_action_shared  : "span#listing_action",
    gallery_uploaded       : "fieldset#social_preview div.uploaded_images",
    main_pix               : "#main-pix-filelist"
  },
  button:{
    save   : "span#save",
    del    : "span#del"
  },
  form:{
    basic_data : "form#social"
  },
  tabs:{
    id      : "#add-social-tabs",
    ul_tab  : ".tabs_title"
  },
  url:{
    listing        : baseUrl + '/social/list',
    save           : baseUrl + '/social/save',
    gallery_reload : baseUrl + '/social/reload-gallery',
    listing_status : baseUrl + '/social/listing-status',
    del_image      : baseUrl + '/social/delete-image',
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

        if( response.field ){
          jQuery.each(response.field, function(i,item) {
            jQuery( self.form.basic_data + " dl.field-" + item + " dt label" ).addClass("missing-field");
            if( item == 'article' ){
              jQuery( "fieldset#ckeditor legend" ).addClass("missing-field");
            }
          });
        }

        if( response.duplicated ){
          jQuery.each(response.duplicated, function(i,item) {
            jQuery( self.form.basic_data + " dl.field-" + item + " dt label" ).addClass("missing-field").html( self.get_error_response(item) );
          });
        }

        if( response.unmatch ){
          jQuery.each(response.unmatch, function(i,item) {
            jQuery( self.form.basic_data + " dl.field-" + item + " dt label" ).addClass("missing-field").html( self.get_error_response(item) );
          });
        }

      });

      blockUI_ajax_saving(self.tabs.id,"off");

    }

  },

  get_error_response:function(item){
    var self=this;
    switch( item ){
      case 'username':
        return self.msg.response_username;
        break;
      case 'seo':
        return self.msg.response_seo;
      case 'pass':
        return self.msg.response_pass;
        break;
    }
  },

  reload_gallery:function(){
    var self=this;

    jQuery.ajax({
      type: 'get',
      url:  self.url.gallery_reload,
      success: function(response){
        jQuery( self.dom.gallery_uploaded ).html( response );
        $('a.cBox-gallery').colorbox({rel:'cBox-gallery'});
      },
      error: function(jqXHR, exception){
        jQuery( self.dom.gallery_uploaded ).html( self.msg.gallery_reload_error + "[" + exception + "]" );
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


  image_delete:function(image,li){
    var self=this;
    if( ! validate_param(image) ){
      return false;
    }
    jQuery(self.dom.del_image_response).empty();

    jQuery.ajax({
      type: 'post',
      data: {image:image},
      dataType: 'json',
      url:  self.url.del_image,
      success: function(response){
        if( response.status == true ){
          jQuery(self.dom.del_image_ul + " li").eq(li).remove();
          self.reload_gallery();
        }
      },
      error: function(jqXHR, exception){
        jQuery(self.dom.del_image_response).html( self.msg.img_delete_error + " [SOC194]" ).addClass('error');
      }
    });
  },

};


jQuery(document).ready(function(){

  jQuery( social.button.save ).click(function(){
    social.save();
  });
  jQuery(document).on('click', social.button.del, function(){
    social.del();
  });
  jQuery(document).on('click', social.dom.del_image_span, function(){
    social.image_delete( jQuery(this).attr('data-id'), jQuery(this).parent().parent().index() );
  });

});