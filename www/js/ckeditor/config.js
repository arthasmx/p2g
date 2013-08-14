
CKEDITOR.editorConfig = function( config ){

 config.skin = 'moono-light';
 config.resize_enabled     = false;
 config.baseHref           = baseUrl;
 config.autoGrow_onStartup = true;

 config.removePlugins = 'elementspath,iframe,forms,flash,docprops,a11yhelp,about,adobeair,ajax,bbcode,iframedialog,image,pagebreak,preview,templates';

 config.extraPlugins = "readmore"

 config.toolbar_simple = [ { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Smiley' ] } ];

 config.toolbar_articleCreate =
 [
  { name: 'document', items : [ 'Font','FontSize', 'Bold','Italic','Underline','Strike','Subscript','Superscript', 'TextColor','BGColor','RemoveFormat', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl','-','SelectAll','ShowBlocks', 'Source' ] },
  { name: 'clipboard', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv', '-', 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo','-','Link','Unlink', '-' , 'Find','Replace','-', 'Table','HorizontalRule','Smiley','SpecialChar','ReadMore' ] }
 ];

 config.toolbar_sectionCreate =
 [
  { name: 'doc_1', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript', 'TextColor','BGColor','RemoveFormat', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl','-','SelectAll','ShowBlocks', 'Source' ] },
  { name: 'clipboard', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote', '-', 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo','-','Link','Unlink', '-' , 'Find','Replace','-', 'Table','HorizontalRule','SpecialChar' ] }
 ];

};