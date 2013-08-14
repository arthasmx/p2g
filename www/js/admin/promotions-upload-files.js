jQuery(document).ready(function(){

  var mp_filelist = '#promotions-filelist';
  var mp_up_btn   = '#promotions-upload';
  var mp_uploader = new plupload.Uploader( get_shared_options( baseUrl + '/promotions/upload','promotions_pix','promotions-pick','jpg') );
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
    promotions.preview();
  });
// ** END **

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