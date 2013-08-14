var banner = {
  blockUI: "#tabs-1",
  msg:{
    status        : "Actualizando estado de banner, espere...",
    preview_error : "No fue posible cargar el banner",
    error_update  : "No se pudo guardar sus cambios. ",
  },
  dom:{
    status  : "#current_status",
    username: "#username",
    preview : "div.uploaded_images"
  },
  button:{
    update   : "span#update"
  },
  url:{
    listing             : baseUrl + '/articles/list',
    save                : baseUrl + '/articles/save',

    preview : baseUrl + '/banner/preview',
    user_status : baseUrl + '/banner/user-status'
  },

  preview:function(){
    var self=this;

    jQuery.ajax({
      type: 'get',
      url:  self.url.preview,
      success: function(response){
        jQuery( self.dom.preview ).html( response );
        //$('a.cBox-gallery').colorbox({rel:'cBox-gallery'});
      },
      error: function(jqXHR, exception){
        jQuery( self.dom.preview ).html( self.msg.preview_error + "[" + exception + "]" );
      }
    });
  },

  user_status:function(value){
    var self=this;
    if( ! validate_param(value) ){
      return false;
    }
    blockUI_ajax_saving( self.blockUI ,"on", self.msg.status,'40%',0,false);
    var user = jQuery( self.dom.username ).val();

    jQuery.ajax({
      type: 'post',
      data: {username:user,value:value},
      dataType: 'json',
      url:  self.url.user_status,
      success: function(response){
        redirect( baseUrl );
        return false;
      },
      error: function(jqXHR, exception){
        alert( self.msg.error_update + "[BNR54]" );
        blockUI_ajax_saving( self.blockUI ,"off");
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
  }


};


jQuery(document).ready(function(){

  jQuery( banner.button.update ).click(function(){
    banner.user_status( jQuery( banner.dom.status ).val() );
  });

});