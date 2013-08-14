jQuery(document).ready(function(){

// main picture
  var mp_filelist = '#main-pix-filelist';
  var mp_up_btn   = '#main-pix-upload';
  var mp_uploader = new plupload.Uploader( get_shared_options( baseUrl + '/articles-up/upload-main-pix','uploader_f_main_pix','main-pix-pick','jpg') );
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

//gallery zip file
  var zip_filelist = '#zip-filelist';
  var zip_up_btn   = '#zip-upload';
  var zip_uploader = new plupload.Uploader( get_shared_options( baseUrl + '/articles-up/zip-gallery','uploader_f_zipped','zipped-file-pick','zip') );
  jQuery(zip_up_btn).click(function(e) {
    zip_uploader.start();
    e.preventDefault();
  });
  zip_uploader.init();
  zip_uploader.bind('FilesAdded', function(up, files) {
    jQuery(zip_filelist).empty();
    if( up.files.length > 1 ){
      zip_uploader.removeFile(zip_uploader.files[0]);
    }
    jQuery(zip_filelist).append('<div id=' + files[0].id + '>' + files[0].name + ' (' + plupload.formatSize(files[0].size) + ') <b>0%</b>' + '</div>');
    up.refresh();
  });
  zip_uploader.bind('UploadProgress', function(up, file) {
    jQuery('#' + file.id + ' b').html(file.percent + '%');
  });
  zip_uploader.bind('Error', function(up, err) {
    jQuery(zip_filelist).html('<div>Error: ' + err.code + ', Message: ' + err.message + '</div>' );
    up.refresh();
  });
  zip_uploader.bind('FileUploaded', function(up, file) {
    jQuery('#' + file.id + ' b').html('100%');
    articles.reload_gallery();
  });
// gallery zip file
// ** END **


// gallery
  jQuery('#uploader_f_gallery').pluploadQueue( get_shared_jqUIwidget_options('/articles-up/images-to-gallery','jpg','5','100', 'gallery',false,true) );
// mp3
  jQuery('#upload_f_audio').pluploadQueue( get_shared_jqUIwidget_options('/articles-up/upload-audio','mp3','5','15','audio',false,true) );
// files
  jQuery('#upload_f_docs').pluploadQueue( get_shared_jqUIwidget_options('/articles-up/upload-docs','doc,xls,pdf,zip,rar,txt','5','20','files',true,false) );
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
      UploadComplete: function(up, files) {
        if( validate_param(reload_container) ){
          switch(reload_container){
            case 'gallery':
              articles.reload_gallery();
              break;
            case 'audio':
              articles.reload_audio();
              break;
            case 'files':
              articles.reload_files();
              break;
          }
        }
      }
    }
  };  
}
