<?php
if(!class_exists('ImportTweetsAsPosts_Settings')){
	class ImportTweetsAsPosts_Settings{
		/*= Construct the plugin object */
		public function __construct(){
			// register actions
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'add_menu'));
		}

    /*= hook into WP's admin_init action hook */
    public function admin_init(){
			// register your plugin's settings
			register_setting('import_tweets_as_posts-group', 'itap_tweet_from');
			register_setting('import_tweets_as_posts-group', 'itap_user_id');
			register_setting('import_tweets_as_posts-group', 'itap_search_string');
			register_setting('import_tweets_as_posts-group', 'itap_search_result_type');
			register_setting('import_tweets_as_posts-group', 'itap_consumer_key');
			register_setting('import_tweets_as_posts-group', 'itap_consumer_secret');
			register_setting('import_tweets_as_posts-group', 'itap_access_token');
			register_setting('import_tweets_as_posts-group', 'itap_access_token_secret');
			register_setting('import_tweets_as_posts-group', 'itap_post_title');
			register_setting('import_tweets_as_posts-group', 'itap_tweets_count');
			register_setting('import_tweets_as_posts-group', 'itap_interval_time');
			register_setting('import_tweets_as_posts-group', 'itap_assigned_category');
			register_setting('import_tweets_as_posts-group', 'itap_post_status');
			register_setting('import_tweets_as_posts-group', 'itap_import_retweets');
			register_setting('import_tweets_as_posts-group', 'itap_exclude_replies');

			// add your settings section
			add_settings_section(
				'import_tweets_as_posts-section',
				'',
				array(&$this, 'settings_section_import_tweets_as_posts'),
				'import_tweets_as_posts'
			);
			
			// add your setting's fields
      add_settings_field(
          'itap_tweet_from', //ID
          'Import Tweets From', // Title
          array(&$this, 'itap_settings_field'), // Callback
          'import_tweets_as_posts', //page
          'import_tweets_as_posts-section', // section
          array('field' => 'itap_tweet_from','field_type'=> 'selectbox') //argument
      );
      add_settings_field(
          'itap_user_id',
          'Twitter User ID',
          array(&$this, 'itap_settings_field'),
          'import_tweets_as_posts',
          'import_tweets_as_posts-section',
          array('field' => 'itap_user_id','field_type'=> 'input')
      );
      add_settings_field(
          'itap_search_string',
          'Twitter Search String',
          array(&$this, 'itap_settings_field'),
          'import_tweets_as_posts',
          'import_tweets_as_posts-section',
          array('field' => 'itap_search_string','field_type'=> 'input')
      );
      add_settings_field(
          'itap_search_result_type',
          'Twitter Search Result Type',
          array(&$this, 'itap_settings_field'),
          'import_tweets_as_posts',
          'import_tweets_as_posts-section',
          array('field' => 'itap_search_result_type','field_type'=> 'selectbox')
      );
      add_settings_field(
          'itap_consumer_key',
          'Twitter Consumer Key',
          array(&$this, 'itap_settings_field'),
          'import_tweets_as_posts',
          'import_tweets_as_posts-section',
          array('field' => 'itap_consumer_key','field_type'=> 'input') 
      );
			add_settings_field(
        'itap_consumer_secret',
        'Twitter Consumer Secret',
        array(&$this, 'itap_settings_field'),
        'import_tweets_as_posts',
        'import_tweets_as_posts-section',
        array('field' => 'itap_consumer_secret','field_type'=> 'input') 
      );
			add_settings_field(
        'itap_access_token',
        'Twitter Access Token',
        array(&$this, 'itap_settings_field'),
        'import_tweets_as_posts',
        'import_tweets_as_posts-section',
        array('field' => 'itap_access_token','field_type'=> 'input') 
      );
			add_settings_field(
        'itap_access_token_secret',
        'Twitter Access Token Secret',
        array(&$this, 'itap_settings_field'),
        'import_tweets_as_posts',
        'import_tweets_as_posts-section',
        array('field' => 'itap_access_token_secret','field_type'=> 'input') 
      );
			add_settings_field(
        'itap_post_title',
        'Twitter Post Title',
        array(&$this, 'itap_settings_field'),
        'import_tweets_as_posts',
        'import_tweets_as_posts-section',
        array('field' => 'itap_post_title','field_type'=> 'input')      
      );
			add_settings_field(
        'itap_tweets_count',
        'No. of Tweets to Import',
        array(&$this, 'itap_settings_field'),
        'import_tweets_as_posts',
        'import_tweets_as_posts-section',
        array('field' => 'itap_tweets_count','field_type'=> 'input')
      );
			add_settings_field(
        'itap_interval_time',
        'Tweets Imports Time Interval',
        array(&$this, 'itap_settings_field'),
        'import_tweets_as_posts',
        'import_tweets_as_posts-section',
        array('field' => 'itap_interval_time','field_type'=> 'input')
      );
			add_settings_field(
        'itap_assigned_category',
        'Assigned Category to Twitter Posts',
        array(&$this, 'itap_settings_field'),
        'import_tweets_as_posts',
        'import_tweets_as_posts-section',
        array('field' => 'itap_assigned_category','field_type'=> 'selectbox')
      );
			add_settings_field(
        'itap_post_status',
        'Twitter Posts Default Status',
        array(&$this, 'itap_settings_field'),
        'import_tweets_as_posts',
        'import_tweets_as_posts-section',
        array('field' => 'itap_post_status','field_type'=> 'selectbox')
      );
			add_settings_field(
        'itap_import_retweets',
        'Import Retweets',
        array(&$this, 'itap_settings_field'),
        'import_tweets_as_posts',
        'import_tweets_as_posts-section',
        array('field' => 'itap_import_retweets','field_type'=> 'selectbox')
      );
			add_settings_field(
        'itap_exclude_replies',
        'Exclude Replies',
        array(&$this, 'itap_settings_field'),
        'import_tweets_as_posts',
        'import_tweets_as_posts-section',
        array('field' => 'itap_exclude_replies','field_type'=> 'selectbox')
      );
    } // END public static function activate
        
    public function settings_section_import_tweets_as_posts(){
      // Think of this as help text for the section.
      echo 'All fields are required.';
    }
		
    /*= This function provides inputs for settings fields */
    public function itap_settings_field($args){
      // Get the field name from the $args array
      $field = $args['field'];
      $field_type = $args['field_type'];
      $value = get_option($field); // Get the value of this setting

      if($field_type=='input'){
        // echo a proper input type="text"
        echo sprintf('<input type="text" name="%s" id="%s" value="%s" width="200" />', $field, $field, $value);
        if($field=='itap_post_title'){
          echo '<span class="note">To display tweet text as post title, leave this field blank.</span>';
        }
        if($field == 'itap_search_string'){
          echo '<span class="note">Enter search text. For more reference <a href="https://dev.twitter.com/docs/using-search" target="_blank">https://dev.twitter.com/docs/using-search</a></span>';
        }
        if($field == 'itap_interval_time'){
          echo '<span class="note">Enter interval time in minutes (e.g. 5).</span>';
        }

      } else if($field_type=='selectbox'){
        _e('<select name="'.$field.'" id="'.$field.'">');

          if($field=='itap_assigned_category'){ // If field type list categories
            $categories = get_categories(array('hide_empty' => 0));
            if($categories){
              foreach($categories as $category){
                $selected = ($category->term_id==$value) ? 'selected' : '';
                _e('<option value="'. $category->term_id .'" '.$selected .'>'. $category->name .'</option>');
              }
            }
          } else if($field=='itap_post_status'){ // If field type post status
            $status_types = array('publish','draft');
            if($status_types){
              foreach($status_types as $type){
                $selected = ($type==$value) ? 'selected' : '';
                _e('<option value="'. $type .'" '.$selected .'>'. $type .'</option>');
              }
            }
          } else if($field=='itap_import_retweets' OR $field=='itap_exclude_replies'){
            $types = array('yes','no');
            if($types){
              foreach($types as $type){
                $selected = ($type==$value) ? 'selected' : '';
                _e('<option value="'. $type .'" '.$selected .'>'. $type .'</option>');
              }
            }
            
          } else if($field=='itap_tweet_from'){
            $types = array('User Timeline','Search Query');
            if($types){
              foreach($types as $type){
                $selected = ($type==$value) ? 'selected="selected"' : '';
                _e('<option value="'. $type .'" '.$selected .'>'. $type .'</option>');
              }
            }
          } else if($field == 'itap_search_result_type'){
            $types = array('mixed','recent','popular');
            if($types){
              foreach($types as $type){
                $selected = ($type==$value) ? 'selected' : '';
                _e('<option value="'. $type .'" '.$selected .'>'. $type .'</option>');
              }
            }
          }
          
        _e( '</select>' );
      }
    }
    
        
    /*= add a menu */	
    public function add_menu(){
			// Add a page to manage this plugin's settings
			add_options_page(
				'Import Tweets as Posts Settings',
				'Import Tweets as Posts',
				'manage_options',
				'import_tweets_as_posts',
				array(&$this, 'itap_plugin_settings_page')
			);
    } 
    
    /*= Menu Callback */	
    public function itap_plugin_settings_page(){
			if(!current_user_can('manage_options')){
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}
			_e('<div class="wrap">');
				echo '<h2>Import Tweets as Posts - Settings</h2>';
        
        echo '<div id="itap_settings_form_wrapper">';
          echo '<form method="post" action="options.php" id="itap_settings_form">';
            @settings_fields('import_tweets_as_posts-group');
            @do_settings_fields('import_tweets_as_posts-group'); 
            do_settings_sections('import_tweets_as_posts');
            @submit_button();
          echo '</form>';
          
          echo '<div id="donate-itap">
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="PU5W6BKWH8BQE">
            <input type="image" src="'. plugins_url('/images/paypal.png', __FILE__ ).'" border="0" name="submit" alt="PayPal Ð The safer, easier way to pay online.">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
            </form>
          </div>';
        echo '</div>';
			echo '</div>';
    } // END public function plugin_settings_page()
    
    
  } // END class ImportTweetsAsPosts_Settings
} // END if(!class_exists('ImportTweetsAsPosts_Settings'))