var events = {
  msg:{
    saving       : "Guardando evento, espere por favor...",
    error_saving : "No fue posible guardar el evento. Favor de reportar este error lo mas pronto posible.",
    relate_error : "No se pudo guardar la relación",
    error_update : "No se pudo guardar sus cambios. ",

    img_delete_error   : "Imposible eliminar la imágen. Favor de reportar este error lo mas pronto posible",
    audio_delete_error : "Imposible eliminar el audio. Favor de reportar este error lo mas pronto posible",
    files_delete_error : "Imposible eliminar el documento. Favor de reportar este error lo mas pronto posible",

    gallery_reload_error : "No fue posible cargar las imágenes",
    audio_reload_error   : "No fue posible cargar los archivos de sonido",
    files_reload_error   : "No fue posible cargar los documentos"
  },
  dom:{
//    saving_effect : "div.basic_data",
    panes         : "div.panes",
    map_coors     : "div#eventMap",
    current_cors  : "span#current_cors",
    coord_rel     : "span.coord_response",
    links_rel     : "span.links_response",
    links_ge      : 'div#links_ge div.grouped-elements',
    list_grid     : "#event-listing",
    list_toolbar  : "div.custom-cbox-bar",

    listing_action_shared  : "span#listing_action",

    del_image_response     : "span.gp_response",
    del_image_ul           : "ul.uploaded_images",
    del_image_span         : "span.iha_del",

    del_audio_response     : "span.audio_response",
    del_audio_ul           : "ul.uploaded_audio",
    del_audio_span         : "span.del_audio",

    del_files_response     : "span.file_response",
    del_files_ul           : "ul.uploaded_files",
    del_files_span         : "span.del_file",

    gallery_uploaded       : "fieldset#gallery_uploaded div.uploaded_images",
    audio_uploaded         : "fieldset#f_files_audio div.uploaded_audio",
    files_uploaded         : "fieldset#f_files_docs div.uploaded_files",
    main_pix               : "#main-pix-filelist",
    zipped_gallery         : "#zip-filelist"
  },
  tabs:{
    id    : "#add-eve-tabs",
    ul_tab: ".tabs_title",

    current : 0,
    max     : 3
  },
  button:{
    next       : "span#next",
    save       : "span#save",
    save_new   : "span#save_new",
    save_close : "span#save_close",
    save_as    : "button#save_as",
    publicate  : "span#save_publicate",
    links_rel  : "span#save_links_rel",
    coordinates : "span#save_coordinates",
    del_coordinates : "span#del_coordinates"
  },
  form:{
    article_id  : "input#article_id",

    promote     : "input#promote",
    mobile      : "input#mobile",
    date_event  : "input#event_date",
    date_public : "input#publicate_at",
    date_stop   : "input#stop_publicate",

    links_rel   : "form#links_rel",

    form        : "form#add-event",
    ckeditor_event : "article"
  },
  url:{
    listing             : baseUrl + '/events/list',
    save                : baseUrl + '/events/save',
    listing_status      : baseUrl + '/events/listing-status',
    listing_lang        : baseUrl + '/events/listing-language',
    listing_promote     : baseUrl + '/events/listing-promote',
    listing_mobile      : baseUrl + '/events/listing-mobile',
    links_rel           : baseUrl + '/events/links-rel',
    save_coordinates    : baseUrl + '/events/save-coordinates',
    del_coordinates     : baseUrl + '/events/del-coordinates',
    del_image           : baseUrl + '/events-fi/event-delete-image',
    gallery_reload      : baseUrl + '/events-fi/event-reload-gallery'
  },

  save:function(button){
    var self   = this;
    var action = "save";

    if( jQuery( self.form.article_id ).is("[data-id]") ){
      action = "update";
    }

    jQuery( self.form.form + " dl dt label, fieldset#ckeditor legend").removeClass("missing-field");
    blockUI_ajax_saving(self.tabs.id,"on",self.msg.saving);

    var params = jQuery(self.form.form).serialize() + "&action=" + action + "&btn="+ button +"&article=" + escape(CKEDITOR.instances[events.form.ckeditor_event].getData());
    jQuery.ajax({
      type: 'POST',
      data: params,
      dataType: 'json',
      url:  self.url.save,
      success: function(response){
        self.process_save_response(response,button);
      },
      error: function(request, status, error){
        jQuery(self.dom.comment_container).html(self.msg.error_saving);
        blockUI_ajax_saving(self.tabs.id,"off");
      }
    });

  },

  process_save_response:function(response,button){
    var self = this;

    if( is_number(response) ){
      switch(button){
      case 'next':
        blockUI_ajax_saving(self.tabs.id,"off");
        jQuery( events.button.save +','+ events.button.save_new +','+ events.button.save_close +','+ events.button.publicate ).removeClass('hide');
        jQuery( events.button.next ).addClass('hide');
        jQuery(self.form.article_id).attr("data-id",response).attr("value",response);
        jQuery(self.tabs.id).tabs({disabled:[]}).tabs('select', 1);
        break
      case 'save_new':
        self.clear_elements();
        break
      case 'save_close':
      case 'save_publicate':
        redirect( self.url.listing );
        break
      default:
        blockUI_ajax_saving(self.tabs.id,"off");
        return false;
        break;
      }

    }else{
      jQuery.each(response, function(i,item) {
        jQuery( self.form.form + " dl.field-" + response[i].field + " dt label").addClass("missing-field");
        if( response[i].field == 'article' ){
          jQuery( "fieldset#ckeditor legend" ).addClass("missing-field");
        }
      });      
      blockUI_ajax_saving(self.tabs.id,"off");
    }

  },

  clear_elements:function(){
    var self=this;

    clear_form(self.form.form);
    ckeditor_clear(self.form.ckeditor_event);
    jQuery('.chzn-select').val('').trigger('liszt:updated');
    jQuery(self.form.promote + "," + self.form.mobile).attr('checked', true);

    jQuery( self.dom.main_pix +','+  self.dom.zipped_gallery +','+ self.dom.links_rel +','+  self.dom.current_cors +','+ self.dom.coord_rel +','+ self.dom.gallery_uploaded +','+ self.dom.current_cors ).empty();

    var plUploader = jQuery('#uploader_f_gallery').pluploadQueue();
    plUploader.splice();

    sheepItForm.removeAllForms();
    jQuery(events.dom.map_coors).attr("value","").removeAttr("data-initialized");

    blockUI_ajax_saving(self.tabs.id,"off");

    jQuery(self.form.article_id).attr("value","").removeAttr("data-id");
    jQuery(self.tabs.id).tabs('select', 0).tabs({disabled:[1,2]});
    jQuery(self.form.date_public).datepicker('setDate', new Date());
    jQuery( events.button.next ).removeClass('hide');
    jQuery( events.button.save +','+ events.button.save_new +','+ events.button.save_close +','+ events.button.publicate ).addClass('hide');
  },

  save_coordinates:function(coordinates){
    var self=this;
    if( validate_param(coordinates) ){
      jQuery.ajax({
        type: 'POST',
        data: {cors:coordinates},
        dataType: 'json',
        url:  self.url.save_coordinates,
        success: function(response){
          jQuery(self.dom.coord_rel).addClass(response.css_class).html( response.message );
        },
        error: function(jqXHR, exception){
          jQuery(self.dom.coord_rel).addClass('error').html( self.msg.error_update + "[COOR]" );
        }
      });
    }
  },
  del_coordinates:function(){
    var self=this;
    jQuery.ajax({
      dataType: 'json',
      url:  self.url.del_coordinates,
      success: function(response){
        if( response.status == true ){
          jQuery(self.dom.coord_rel).addClass(response.css_class).html( response.message );
          jQuery(self.dom.current_cors).empty();
        }else{
          jQuery(self.dom.coord_rel).addClass('error').html( self.msg.error_update + "[OR208]" );
        }
      },
      error: function(jqXHR, exception){
        jQuery(self.dom.coord_rel).addClass('error').html( self.msg.error_update + "[COR212]" );
      }
    });
  },

  save_links_rel:function(){
    var self = this;
    var links = {links:'none'};

    jQuery( self.dom.links_ge + ' small.error').remove();
    jQuery( self.dom.links_rel ).empty().removeClass('success error');

    if ( jQuery( self.dom.links_ge ).size() > 0 ){
      var save = true;

      jQuery( self.dom.links_ge ).each(function(){
        var desc = jQuery.trim( jQuery('.desc',this).val() ), url = jQuery.trim( jQuery('.url',this).val() ), type = jQuery.trim( jQuery('.type',this).val() );
        if ( ( ! desc || desc.length<5 ) || ( ! url || url.length<10 )){
          jQuery(this).append('<small class="error">Llena el formulario correctamente</small>');
          save=false;
        }
       });

      if( ! save ){ return false; }
      links = jQuery(self.form.links_rel).serialize();
    }

    jQuery.ajax({
      type: 'post',
      data: links,
      dataType: 'json',
      url:  self.url.links_rel,
      success: function(response){
        jQuery(self.dom.links_rel).addClass(response.css_class).html( response.message );
      },
      error: function(jqXHR, exception){
        jQuery(self.dom.links_rel).html( self.msg.error_update + "[LNK]" ).addClass('error');
      }
    });
  },

  status:function(value){
    var self=this;
    self.listing_action_shared('status',value,self.url.listing_status);
  },
  language:function(value){
    var self=this;
    self.listing_action_shared('language',value,self.url.listing_lang);
  },
  promote:function(value){
    var self=this;
    self.listing_action_shared('promote',value,self.url.listing_promote);
  },
  mobile:function(value){
    var self=this;
    self.listing_action_shared('mobile',value,self.url.listing_mobile);
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

  del:function(image,li){
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
        jQuery(self.dom.del_image_response).html( self.msg.img_delete_error + " [" + exception + "]" ).addClass('error');
      }
    });
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
  }

};


jQuery(document).ready(function(){
  /* tabs click */
  jQuery( events.tabs.id + " ul li").click(function(){
    if( ! jQuery( events.form.article_id ).is("[data-id]") ){
      jQuery(events.tabs.id).tabs('select', 0);
    }
    if( jQuery(this).index() == 2 && ! jQuery(events.dom.map_coors).is('[data-initialized]') ){
      initialize();
    }
  });

  /* save */
  jQuery(events.button.next+','+events.button.save +','+ events.button.save_new +','+ events.button.save_close +','+ events.button.publicate).click(function(){
    events.save( jQuery(this).attr('id') );
  });
  /* coordinates add */
  jQuery(events.button.coordinates).click(function() {
    if ( ! jQuery(events.dom.current_cors).is(':empty') ){
      events.save_coordinates( jQuery(events.dom.current_cors).text() );
    }
  });
  /* coordinates del */
  jQuery(events.button.del_coordinates).click(function() {
    events.del_coordinates();
  });
  /* links rel */
  jQuery(events.button.links_rel).click(function() {
    events.save_links_rel();
  });
  /* delete uploaded images */
  jQuery(document).on('click', events.dom.del_image_span, function(){
    events.del( jQuery(this).attr('data-id'), jQuery(this).parent().parent().index() );
  });

});