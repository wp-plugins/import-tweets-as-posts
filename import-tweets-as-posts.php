<?php   
/* Plugin Name: Import Tweets as Posts
 * Plugin URI:  http://wordpress.org/extend/plugins/import-tweets-as-posts
 * Description: This plugin will read tweets from user's timeline and import them as posts in WordPress.
 * Version: 1.0
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

if($ITAP_Settings){
	/*= Function to import tweets as posts
	----------------------------------------------------------- */
	add_action('import_tweets_as_posts','import_tweets_as_posts_function');
	function import_tweets_as_posts_function(){
	  $post_tweet_id;
	  if(get_option('itap_user_id')<>'' AND get_option('itap_consumer_key')<>'' AND 
		get_option('itap_consumer_secret')<>'' AND get_option('itap_access_token')<>'' AND get_option('itap_access_token_secret')<>'' ){
		
		$twitteruser = get_option('itap_user_id');
		$consumerkey = get_option('itap_consumer_key');
		$consumersecret = get_option('itap_consumer_secret');
		$accesstoken = get_option('itap_access_token');
		$accesstokensecret = get_option('itap_access_token_secret');
		
		$notweets = (get_option('itap_tweets_count')) ? get_option('itap_tweets_count') : 30;
		$twitter_posts_category = get_option('itap_assigned_category');
		$twitter_post_title = (get_option('itap_post_title')) ? get_option('itap_post_title') : 'Tweet';
		$twitter_post_status = get_option('itap_post_status');

		$connection = new TwitterOAuth($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);
		
		$args = array(
			'posts_per_page' => 1, 
			'category' => $twitter_posts_category, 
			'meta_key' => '_tweet_id',
			'order' => 'DESC',
			'post_status' => $twitter_post_status
		);
		$posts = get_posts($args);
		$user_timeline_url = "https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitteruser."&count=".$notweets;
		if($posts){
			foreach($posts as $post){
			  $post_tweet_id = get_post_meta($post->ID, '_tweet_id', true);
			}
			// Get twitter feeds after the recent tweet (by id) in WordPress database
			$user_timeline_url .= "&since_id".$post_tweet_id;
		}
		$tweets = $connection->get($user_timeline_url);

		if($tweets){
			$import_retweets = get_option('itap_import_retweets');
			
			foreach($tweets as $tweet){
				if($import_retweets=='false'){
				  if($tweet->retweeted) continue;
				}
			  
				$tweet_id = abs((int)$tweet->id);
				$post = get_posts(array(
					'category' => $twitter_posts_category, 
					'meta_key' => '_tweet_id',
					'meta_value' => $tweet_id,
					'post_status' => $twitter_post_status
				));
				if($post) continue;

				// Message. Convert links to real links.
				$pattern = '/http:(\S)+/';
				$replace = '<a href="${0}" target="_blank">${0}</a>';
				$text = preg_replace($pattern, $replace, $tweet->text);
				$data = array(
					'post_content'   => $text,
					'post_title'     =>  $twitter_post_title .' ('. $tweet_id .')',
					'post_status'    => $twitter_post_status,
					'post_type'      => 'post',
					'post_author'    => 1,
					'post_date'      => date('Y-m-d H:i:s', strtotime($tweet->created_at)),
					'post_category'  => array( $twitter_posts_category ),
					'comment_status' => 'closed'
				); 

				$insert_id = wp_insert_post($data);
				if($insert_id){
					update_post_meta($insert_id, '_tweet_id', $tweet_id);
				}
			}
		} // end if
	  }
	} // end of import_tweets_as_posts_function
	
}
?>

