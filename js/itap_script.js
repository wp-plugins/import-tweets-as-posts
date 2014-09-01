/* **********************************************
    ITAP - Settings Page Script
********************************************** */
(function($){
	$(document).ready(function(){
    if($('#itap_tweet_from').length > 0){
      var import_type = $('#itap_tweet_from').val();
      
      if(import_type=='Search Query'){
        $('#itap_user_id, #itap_import_retweets, #itap_exclude_replies').parents('tr').hide();
      } else {
        $('#itap_search_string, #itap_search_result_type').parents('tr').hide();
      }
        
      $('#itap_tweet_from').on('change', function(){
        import_type = $(this).val();
        if(import_type=='Search Query'){
          $('#itap_search_string, #itap_search_result_type').parents('tr').show();
          $('#itap_user_id, #itap_import_retweets, #itap_exclude_replies').parents('tr').hide();
        } else {
          $('#itap_user_id, #itap_import_retweets, #itap_exclude_replies').parents('tr').show();
          $('#itap_search_string, #itap_search_result_type').parents('tr').hide();
        }
      });
    }
	
	});
})(jQuery);