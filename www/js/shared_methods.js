
function is_jason(data){
  if(typeof(data)=='undefined'){
    return false;
  }
  try{
    var data_checked = jQuery.parseJSON(data);
    return data_checked;
  }catch(e){
    return false;
  }
}

function loading(target) {
  jQuery(target).show().html("<div class='ajax-loading'></div>");
}

function show_error(msg,target_id,seconds_delay) {
  jQuery(target_id).html(msg).show();
  if( validate_param(seconds_delay) ){
    jQuery(target_id).delay(seconds_delay).fadeOut(seconds_delay);
  }
}

function numeric_values_only(value) {
  var chars = {
     allowed: "1234567890"
  };

  valid_value="";
  for (var i in value) {
   char=value[i];
   if (chars.allowed.indexOf(value[i])!=-1) {
    valid_value+=value[i];
   }
  }
 return valid_value;
}

function pop_up(route,id){
  nueva = window.open(route + "?id=" + escape(id) ,'',CONFIG='HEIGHT=230,WIDTH=300,TOOLBAR=no,MENUBAR=no,SCROLLBARS=no,RESIZABLE=no,LOCATION=no,DIRECTORIES=no,STATUS=no');
  return false;
}

function show_div(target){
  if(typeof(target)=='undefined'){
    return false;
  }
  jQuery(target).removeClass('hide').addClass('shown').hide().fadeIn('slow');
}

function hide_div(target){
  if(typeof(target)=='undefined'){
    return false;
  }
  jQuery(target).removeClass('shown').fadeOut('slow').addClass('hide');
}

function add_hidden_element(name, value, target){
  jQuery('<input />').attr('type', 'hidden').attr('name', name).attr('value', value).appendTo(target);
}

function set_element_position(from_this_element, to_this_element, left_value, top_value){
  var base_position = jQuery(from_this_element).position();

  if( base_position ){
    if( is_number(left_value) ) { jQuery(to_this_element).css('left', base_position.left + left_value) }
    if( is_number(top_value) )  { jQuery(to_this_element).css('top' , base_position.top  + top_value) }
  }
  return true;
}

function is_number(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

function validate_param(param){
  if( typeof(param) == "undefined" ){
    return false;
  }
  return ( param != false );
}

function saving(target,status,element_class){
  element_class = (element_class) ? element_class : 'saving' ;
  if(status=="on") {
    jQuery(target).addClass(element_class);
  }
  if(status=="off") {
    jQuery(target).removeClass(element_class);
  }
}

function ajax_saving(target,status,W,H){
  if(status=="on") {
    jQuery("<div class='ajax_saving'></div>").prependTo(target).css({ 'height':H,'width':W });
  }else{
    jQuery(target + " div.ajax_saving").remove();
  }
}

function blockUI_ajax_saving(target,on_off,msg,the_width,delay,go_up){
  if( typeof(go_top)=='undefined' || go_up==true){
    go_top();
  }

  if( on_off=="off" ){
    jQuery(target).unblock();
    return true;
  }

  if( typeof(target) == "undefined" ){
    return false;
  }
  if( typeof(msg) == "undefined" ){
    msg = "<div class='ajax_saving'></div>";
  }
  if( typeof(delay) == "undefined" ){
    delay = 0;
  }
  if( typeof(the_width) == "undefined" ){
    the_width = '30%';
  }

  jQuery(target).block({
    message: "<h1 style='padding:20px;' class='blockUI'>" + msg + "</h1>",
    timeout: delay,
    css        : { border  : '3px solid #94B52C', width: the_width },
    overlayCSS : { opacity : 0.7, background:'#fff' }
  });
}

function string_to_seo(string){
  var to_replace = {".": "-"," ": "-","_": "-",",": "-",":": "-","á": "a","é": "e","í": "i","ó": "o","ú": "u","à": "a","è": "e","ì": "i","ò": "o","ù": "u","ä": "a","ë": "e","ï": "i","ö": "o","ü": "u","ñ": "n","ç": "c"};
  var allowed    = "abcdefghijklmnopqrstuvwxyz-1234567890";
  var string     = string.toLowerCase();
  var seo        = "";

  for (var i in to_replace){
    string = string.split(i).join(to_replace[i]);
  }

  for (var i in string){
    char = string[i];
    if (allowed.indexOf(string[i])!=-1) {
      seo += string[i];
    }
  }

  seo = seo.split("----").join("-").split("---").join("-").split("--").join("-");
  return seo;
}

function go_top(){
  window.scrollTo(0,0);
}

function clear_form(container){
  jQuery( container + ' input[type=text], ' + container + ' select, ' + container + ' input[type=hidden],' + container + ' textarea').each(function(index, value) {
    jQuery(this).val('');
  });
}

function ckeditor_clear(instance){
  jQuery("span#cke_" + instance + " iframe").contents().find("body").empty();
}

function appendo_clear(instance){
  jQuery("table#"+ instance +" tr:gt(1)").remove();
  jQuery("fieldset#link_"+ instance +" div.appendoButtons").children().eq(1).hide();
}

function redirect(url){
  window.location = url;
  return false;
}