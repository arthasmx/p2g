var contact = {
 div     : "div#guestbook-form",
 form    : "form#contact",
 result  : "form#contact .result",
 button  : "button#button",
 blockUI : ".block-ui",
 captcha:{
   id      : '#captcha-id',
   input   : '#captcha-input',
   refresh : '#captcha-refresh'
 },
 url:{
   send    : wwwUrl  + 'contact',
   refresh : wwwUrl  + 'captcha-contact-refresh',
 },     
 msg:{
   sending       : "Enviando mensaje, espere por favor...",
   error_sending : "No se pudo enviar el mensaje, completa el formulario e intentalo de nuevo",
   success       : "<div class='alert alert-success'><span class='icon-ok'></span> Mensaje enviado!</h2><p class='span12'>Tendrá una respuesta a su comentario lo mas pronto posible, muchas gracias</p></div>"
 },

	send:function(){
	  var self=this;

   jQuery.ajax({
     type: 'post',
     data: jQuery(self.form).serialize(),
     dataType: 'json',
     url:  self.url.send,
     beforeSend: function(objeto){
       jQuery(self.form + ' input, ' + self.form + ' textarea').removeClass('missing-field');
       blockUI_ajax_saving( self.blockUI ,"on",self.msg.sending,'70%',0,false);
     },
     success: function(response){

       if( is_number(response) ){
         jQuery( self.form ).html( self.msg.success );
       }else{
         self.refresh_captcha();

         jQuery.each(response, function(i,item) {

           if( response[i].field == 'captcha' ){
             jQuery(self.form + ' input[id^=captcha]').addClass("missing-field");
           }else{
             jQuery( self.form + " #" + response[i].field).addClass("missing-field");
           }

         });
         jQuery( self.result ).html( '<div class="alert alert-danger"> ' + self.msg.error_sending + '</div>' );
       }
       blockUI_ajax_saving( self.blockUI ,"off");

     },
     error: function(jqXHR, exception){
       self.refresh_captcha();
       alert( self.msg.error_sending + " [FRO-C55]" );
       blockUI_ajax_saving( self.blockUI ,"off");
     }
   });

	},

 refresh_captcha:function(){
   var self=this;
   jQuery.ajax({ 
       url: self.url.refresh, 
       dataType:'json',
       beforeSend:function(){
         jQuery(self.captcha.input).val('');
       },
       success: function(data) { 
           jQuery( self.form + ' img').attr('src', data.src); 
           jQuery( self.captcha.id ).attr('value', data.id); 
       }
   }); 
 }

};

jQuery(document).ready(function(){

  jQuery(contact.form + ' ' + contact.button).click(function(){
    contact.send();
  });

  jQuery(contact.captcha.refresh).click(function() {
    contact.refresh_captcha();
  }); 

});