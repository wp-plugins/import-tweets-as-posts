<?php
if(!class_exists('ImportTweetsAsPosts_Settings')){
  class ImportTweetsAsPosts_Settings{
    /*= Construct the plugin object */
    var $fields;
    
	public function __construct(){
      //field_id as key => feild title/label, field type
      $this->fields = array(
        'itap_tweet_from' => array('title'=>'Import Tweets From', 'type'=>'selectbox'),
        'itap_user_id' => array('title'=>'Twitter User ID', 'type'=>'input'),
        'itap_search_string' => array('title'=>'Twitter Search String', 'type'=>'input'),
        'itap_search_result_type' => array('title'=>'Twitter Search Result Type', 'type'=>'selectbox'),
        'itap_consumer_key' => array('title'=>'Twitter Consumer Key', 'type'=>'input'),
        'itap_consumer_secret' => array('title'=>'Twitter Consumer Secret', 'type'=>'input'),
        'itap_access_token' => array('title'=>'Twitter Access Token', 'type'=>'input'),
        'itap_access_token_secret' => array('title'=>'witter Access Token Secret', 'type'=>'input'),
        'itap_tweets_count' => array('title'=>'No. of Tweets to Import', 'type'=>'input'),
        'itap_interval_time' => array('title'=>'Tweets Imports Time Interval', 'type'=>'input'),
        'itap_post_title' => array('title'=>'Tweets Post Title Prefix', 'type'=>'input'),
        'itap_post_title_limit' => array('title'=>'Tweets Post Title Characters Limit', 'type'=>'input'),
        'itap_post_type' => array('title'=>'Tweets Post Type', 'type'=>'selectbox'),
        'itap_assigned_category' => array('title'=>'Assigned Category to Twitter Posts', 'type'=>'selectbox'),
        'itap_post_status' => array('title'=>'Twitter Posts Default Status', 'type'=>'selectbox'),
        'itap_import_retweets' => array('title'=>'Import Retweets', 'type'=>'selectbox'),
        'itap_exclude_replies' => array('title'=>'Exclude Replies', 'type'=>'selectbox'),
        'itap_wp_time_as_published_date' => array('title'=>'Publish date as WordPress Timezone', 'type'=>'selectbox')
      );
      
      // register actions
      add_action('admin_init', array(&$this, 'admin_init'));
      add_action('admin_menu', array(&$this, 'add_menu'));
    }

    /*= hook into WP's admin_init action hook */
    public function admin_init(){
      // Plugins Settings Section
      add_settings_section(
        'import_tweets_as_posts-section', '',
        array(&$this, 'settings_section_itap'),
        'import_tweets_as_posts'
      );
      
      // Register feilds and their settings
      if($this->fields){
        foreach($this->fields as $key => $value){
          //Register Field
          register_setting('import_tweets_as_posts-group', $key); // field group, feild name
          
          //Add Field Settings
          add_settings_field(
            $key, $value['title'], // field id, field label
            array(&$this, 'itap_settings_field'), // Callback
            'import_tweets_as_posts', //page
            'import_tweets_as_posts-section', // section
            array('field' => $key,'field_type'=> $value['type']) //argument
          );
        }
      }
    } // END public static function activate
    
        
    public function settings_section_itap(){
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
          _e('<span class="note">Add some prefix to twitter post title.</span>');
        } else if($field == 'itap_search_string'){
          _e('<span class="note">Enter search text. For more reference <a href="https://dev.twitter.com/docs/using-search" target="_blank">https://dev.twitter.com/docs/using-search</a></span>');
        } else if($field == 'itap_interval_time'){
          _e('<span class="note">Enter interval time in minutes (e.g. 5).</span>');
        } else if($field == 'itap_post_title_limit'){
          _e('<span class="note">Enter post title characters limit (e.g. 40).</span>');
        }
      
      } else if($field_type=='selectbox'){
        _e('<select name="'.$field.'" id="'.$field.'">');

          if($field=='itap_post_type'){ // If field type list categories
            $itap_post_types = array('post','tweet');
            if($itap_post_types){
              foreach($itap_post_types as $itap_post_type){
                $selected = ($itap_post_type==$value) ? 'selected' : '';
                _e('<option value="'. $itap_post_type .'" '.$selected .'>'. $itap_post_type .'</option>');
              }
            }
          } else if($field=='itap_assigned_category'){ // If field type list categories
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
          } else if($field == 'itap_wp_time_as_published_date'){
            $types = array('no','yes');
            if($types){
              foreach($types as $type){
                $selected = ($type==$value) ? 'selected' : '';
                _e('<option value="'. $type .'" '.$selected .'>'. $type .'</option>');
              }
            }
          }
        _e( '</select><br />' );
        
        if($field == 'itap_wp_time_as_published_date'){
           _e('<span class="note">By default, Tweet publish date will be set as per twitter timzone setting.');
        }
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
      _e('<h2>Import Tweets as Posts - Settings</h2>');
        
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
            <input type="image" src="'. plugins_url('/images/btn_donate.gif', __FILE__ ).'" border="0" name="submit" alt="PayPal The safer, easier way to pay online.">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
            </form>
            <em><small>Donate some cheers to ITAP plugin. :)</small></em> 
          </div>'; // end #donate-itap
        echo '</div>';
        
      _e('</div>'); // end .wrap
    } // END public function plugin_settings_page()
    
    
  } // END class ImportTweetsAsPosts_Settings
} // END if(!class_exists('ImportTweetsAsPosts_Settings'))