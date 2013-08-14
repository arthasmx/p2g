var articles = {
  msg:{
    saving       : "Guardando artículo, espere por favor...",
    error_saving : "No fue posible guardar el artículo. Favor de reportar este error lo mas pronto posible.",
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
    saving_effect : "div.basic_data",
    panes         : "div.panes",
    map_coors     : "div#googleMap",
    current_cors  : "span#current_cors",
    coord_rel     : "span.coord_response",
    links_rel     : "span.links_response",
    links_ge      : 'div#links_ge div.grouped-elements',
    list_grid     : "#article-listing",
    list_toolbar  : "div.custom-cbox-bar",

//    event_article_uploader : "div#event-article-uploader",
    announce_uploader      : "div#announce-uploader",
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
    id    : "#add-art-tabs",
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

    basic_data       : "form#add-article",
    ckeditor_article : "article"
  },
  url:{
    listing             : baseUrl + '/articles/list',
    save                : baseUrl + '/articles/save',
    listing_status      : baseUrl + '/articles/listing-status',
    listing_lang        : baseUrl + '/articles/listing-language',
    listing_promote     : baseUrl + '/articles/listing-promote',
    listing_mobile      : baseUrl + '/articles/listing-mobile',
    links_rel           : baseUrl + '/articles/links-rel',
    save_coordinates    : baseUrl + '/articles/save-coordinates',
    del_coordinates     : baseUrl + '/articles/del-coordinates',

    del_image           : baseUrl + '/articles-fi/delete-image',
    del_audio           : baseUrl + '/articles-fi/delete-audio',
    del_files           : baseUrl + '/articles-fi/delete-file',

    gallery_reload      : baseUrl + '/articles-fi/reload-gallery',
    audio_reload        : baseUrl + '/articles-fi/reload-audio',
    files_reload        : baseUrl + '/articles-fi/reload-files'
  },

  save:function(button){
    var self   = this;
    var action = "save";

    if( jQuery( self.form.article_id ).is("[data-id]") ){
      action = "update";
    }

    jQuery( self.form.basic_data + " dl dt label, fieldset#ckeditor legend").removeClass("missing-field");
    blockUI_ajax_saving(self.tabs.id,"on",self.msg.saving);

    var params = jQuery(self.form.basic_data).serialize() + "&action=" + action + "&btn="+ button +"&article=" + escape(CKEDITOR.instances[articles.form.ckeditor_article].getData());
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
        jQuery( articles.button.save +','+ articles.button.save_new +','+ articles.button.save_close +','+ articles.button.publicate ).removeClass('hide');
        jQuery( articles.button.next ).addClass('hide');
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
        jQuery( self.form.basic_data + " dl.field-" + response[i].field + " dt label").addClass("missing-field");
        if( response[i].field == 'article' ){
          jQuery( "fieldset#ckeditor legend" ).addClass("missing-field");
        }
      });      
      blockUI_ajax_saving(self.tabs.id,"off");
    }

  },

  clear_elements:function(){
    var self=this;

    // tab 1
      clear_form(self.form.basic_data);
      ckeditor_clear(self.form.ckeditor_article);
      jQuery('.chzn-select').val('').trigger('liszt:updated');
      jQuery(self.form.promote + "," + self.form.mobile).attr('checked', true);

    // tab 2
      jQuery( self.dom.main_pix +','+  self.dom.zipped_gallery +','+ self.dom.links_rel +','+  self.dom.current_cors +','+ self.dom.coord_rel +','+ self.dom.gallery_uploaded +','+ self.dom.audio_uploaded +','+ self.dom.files_uploaded +','+ self.dom.current_cors ).empty();

      var plUploader = jQuery('#upload_f_audio').pluploadQueue();
      plUploader.splice();
      plUploader = jQuery('#upload_f_docs').pluploadQueue();
      plUploader.splice();
      plUploader = jQuery('#uploader_f_gallery').pluploadQueue();
      plUploader.splice();
    // tab 4
      sheepItForm.removeAllForms();
      jQuery(articles.dom.map_coors).attr("value","").removeAttr("data-initialized");
      jQuery(self.dom.current_cors).empty();

      blockUI_ajax_saving(self.tabs.id,"off");
      jQuery(self.form.article_id).attr("value","").removeAttr("data-id");
      jQuery(self.tabs.id).tabs('select', 0).tabs({disabled:[1,2,3,4,5]});
      jQuery(self.form.date_public).datepicker('setDate', new Date());
      jQuery( articles.button.next ).removeClass('hide');
      jQuery( articles.button.save +','+ articles.button.save_new +','+ articles.button.save_close +','+ articles.button.publicate ).addClass('hide');
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

  del_audio:function(audio,li){
    var self=this;
    if( ! validate_param(audio) ){
      return false;
    }

    jQuery.ajax({
      type: 'post',
      data: {audio:audio},
      dataType: 'json',
      url:  self.url.del_audio,
      success: function(response){
        if( response.status == true ){
          jQuery(self.dom.del_audio_ul + " li").eq(li).remove();
          self.reload_audio();
        }
      },
      error: function(jqXHR, exception){
        jQuery(self.dom.del_audio_response).html( self.msg.audio_delete_error + " [" + exception + "]" ).addClass('error');
      }
    });
  },

  del_file:function(file,li){
    var self=this;
    if( ! validate_param(file) ){
      return false;
    }

    jQuery.ajax({
      type: 'post',
      data: {file:file},
      dataType: 'json',
      url:  self.url.del_files,
      success: function(response){
        if( response.status == true ){
          jQuery(self.dom.del_files_ul + " li").eq(li).remove();
          self.reload_files();
        }
      },
      error: function(jqXHR, exception){
        jQuery(self.dom.del_files_response).html( self.msg.files_delete_error + " [" + exception + "]" ).addClass('error');
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
  },

  reload_audio:function(){
    var self=this;

    jQuery.ajax({
      type: 'get',
      url:  self.url.audio_reload,
      success: function(response){
        jQuery( self.dom.audio_uploaded ).html( response );
      },
      error: function(jqXHR, exception){
        jQuery( self.dom.audio_uploaded ).html( self.msg.audio_reload_error + "[" + exception + "]" );
      }
    });
  },

  reload_files:function(){
    var self=this;

    jQuery.ajax({
      type: 'get',
      url:  self.url.files_reload,
      success: function(response){
        jQuery( self.dom.files_uploaded ).html( response );
      },
      error: function(jqXHR, exception){
        jQuery( self.dom.files_uploaded ).html( self.msg.files_reload_error + "[" + exception + "]" );
      }
    });
  }


};


jQuery(document).ready(function(){
  /* tabs click */
  jQuery( articles.tabs.id + " ul li").click(function(){
    if( ! jQuery( articles.form.article_id ).is("[data-id]") ){
      jQuery(articles.tabs.id).tabs('select', 0);
    }
    if( jQuery(this).index() == 3 && ! jQuery(articles.dom.map_coors).is('[data-initialized]') ){
      initialize();
    }
  });

  /* save */
  jQuery(articles.button.next+','+articles.button.save +','+ articles.button.save_new +','+ articles.button.save_close +','+ articles.button.publicate).click(function(){
    articles.save( jQuery(this).attr('id') );
  });
  /* coordinates add */
  jQuery(articles.button.coordinates).click(function() {
    if ( ! jQuery(articles.dom.current_cors).is(':empty') ){
      articles.save_coordinates( jQuery(articles.dom.current_cors).text() );
    }
  });
  /* coordinates del */
  jQuery(articles.button.del_coordinates).click(function() {
    articles.del_coordinates();
  });
  /* links rel */
  jQuery(articles.button.links_rel).click(function() {
    articles.save_links_rel();
  });
  /* delete uploaded images */
  jQuery(document).on('click', articles.dom.del_image_span, function(){
    articles.del( jQuery(this).attr('data-id'), jQuery(this).parent().parent().index() );
  });
  /* delete uploaded audio */
  jQuery(document).on('click', articles.dom.del_audio_span, function(){
    articles.del_audio( jQuery(this).attr('data-id'), jQuery(this).parent().parent().index() );
  });
  /* delete uploaded files*/
  jQuery(document).on('click', articles.dom.del_files_span, function(){
    articles.del_file( jQuery(this).attr('data-id'), jQuery(this).parent().parent().index() );
  });

});