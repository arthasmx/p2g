jQuery(document).ready(function(){

//main picture
  var mp_filelist = '#main-pix-filelist';
  var mp_up_btn   = '#main-pix-upload';
  var mp_uploader = new plupload.Uploader( get_shared_options( baseUrl + '/social/upload-main-pix','uploader_f_main_pix','main-pix-pick','jpg') );
  jQuery(mp_up_btn).click(function(e) {
    mp_uploader.start();
    e.preventDefault();
  });
  mp_uploader.init();
  mp_uploader.bind('FilesAdded', function(up, files) {
    jQuery(mp_filelist).empty();
    if( up.files.length > 1 ){
      mp_uploader.removeFile(mp_uploader.files[0]);
    }
    jQuery(mp_filelist).append('<div id=' + files[0].id + '>' + files[0].name + ' (' + plupload.formatSize(files[0].size) + ') <b>0%</b>' + '</div>');
    up.refresh();
  });
  mp_uploader.bind('UploadProgress', function(up, file) {
    jQuery('#' + file.id + ' b').html(file.percent + '%');
  });
  mp_uploader.bind('Error', function(up, err) {
    jQuery(mp_filelist).html('<div>Error: ' + err.code + ', Message: ' + err.message + '</div>' );
    up.refresh();
  });
  mp_uploader.bind('FileUploaded', function(up, file) {
    jQuery('#' + file.id + ' b').html('100%');
  });
//main picture
// ** END **

// gallery
  jQuery('#uploader_f_gallery').pluploadQueue( get_shared_jqUIwidget_options('/social/upload','jpg','5','100', 'gallery',false,true) );

});

function get_shared_options(route,container,btn,ext){
  return {
    runtimes        : 'html5,flash,browserplus',
    container       : container,
    browse_button   : btn,
    max_file_size   : '5mb',
    chunk_size      : '1mb',
    unique_names    : true,
    multi_selection : false,
    filters         : [{title : 'Image files', extensions : ext}],
    flash_swf_url   : jsUrl + '/plupload/plupload_flash.swf',
    url             : route
  };
}

function get_shared_jqUIwidget_options(route,ext,size,files,reload_container,rename,unique){
  return {
    runtimes        : 'html5,flash',
    url             : baseUrl + route,
    max_file_size   : size + 'mb',
    max_file_count  : files,
    rename          : rename,
    unique_names    : unique,
    multiple_queues : true,
    filters         : [ {title : 'Image files', extensions : ext}],
    flash_swf_url   : jsUrl + '/plupload/plupload_flash.swf',
    preinit : {
      UploadFile: function(up, file) {
        up.settings.multipart_params = {image_name : $("#business option:selected").text() };
      },
      UploadComplete: function(up, files) {
        if( validate_param(reload_container) ){
          switch(reload_container){
            case 'gallery':
              social.reload_gallery();
              break;
          }
        }
      }
    }
  };  
}
