var towns = {

  msg:{
    saving       : "Guardando municipio, espere por favor...",
    load_section : "Cargando tema...",
    save_section : "Guardando tema...",

    image_delete : "Eliminando imagen...",
    
    error_saving : "No fue posible guardar el municipio. Favor de reportar este error lo mas pronto posible.",
    relate_error : "No se pudo guardar la relación",
    error_update : "No se pudo guardar sus cambios. ",

    img_delete_error   : "Imposible eliminar la imágen. Favor de reportar este error lo mas pronto posible",
    audio_delete_error : "Imposible eliminar el audio. Favor de reportar este error lo mas pronto posible",
    files_delete_error : "Imposible eliminar el documento. Favor de reportar este error lo mas pronto posible",

    gallery_reload_error : "No fue posible cargar las imágenes",
    audio_reload_error   : "No fue posible cargar los archivos de sonido",
    files_reload_error   : "No fue posible cargar los documentos",

    section_legend_1     : "Defina el tema { ",
    section_legend_2     : " } para el municipio en turno",
    
    response_seo         : "Link de municipio esta duplicado (SEO)!",
    response_username    : "Este correo ya ha sido utilizado!",
    response_pass        : "Contraseña no coincide!"
  },
  dom:{
    saving_effect : "div.basic_data",
    panes         : "div.panes",
    map_coors     : "div#googleMap",
    current_cors  : "span#current_cors",
    coord_rel     : "span.coord_response",
    links_rel     : "span.links_response",
    links_ge      : 'div#links_ge div.grouped-elements',
    list_grid     : "#town-listing",
    list_toolbar  : "div.custom-cbox-bar",

    announce_uploader      : "div#announce-uploader",
    listing_action_shared  : "span#listing_action",

    del_image_response     : "span.gp_response",
    del_image_ul           : "ul.uploaded_images",
    del_image_span         : "span.iha_del",

    gallery_uploaded       : "fieldset#gallery_uploaded div.uploaded_images",
    main_pix               : "#main-pix-filelist",
    zipped_gallery         : "#zip-filelist",

    section_info           : "fieldset#section_info",
    daSection_instructions : "div#daSection div.instructions",
    daSectionCkeditor      : "div.daSectionCkeditor",
    section_image_results  : "table.sections img"
  },
  tabs:{
    id    : "#add-town-tabs",
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
    links_rel   : "form#links_rel",

    add_section      : "form#add-section",
    ckeditor_section : "section_desc",

    basic_data       : "form#add-town",
    ckeditor_article : "article"
  },
  url:{
    listing             : baseUrl + '/towns/list',
    save                : baseUrl + '/towns/save',
    listing_status      : baseUrl + '/towns/listing-status',
    listing_lang        : baseUrl + '/towns/listing-language',
    listing_promote     : baseUrl + '/towns/listing-promote',
    listing_mobile      : baseUrl + '/towns/listing-mobile',

    links_rel           : baseUrl + '/towns/links-rel',

    save_coordinates    : baseUrl + '/towns/save-coordinates',
    del_coordinates    : baseUrl + '/towns/del-coordinates',

    del_image           : baseUrl + '/towns/delete-image',
    gallery_reload      : baseUrl + '/towns/reload-gallery',

    section_value       : baseUrl + '/towns/section-value',
    section_save        : baseUrl + '/towns/save-section',
    section_quit        : baseUrl + '/towns/quit-section'
  },

  save:function(button){
    var self   = this;
    var action = "save";

    if( jQuery( self.form.article_id ).is("[data-id]") ){
      action = "update";
    }

    jQuery( self.form.basic_data + " dl dt label, fieldset#ckeditor legend").removeClass("missing-field");
    blockUI_ajax_saving(self.tabs.id,"on",self.msg.saving);

    var params = jQuery(self.form.basic_data).serialize() + "&action=" + action + "&btn="+ button +"&article=" + escape(CKEDITOR.instances[towns.form.ckeditor_article].getData());
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
        jQuery( towns.button.save +','+ towns.button.save_new +','+ towns.button.save_close +','+ towns.button.publicate ).removeClass('hide');
        jQuery( towns.button.next ).addClass('hide');
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
  
  clear_elements:function(){
    var self=this;

    // tab 1
      clear_form(self.form.basic_data);
      ckeditor_clear(self.form.ckeditor_article);
      jQuery('#tags').val('').trigger('liszt:updated');
    // tab 2
      self.disable_section_editor();
      jQuery(self.dom.section_image_results).attr("src","/media/images/section-not-available.png");
    // tab 3
      jQuery( self.dom.main_pix +','+  self.dom.zipped_gallery +','+ self.dom.links_rel +','+  self.dom.current_cors +','+ self.dom.coord_rel +','+ self.dom.gallery_uploaded +','+ self.dom.current_cors ).empty();
      var plUploader = jQuery('#uploader_f_gallery').pluploadQueue();
      plUploader.splice();
    // tab 4
      sheepItForm.removeAllForms();
      jQuery(self.dom.map_coors).attr("value","").removeAttr("data-initialized");
      jQuery(self.dom.current_cors).empty();

      blockUI_ajax_saving(self.tabs.id,"off");
      jQuery(self.form.article_id).attr("value","").removeAttr("data-id");
      jQuery(self.tabs.id).tabs('select', 0).tabs({disabled:[1,2,3]});
      jQuery( self.button.next ).removeClass('hide');
      jQuery( self.button.save +','+ self.button.save_new +','+ self.button.save_close +','+ self.button.publicate ).addClass('hide');
  },

  enable_section_editor:function(section){
    var self         = this;
    var section_desc = jQuery( 'a[data-section="'+ section +'"]' ).text();

    jQuery.ajax({
      type: 'GET',
      url:  towns.url.section_value,
      data: {section:section},
      beforeSend : function (){
        blockUI_ajax_saving(self.dom.section_info,"on",self.msg.load_section);
      },
      success: function(response){
        jQuery( towns.dom.section_info ).attr("data-section", section ).show();
        jQuery( towns.dom.section_info + ' legend' ).text( self.msg.section_legend_1 + section_desc + self.msg.section_legend_2 );
        jQuery( towns.dom.daSection_instructions ).hide();
        jQuery( towns.dom.daSectionCkeditor ).html(response)
        CKEDITOR.replace( self.form.ckeditor_section, {toolbar : 'sectionCreate',height : '20em',language : 'es'});

        blockUI_ajax_saving(self.dom.section_info,"off");
      },
      error: function(request, status, error){}
    });

  },

  disable_section_editor:function(){
    var self=this;
    jQuery( self.dom.section_info ).attr("data-section","").hide();
    jQuery( self.dom.daSection_instructions ).show();
  },

  section_save_and_close:function(){
    var self=this;
    self.save_section_value();
    self.disable_section_editor();
  },
  section_save_and_continue:function(){
    var self=this;
    self.save_section_value();
    towns.enable_section_editor( jQuery(self.dom.section_info).attr('data-section') ); // reload
  },

  save_section_value:function(){
    var self   = this;
    var params = jQuery(self.form.add_section).serialize() + "&section_desc=" + escape(CKEDITOR.instances[self.form.ckeditor_section].getData());

    jQuery.ajax({
      type: 'POST',
      data: params,
      dataType: 'json',
      url:  self.url.section_save,
      beforeSend : function (){
        blockUI_ajax_saving(self.dom.section_info,"on",self.msg.save_section);
      },
      success: function(response){
        if( ! is_number(response) ){
          alert(self.msg.error_update);
        }else{
          jQuery( "img." + jQuery("input#town_section").val() ).attr("src","/media/images/section-available.png");
        }
        blockUI_ajax_saving(self.dom.section_info,"off");
        return true;
      },
      error: function(request, status, error){
        alert(self.msg.error_update);
        blockUI_ajax_saving(self.dom.section_info,"off");
        self.disable_section_editor();
        return true;
      }
    });

  },

  quit_section:function( section ){
    var self=this;

    jQuery.ajax({
      type: 'POST',
      data: {section:section},
      dataType: 'json',
      url:  self.url.section_quit,
      beforeSend : function (){
        blockUI_ajax_saving(self.dom.section_info,"on",self.msg.save_section);
      },
      success: function(response){
        if( ! is_number(response) ){
          alert(self.msg.error_update);
        }else{
          jQuery( "img." + jQuery("input#town_section").val() ).attr("src","/media/images/section-not-available.png");
        }
        self.disable_section_editor();
        blockUI_ajax_saving(self.dom.section_info,"off");
        return true;
      },
      error: function(request, status, error){
        alert(self.msg.error_update);
        blockUI_ajax_saving(self.dom.section_info,"off");
        self.disable_section_editor();
        return true;
      }
    });

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
      beforeSend : function (){
        blockUI_ajax_saving(self.dom.gallery_uploaded,"on",self.msg.image_delete);
      },
      success: function(response){
        if( response.status == true ){
          //jQuery(self.dom.del_image_ul + " li").eq(li).remove();
          self.reload_gallery();
        }
        blockUI_ajax_saving(self.dom.gallery_uploaded,"off");
      },
      error: function(jqXHR, exception){
        jQuery(self.dom.del_image_response).html( self.msg.img_delete_error + " [" + exception + "]" ).addClass('error');
        blockUI_ajax_saving(self.dom.gallery_uploaded,"off");
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
  jQuery( towns.tabs.id + " ul li").click(function(){

    if( ! jQuery( towns.form.article_id ).is("[data-id]") ){
      jQuery(towns.tabs.id).tabs('select', 0);
    }
    if( jQuery(this).index() == 3 && ! jQuery(towns.dom.map_coors).is('[data-initialized]') ){
      initialize();
    }

  });

  /* save */
  jQuery(towns.button.next+','+towns.button.save +','+ towns.button.save_new +','+ towns.button.save_close +','+ towns.button.publicate).click(function(){
    towns.save( jQuery(this).attr('id') );
  });
  /* coordinates add */
  jQuery(towns.button.coordinates).click(function() {
    if ( ! jQuery(towns.dom.current_cors).is(':empty') ){
      towns.save_coordinates( jQuery(towns.dom.current_cors).text() );
    }
  });
  /* coordinates del */
  jQuery(towns.button.del_coordinates).click(function() {
    towns.del_coordinates();
  });
  /* links rel */
  jQuery(towns.button.links_rel).click(function() {
    towns.save_links_rel();
  });
  /* delete uploaded images */
  jQuery(document).on('click', towns.dom.del_image_span, function(){
    towns.del( jQuery(this).attr('data-id'), jQuery(this).parent().parent().index() );
  });

  jQuery(document).on('click', 'table.sections a', function(){
    towns.enable_section_editor( jQuery(this).attr('data-section') );
  });
  jQuery(document).on('click', 'span#save_section', function(){
    towns.section_save_and_continue();
  });
  jQuery(document).on('click', 'span#save_close_section', function(){
    towns.section_save_and_close();
  });
  jQuery(document).on('click', 'span#close_section', function(){
    towns.disable_section_editor();
  });
  jQuery(document).on('click', 'span#section_quit', function(){
    towns.quit_section( jQuery(this).attr('data-section') );
  });

});