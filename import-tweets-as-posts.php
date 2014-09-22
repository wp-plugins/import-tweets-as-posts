<?php   
/* Plugin Name: Import Tweets as Posts
 * Plugin URI:  http://wordpress.org/extend/plugins/import-tweets-as-posts
 * Description: Import tweets from user's timeline or search query under default post or custom post type "tweet" in WordPress.
 * Version: 2.0
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


// Register Custom Post Type
function tweets_post_type() {
  $post_type = get_option('itap_post_type');
  
  if($post_type=='tweet'){
    $labels = array(
      'name'                => _x( 'Tweets', 'Post Type General Name', 'text_domain' ),
      'singular_name'       => _x( 'Tweet', 'Post Type Singular Name', 'text_domain' ),
      'menu_name'           => __( 'Tweets', 'text_domain' ),
      'parent_item_colon'   => __( 'Parent Tweet:', 'text_domain' ),
      'all_items'           => __( 'All Tweets', 'text_domain' ),
      'view_item'           => __( 'View Tweet', 'text_domain' ),
      'add_new_item'        => __( 'Add New Tweet', 'text_domain' ),
      'add_new'             => __( 'New Tweet', 'text_domain' ),
      'edit_item'           => __( 'Edit Tweet', 'text_domain' ),
      'update_item'         => __( 'Update Tweet', 'text_domain' ),
      'search_items'        => __( 'Search Tweet', 'text_domain' ),
      'not_found'           => __( 'No tweet found', 'text_domain' ),
      'not_found_in_trash'  => __( 'No tweets found in Trash', 'text_domain' ),
    );
    $args = array(
      'label'               => __( 'tweet', 'text_domain' ),
      'description'         => __( 'Tweet information pages', 'text_domain' ),
      'labels'              => $labels,
      'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'custom-fields', ),
      'taxonomies'          => array( 'tweets' ),
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'show_in_nav_menus'   => true,
      'show_in_admin_bar'   => true,
      'menu_position'       => 5,
      'can_export'          => true,
      'has_archive'         => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'capability_type'     => 'post',
    );
    register_post_type( 'tweet', $args );
//    flush_rewrite_rules();
  }
  
}
// Hook into the 'init' action
add_action( 'init', 'tweets_post_type', 0 );


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
      $post_type = get_option('itap_post_type');
      
      if($tweet_from=='Search Query'){ // Import from search query
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
      
      
      $args = array(
        'posts_per_page' => 1, 
        'post_type' => $post_type,
        'meta_key' => '_tweet_id',
        'post_status' => $post_status_check,
        'order' => 'DESC'
      );
      
//      if($post_type=='post') $args['category'] = $twitter_posts_category;
      $posts = get_posts($args);
     
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
          $post_exist_args = array(
            'post_type' => $post_type,
            'post_status' => $post_status_check,
            'meta_key' => '_tweet_id',
            'meta_value' => $tweet_id,
          );
//          if($post_type=='post') $post_exist_args['category'] = $twitter_posts_category;
          $post_exist = get_posts($post_exist_args);
          if($post_exist) continue; // Do Nothing
            

          // Convert links to real links.
//          $pattern = '/http:(\S)+/';
          $pattern = "/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
          $replace = '<a href="${0}" target="_blank">${0}</a>';
          $tweet_text = preg_replace($pattern, $replace, $tweet->text);
          
          // Convert @ to follow
          $follow_pattern = '/(@([_a-z0-9\-]+))/i';
          $follow_replace = '<a href="https://twitter.com/${0}" target="_blank">${0}</a>';
          $tweet_text = preg_replace($follow_pattern, $follow_replace, $tweet_text);
          
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
//          $tweet_post_time = strtotime($tweet->created_at) + $tweet->user->utc_offset;
          
          $tweet_created_at = strtotime($tweet->created_at);
          $itap_set_timezone = get_option('itap_wp_time_as_published_date');
          
          if($itap_set_timezone=='yes'){
            $wp_offset = get_option('gmt_offset');
            if($wp_offset){
              $tweet_post_time = $tweet_created_at + ($wp_offset * 3600);
            }
          } else {
            $tweet_post_time = $tweet_created_at + $tweet->user->utc_offset;
          }
          $publish_date_time = date_i18n( 'Y-m-d H:i:s', $tweet_post_time );
          
          
          if(get_option('itap_post_title')){
            $twitter_post_title = get_option('itap_post_title') .' '. strip_tags(html_entity_decode($tweet_text));
          } else {
            $twitter_post_title = strip_tags(html_entity_decode($tweet_text));
          }
          

          $data = array(
            'post_content'   => $tweet_text,
            'post_title'     => $twitter_post_title,
            'post_status'    => $twitter_post_status,
            'post_type'      => $post_type,
            'post_author'    => 1,
            'post_date'      => $publish_date_time,
            'comment_status' => 'closed'
          );
          if($post_type == 'post') $data['post_category'] = array( $twitter_posts_category );
          $insert_id = wp_insert_post($data);

          // Add Featured Image to Post
          $tweet_media = $tweet->entities->media;
          if($tweet_media AND $insert_id){
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
          }

          if($insert_id){
            update_post_meta($insert_id, '_tweet_id', $tweet_id);
          }
        }
      } // end if
	  }
	} // end of import_tweets_as_posts_function
	
}
?>