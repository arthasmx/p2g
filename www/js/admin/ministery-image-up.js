var up = {
  uploader : '#user_min',
  url      : baseUrl + '/ministeries/upload-picture',

	 upload:function( files ){
	   var self=this;

	   if (window.FormData) {
	     var formdata = new FormData();
	   }else{
	     alert('El navegador que utilizas no cuenta con los plugins necesarios para subir imágenes en esta sección.');
	     return false;
	   }

   if( files[0] ){
     file = files[0];

      if (formdata) {
        formdata.append("user_min", file );
        formdata.append("avatar", jQuery('#avatar').val() );
        formdata.append("user", jQuery('#user').val() );

        jQuery.ajax({
          url: self.url,
          type: "POST",
          data: formdata,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function (resp) {
            if( resp.status==true ){
              jQuery("#BrowserVisible").css("background-image", 'url(' + wwwUrl + "/media" + jQuery('#avatar').val() + '?rand='+ Math.random() +')' );
            }else{
              alert('No se pudo subir la imágen al ministerio [ERM34]')
            }

          }
        });

      }
    }
	 }

};

jQuery(document).ready(function(){

  jQuery(document).on('change', up.uploader, function(){
    up.upload( this.files );
  });

});