<?php   
/* Plugin Name: Import Tweets as Posts
 * Plugin URI:  http://wordpress.org/extend/plugins/import-tweets-as-posts
 * Description: This plugin will read tweets from user's timeline or search query and import them as posts in WordPress.
 * Version: 1.4
 * Author: Chandan Kumar
 * Author URI: http://www.chandankumar.in/
 * License: GPL2
 
Copyright 2014 Chandan Kumar (email : chandanonline4u@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
*/

session_start();
$ITAP_Plugin = plugin_basename(__FILE__);

// Include Files
require_once(sprintf("%s/twitteroauth.php", dirname(__FILE__)));
require_once(sprintf("%s/itap-settings.php", dirname(__FILE__)));
require_once(ABSPATH . 'wp-admin/includes/image.php');
$ITAP_Settings = new ImportTweetsAsPosts_Settings();


/*= The activation hook is executed when the plugin is activated.
-------------------------------------------------------------------- */
register_activation_hook(__FILE__,'itap_crontasks_activation');
function itap_crontasks_activation(){  
	wp_schedule_event(time(), 'interval_minutes', 'import_tweets_as_posts');
}

/*= The deactivation hook is executed when the plugin is deactivated
----------------------------------------------------------------------- */
register_deactivation_hook(__FILE__,'itap_crontasks_deactivation');
function itap_crontasks_deactivation(){
	wp_clear_scheduled_hook('import_tweets_as_posts');
}

/*= Add the settings link to the plugins page
----------------------------------------------------------------------- */
add_filter("plugin_action_links_$ITAP_Plugin", 'itap_plugin_settings_link');
function itap_plugin_settings_link($links){
	$settings_link = '<a href="options-general.php?page=import_tweets_as_posts">Settings</a>';
	array_unshift($links, $settings_link);
	return $links;
}


/*= Add once 5 minute interval to wp schedules
-------------------------------------------------- */
add_filter('cron_schedules', 'import_interval_minutes');
function import_interval_minutes($interval) {
	$interval_time = (get_option('itap_interval_time')) ? (get_option('itap_interval_time') * 60) : (1*60) ;
	$interval['interval_minutes'] = array('interval' => $interval_time, 'display' => __('Every $interval_time minutes') );
	return $interval;
}


/*= Include ITAP Setting Page Style and Script
-------------------------------------------------- */
function itap_settings_enqueue() {
  wp_register_style('itap_setting_style', plugins_url('css/itap_style.css',__FILE__ ));
  wp_enqueue_style('itap_setting_style');
  wp_register_script( 'itap_setting_script', plugins_url('js/itap_script.js',__FILE__ ));
  wp_enqueue_script('itap_setting_script');
}
add_action( 'admin_init','itap_settings_enqueue');



if($ITAP_Settings){
	/*= Function to import tweets as posts
	----------------------------------------------------------- */
	add_action('import_tweets_as_posts','import_tweets_as_posts_function');
	function import_tweets_as_posts_function(){
	  $post_tweet_id;
	  if( ( get_option('itap_user_id')<>'' OR get_option('itap_search_string')<>'') AND get_option('itap_consumer_key')<>'' AND 
      get_option('itap_consumer_secret')<>'' AND get_option('itap_access_token')<>'' AND get_option('itap_access_token_secret')<>'' ){
		
      $tweet_from = get_option('itap_tweet_from');
      $twitteruser = get_option('itap_user_id');
      $tweet_search_string = get_option('itap_search_string');
      $search_result_type = get_option('itap_search_result_type');
      
      $consumerkey = get_option('itap_consumer_key');
      $consumersecret = get_option('itap_consumer_secret');
      $accesstoken = get_option('itap_access_token');
      $accesstokensecret = get_option('itap_access_token_secret');

      $notweets = (get_option('itap_tweets_count')) ? get_option('itap_tweets_count') : 30;
      $twitter_posts_category = get_option('itap_assigned_category');
      $twitter_post_status = get_option('itap_post_status');
      $import_retweets = get_option('itap_import_retweets');
      $exclude_replies = get_option('itap_exclude_replies');

      $connection = new TwitterOAuth($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);
      $post_status_check =  array('publish','pending','draft','auto-draft', 'future', 'private', 'inherit','schedule');
      
      $args = array(
        'posts_per_page' => 1, 
        'category' => $twitter_posts_category, 
        'meta_key' => '_tweet_id',
        'order' => 'DESC',
        'post_status' => $post_status_check
      );
      $posts = get_posts($args);
     
      
      if($tweet_from=='Search Query'){
        $tweet_api_url = "https://api.twitter.com/1.1/search/tweets.json?q=".  rawurlencode($tweet_search_string) ."&result_type=".$search_result_type."&count=".$notweets;
        
      } else { // Import from user timeline
        $tweet_api_url = "https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitteruser."&count=".$notweets;
        if($import_retweets=='no'){
          $tweet_api_url .= "&include_rts=false";
        }
        if($exclude_replies=='yes'){
          $tweet_api_url .= "&exclude_replies=true";
        }
      }
      
      
      if($posts){
        foreach($posts as $post){
          $post_tweet_id = get_post_meta($post->ID, '_tweet_id', true);
        }
        if($post_tweet_id){
          $tweet_api_url .= "&since_id=".$post_tweet_id; // Get twitter feeds after the recent tweet (by id) in WordPress database
        }
      }
      
      
      $tweets = $connection->get($tweet_api_url);
      if($tweet_from=='Search Query'){
        $tweets = $tweets->statuses;
      }
      
      if($tweets){
        foreach($tweets as $tweet){
          $tweet_id = abs((int)$tweet->id);
          $post_exist = get_posts(array(
            'category' => $twitter_posts_category, 
            'meta_key' => '_tweet_id',
            'meta_value' => $tweet_id,
            'post_status' => $post_status_check
          ));
          if($post_exist) continue; // Do Nothing
            

          // Message. Convert links to real links.
          $pattern = '/http:(\S)+/';
          $replace = '<a href="${0}" target="_blank">${0}</a>';
          $tweet_text = preg_replace($pattern, $replace, $tweet->text);
          
          // Link Search Querys under tweet text
          $hashtags = $tweet->entities->hashtags;
          if($hashtags){
            foreach($hashtags as $hashtag){
              $hashFindPattern = "/#". $hashtag->text ."/";
              $hashUrl = 'https://twitter.com/hashtag/'. $hashtag->text .'?src=hash';
              $hashReplace = '<a href="'.$hashUrl.'" target="_blank">#'. $hashtag->text .'</a>';
              $tweet_text = preg_replace($hashFindPattern, $hashReplace, $tweet_text);
            }
          }

          // Set tweet time as post publish date
          $tweet_time = strtotime($tweet->created_at) + $tweet->user->utc_offset;
          $publish_date_time = date_i18n( 'Y-m-d H:i:s', $tweet_time );

          if(get_option('itap_post_title')){
            $twitter_post_title = get_option('itap_post_title') .' ('. $tweet_id .')';
          } else {
            $twitter_post_title = strip_tags(html_entity_decode($tweet_text));
          }

          $data = array(
            'post_content'   => $tweet_text,
            'post_title'     => $twitter_post_title,
            'post_status'    => $twitter_post_status,
            'post_type'      => 'post',
            'post_author'    => 1,
            'post_date'      => $publish_date_time,
            'post_category'  => array( $twitter_posts_category ),
            'comment_status' => 'closed'
          ); 
          $insert_id = wp_insert_post($data);

          // Add Featured Image to Post
          $tweet_media = $tweet->entities->media;
          $tweet_media_url = $tweet_media[0]->media_url; // Define the image URL here
          $upload_dir = wp_upload_dir(); // Set upload folder
          $image_data = file_get_contents($tweet_media_url); // Get image data
          $filename   = basename($tweet_media_url); // Create image file name

          // Check folder permission and define file location
          if( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
          } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
          }

          // Create the image  file on the server
          file_put_contents( $file, $image_data );

          // Check image file type
          $wp_filetype = wp_check_filetype( $filename, null );

          // Set attachment data
          $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name( $filename ),
            'post_content'   => '',
            'post_status'    => 'inherit'
          );

          // Create the attachment
          $attach_id = wp_insert_attachment( $attachment, $file, $insert_id );

          // Define attachment metadata
          $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

          // Assign metadata to attachment
          wp_update_attachment_metadata( $attach_id, $attach_data );

          // And finally assign featured image to post
          set_post_thumbnail( $insert_id, $attach_id );


          if($insert_id){
            update_post_meta($insert_id, '_tweet_id', $tweet_id);
          }
        }
      } // end if
	  }
	} // end of import_tweets_as_posts_function
	
}
?>