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
			register_setting('import_tweets_as_posts-group', 'itap_user_id');
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

			// add your settings section
			add_settings_section(
				'import_tweets_as_posts-section',
				'',
				array(&$this, 'settings_section_import_tweets_as_posts'),
				'import_tweets_as_posts'
			);
			
			// add your setting's fields
            add_settings_field(
                'itap_user_id', //ID
                'Twitter User ID', // Title
                array(&$this, 'settings_field_input_text'), // Callback
                'import_tweets_as_posts', //page
                'import_tweets_as_posts-section', // section
                array('field' => 'itap_user_id') //argument
            );
            add_settings_field(
                'itap_consumer_key',
                'Twitter Consumer Key',
                array(&$this, 'settings_field_input_text'),
                'import_tweets_as_posts',
                'import_tweets_as_posts-section',
                array('field' => 'itap_consumer_key')
            );
			add_settings_field(
                'itap_consumer_secret',
                'Twitter Consumer Secret',
                array(&$this, 'settings_field_input_text'),
                'import_tweets_as_posts',
                'import_tweets_as_posts-section',
                array('field' => 'itap_consumer_secret')
            );
			add_settings_field(
                'itap_access_token',
                'Twitter Access Token',
                array(&$this, 'settings_field_input_text'),
                'import_tweets_as_posts',
                'import_tweets_as_posts-section',
                array('field' => 'itap_access_token')
            );
			add_settings_field(
                'itap_access_token_secret',
                'Twitter Access Token Secret',
                array(&$this, 'settings_field_input_text'),
                'import_tweets_as_posts',
                'import_tweets_as_posts-section',
                array('field' => 'itap_access_token_secret')
            );
			add_settings_field(
                'itap_post_title',
                'Twitter Post Title',
                array(&$this, 'settings_field_input_text'),
                'import_tweets_as_posts',
                'import_tweets_as_posts-section',
                array('field' => 'itap_post_title')
            );
			add_settings_field(
                'itap_tweets_count',
                'No. of Tweets to Import',
                array(&$this, 'settings_field_input_text'),
                'import_tweets_as_posts',
                'import_tweets_as_posts-section',
                array('field' => 'itap_tweets_count')
            );
			add_settings_field(
                'itap_interval_time',
                'Tweets Imports Time Interval <br />(In minutes)',
                array(&$this, 'settings_field_input_text'),
                'import_tweets_as_posts',
                'import_tweets_as_posts-section',
                array('field' => 'itap_interval_time')
            );
			add_settings_field(
                'itap_assigned_category',
                'Assigned Category to Twitter Posts',
                array(&$this, 'settings_field_categories'),
                'import_tweets_as_posts',
                'import_tweets_as_posts-section',
                array('field' => 'itap_assigned_category')
            );
			add_settings_field(
                'itap_post_status',
                'Twitter Posts Default Status',
                array(&$this, 'settings_field_post_status'),
                'import_tweets_as_posts',
                'import_tweets_as_posts-section',
                array('field' => 'itap_post_status')
            );
			add_settings_field(
                'itap_import_retweets',
                'Import Retweets',
                array(&$this, 'settings_field_retweets'),
                'import_tweets_as_posts',
                'import_tweets_as_posts-section',
                array('field' => 'itap_import_retweets')
            );
        } // END public static function activate
        
        public function settings_section_import_tweets_as_posts(){
			// Think of this as help text for the section.
			echo 'All fields are required.';
		}
		
        /*= This function provides text inputs for settings fields */
        public function settings_field_input_text($args){
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = get_option($field);
            // echo a proper input type="text"
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" width="200" />', $field, $field, $value);
        } 
		
        /*= This function provides categories dropdown inputs for settings fields */
		public function settings_field_categories($args){
			$categories = get_categories(array('hide_empty' => 0));
			if($categories){
				$field = $args['field'];
				$value = get_option($field);
				_e('<select name="'.$field.'" id="'.$field.'">');
				foreach($categories as $category){
					$selected = ($category->term_id==$value) ? 'selected' : '';
					_e('<option value="'. $category->term_id .'" '.$selected .'>'. $category->name .'</option>');
				}
				_e( '</select>' );
			}
		}
		
		/*= This function provides post status dropdown inputs for settings fields */
		public function settings_field_post_status($args){
			$status_types = array('publish','draft');
			if($status_types){
				$field = $args['field'];
				$value = get_option($field);
				_e('<select name="'.$field.'" id="'.$field.'">');
				foreach($status_types as $type){
					$selected = ($type==$value) ? 'selected' : '';
					_e('<option value="'. $type .'" '.$selected .'>'. $type .'</option>');
				}
				_e( '</select>' );
			}
		}
		
		/*= This function provides retweets dropdown inputs for settings fields */
		public function settings_field_retweets($args){
			$types = array('yes','no');
			if($types){
				$field = $args['field'];
				$value = get_option($field);
				_e('<select name="'.$field.'" id="'.$field.'">');
				foreach($types as $type){
					$selected = ($type==$value) ? 'selected' : '';
					_e('<option value="'. $type .'" '.$selected .'>'. $type .'</option>');
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
				// Form Style CSS
				_e('<style type="text/css">
					#itap_settings_form{ border-top: 1px solid #d3d3d3; padding: 30px 0; } 
					#itap_settings_form table{ background: #efefef; width: 60%; }
					#itap_settings_form table th{ border-bottom: 3px solid #fff; line-height: 20px; }
					#itap_settings_form table td{ border-bottom: 3px solid #fff; padding: 8px 10px; }
					#itap_settings_form table td input{ width: 100%; padding: 6px 10px; margin: 0; color: #666;}
				</style>');
				
				echo '<h2>Import Tweets as Posts - Settings</h2>';
				echo '<form method="post" action="options.php" id="itap_settings_form">';
				
				@settings_fields('import_tweets_as_posts-group');
				@do_settings_fields('import_tweets_as_posts-group'); 
				do_settings_sections('import_tweets_as_posts');
				@submit_button();
				echo '</form>';
			echo '</div>';

        } // END public function plugin_settings_page()
    } // END class ImportTweetsAsPosts_Settings
} // END if(!class_exists('ImportTweetsAsPosts_Settings'))