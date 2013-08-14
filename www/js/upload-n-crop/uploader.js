var uploader = {
  crop  : false,
  ratio : "4:3",
  dom:{
    target    : "#upload-file",
    indicator : "#upload-status",
    uploaded  : "#uploaded-image",
    crop      : ""
  },
  url:{
    upNcrop : baseUrl + "/uploader/up-n-crop",
    image   : baseUrl + "/uploader/image-upload",
    file    : baseUrl + "/uploader/file",
    audio   : baseUrl + "/uploader/audio"
  },
  get_url_by_type:function(type){
    var self  = this;
    self.crop = false;

    if(type == 'upNcrop'){
      self.crop = true;
      return self.url.upNcrop;
    }
    if(type == 'file'){
      return self.url.file;
    }
    if(type == 'audio'){
      return self.url.audio;
    }

    return self.url.image;
  },
  upload:function(section,target,type){
    var self=this;
    if(typeof(section)=='undefined'){
      return false;
    }
    if(typeof(target)!='undefined'){
      self.dom.target = target
    }

    var myUpload = jQuery( self.dom.target ).upload({
      enctype    : 'multipart/form-data',
      params     : {section:section},
      autoSubmit : true,

      name       : 'image',
      action     : self.get_url_by_type(type),

      onSubmit   : function(){
        jQuery( self.dom.indicator ).html('').hide();
        // loadingmessage('Please wait, uploading file...', 'show');
        alert('subiendo...');
      },

      onComplete : function(response) {
        if( responseType =="success" ){

          if( self.crop == true ){
            response           = unescape(response);
            var response       = response.split("|");
            var responseType   = response[0];
            var responseMsg    = response[1];
            var current_width  = response[2];
            var current_height = response[3];

            jQuery( self.dom.uploaded ).find('#thumbnail').imgAreaSelect({
              x1 : 120,
              y1 : 90,
              x2 : 280,
              y2 : 210,
              aspectRatio : self.ratio

            });
          }

        }else{
          alert('Unexpected Error. Please try again\n' + response);
        }
      }
    });

  }

};


jQuery(document).ready(function(){



});