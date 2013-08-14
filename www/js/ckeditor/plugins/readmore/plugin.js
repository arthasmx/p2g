
CKEDITOR.plugins.add( 'readmore', {
 requires: 'fakeobjects',

 onLoad: function() {
  var cssStyles = [
   '{',
    'background: url(' + CKEDITOR.getUrl( this.path + 'icons/read-more.png' ) + ') no-repeat center center;',
    'clear: both;',
    'width:100%; _width:99.9%;',
    'padding:32px;',
    'margin:0 5px;',
    'height: 5px;',
    'cursor: default;',
   '}'
   ].join( '' ).replace( /;/g, ' !important;' ); 

  CKEDITOR.addCss( 'span.read-more' + cssStyles );
 },
 init: function( editor ) {
  if ( editor.blockless )
   return;

  editor.addCommand( 'readmore', CKEDITOR.plugins.readmoreCmd );

  editor.ui.addButton && editor.ui.addButton( 'ReadMore', {
   label: 'Read More...',
   command: 'readmore',
   icon: this.path + 'icons/read.png'
  });

 }
});

CKEDITOR.plugins.readmoreCmd = {
 exec: function( editor ) {
  editor.insertHtml('<!--fx--read-more--xf--><span class=\x22read-more\x22>&nbsp;</span>');
 }
};
