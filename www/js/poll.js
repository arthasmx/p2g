
jQuery(document).ready(function(){
  jQuery(document).on("click", "ul.poll-options li", function(){
    if( jQuery(this).attr('data-id') ){
      poll( jQuery(this).addClass('chosen').parent().addClass('chosen').attr('data-poll_id'), jQuery(this).attr('data-id') );
    }
  });
});

  function poll(poll_id, vote){
    var url    = baseUrl + "poll/vote/" + poll_id + "/" + vote;
    var item   = 0;
    var colors = ['#0f0','#f00','#00f','#ff0','#0ff0'];

    $.ajax({
      url:  url,
      dataType: 'json',
      success: function(response){
        if( response == false ){
          jQuery('ul.poll-options li.voting').addClass('error').text(poll_on_error);
        }else{
          for (item = 0; item <= (response.options.length-1); item++){
            jQuery("li#graph_" + response.options[item].id).css("background-color",colors[item]).animate({width:response.options[item].percentage + "%"});
            jQuery('li[data-id="'+ response.options[item].id +'"]').html(response.options[item].option + ' ('+ response.options[item].percentage +'%)');
          }
          if( response.duplicated != "false"){
            jQuery('ul.poll-options li.voting').addClass('error').text(response.duplicated);
          }
        }
        return true;
      },
      error: function(request, status, error){
        jQuery('ul.poll-options li.voting').addClass('error').text(poll_on_error);
      }
    });
  }
