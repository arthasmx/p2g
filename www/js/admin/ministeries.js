var ministeries = {
  msg:{
    saving     : "Guardando ministerios, espere por favor...",
    loading    : "Cargando detalles, espere por favor...",
    error_save : "No se pudo guardar sus cambios. "
  },
  dom:{
    list_grid     : "#ministeries-listing",
    list_toolbar  : "div.custom-cbox-bar",
    listing_action_shared  : "span#listing_action",
    main_pix               : "#main-pix-filelist"
  },
  button:{
    save   : "span#save",
    clear  : "span#clear"
  },
  form:{
    user_mini   : "#user-ministeries",
    ministeries : "#multi_mini",
    multi       : "#multiselect"
  },
  url:{
    save : baseUrl + '/ministeries/save',
    save_multiple : baseUrl + '/ministeries/save-multiple'
  },

  save:function(){
    var self   = this;

    jQuery.ajax({
      type: 'post',
      data: jQuery(self.form.user_mini).serialize(),
      dataType: 'json',
      url:  self.url.save,
      beforeSend: function(objeto){
        blockUI_ajax_saving('.ui-dialog',"on",self.msg.saving,'70%');
      },
      success: function(response){
        blockUI_ajax_saving('.ui-dialog',"off");

        if( response.status==false) {
          alert( self.msg.error_save + " [ERJ40]" );
        }else{
          jQuery('#edit-ministeries').dialog('close');
        }
      },
      error: function(jqXHR, exception){
        alert( self.msg.error_save + " [ERJ45]" );
        blockUI_ajax_saving('.ui-dialog',"off");
      }
    });

  },

  save_multiple:function(){
    var self   = this;
    var ids = jQuery( self.dom.list_grid ).getGridParam("selarrrow");
    params = jQuery(self.form.multi).serialize();

    jQuery.ajax({
      type: 'post',
      data: {mini:$("#multi_mini").val(), ids:ids},
      dataType: 'json',
      url:  self.url.save_multiple,
      beforeSend: function(objeto){
        blockUI_ajax_saving('#min-1',"on",self.msg.saving,'50%');
      },
      success: function(response){
        blockUI_ajax_saving('#min-1',"off");
        jQuery( ministeries.form.ministeries + ' option:selected').removeAttr('selected');
        jQuery('#ministeries-listing').jqGrid('resetSelection');
        jQuery( 'fieldset#min-options' ).addClass('hide');
      },
      error: function(jqXHR, exception){
        blockUI_ajax_saving('#min-1',"off");
        alert( self.msg.error_save + " [ERJ66]" );
      }
    });

  }

};


jQuery(document).ready(function(){

  /* save */
  jQuery(ministeries.button.save).click(function(){
    if( jQuery( ministeries.form.ministeries + ' option:selected').length <= 0 ){
      alert('Debes seleccionar una opción mínimamente');
      return false;
    }else{
      ministeries.save_multiple();
    }

  });
  /* clear */
  jQuery(document).on('click', ministeries.button.clear, function(){
    jQuery( ministeries.form.ministeries + ' option:selected').removeAttr('selected');
  });

});