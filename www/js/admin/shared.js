var shared= {

  form:{
    status : 'form select.status_change'
  },
  msg:{
    ok:{
      saved  : 'Datos guardados',
      saving : 'Guardando...'
    },
    error:{
      saved : 'No fué posible guardar los datos',
      error : 'Ocurrió un error de aplicación, favor de reportarlo de inmediato'
    }
  },

  status:function(selected,form,tr,td,url){
    saving( tr, 'on');
    jQuery(selected).attr('disabled','true');

    $.ajax({
      type: 'POST',
      data: form,
      url:  url,
      success: function(response){
        if(response=='false'){
          jQuery(selected).removeAttr('disabled').attr('title',self.msg.error.saved);
          jQuery(td).addClass('td_error');
        }
        jQuery(selected).removeAttr('disabled');
        saving( tr, 'off');
      },

      error: function(request, status, error){
        alert(self.msg.error.error);
      }
    });
  }

};


jQuery(document).ready(function(){

});