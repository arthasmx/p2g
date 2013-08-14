var fp = {
  paginator_div : 'div.f-pagination',

  gallery:function(route,page,container,rel){
    var self    = this;
    if( ! validate_param(route) || ! validate_param(page) || ! validate_param(container) || ! validate_param(rel) ){
      return false;
    }

    var loading_element = jQuery(self.paginator_div).parent().attr('class');
    loading( "div." + loading_element );

    $.ajax({
      type: 'GET',
      url:  baseUrl + route + page,
      success: function(response){
        jQuery( "div." + loading_element).html(response);
        $(container).colorbox({rel:rel});
      },
      error: function(request, status, error){
        jQuery( "div." + loading_element ).html("<div class='f-pagination-error'>No se encontraron archivos a mostrar</div>");
      }
    });

  }

/*
	 paginate:function(url,page,cBox_container,cBox_rel){
	   var self    = this;
	   if( typeof(page)=="undefined" ){
	     return false;
	   }

	   var fp_type = jQuery(self.paginator_div).parent().attr('class');
	   loading( "div." + fp_type );

	   $.ajax({
	     type: 'GET',
	     url:  self.url + page,
	     success: function(response){
	       jQuery( "div." + fp_type ).html(response);
	       console.log( cBox_container )
	       if( validate_param(cBox_container) && validate_param(cBox_rel) ){
	         // jQuery( 'a.cBox-gallery' ).colorbox({rel:'cBox-gallery'});

	         $(document).on("click", "a.cBox-gallery", function (event) {
	           event.preventDefault();
	           $.colorbox({ rel:"cBox-gallery" });
	         });

	       }
	     },
	     error: function(request, status, error){
	       jQuery( "div." + fp_type ).html("<div class='f-pagination-error'>No se encontraron archivos a mostrar</div>");
	     }
	   });
	 }
*/

};