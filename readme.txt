=== Import Tweets as Posts ===
Contributors: Chandan Kumar
Plugin Name:  Import Tweets as Posts
Plugin URI:   http://plugins.svn.wordpress.org/import-tweets-as-posts/
Author URI:   http://www.chandankumar.in
Author:       Chandan Kumar
Donate link:  http://chandankumar.in
Tags: Import tweets as posts, tweets to posts, twitter feeds, posts, import tweets, import tweets to blog
Requires at least: 2.8.6
Tested up to: 3.8.1
Stable tag: 1.0
License: GPLv2

== Description ==

"Import Tweets as Posts" allows users to easily import their twitter timeline feeds as Posts in WordPress. User can specify the import interval time, Category, Post Title. There is also an option to exclude retweets to import from user's twitter timeline.

Released under the terms of the GNU GPL, version 2.
http://www.fsf.org/licensing/licenses/gpl.html

NO WARRANTY.
Copyright (c) 2014 Chandan Kumar


== Installation ==

1. Extract the import-tweets-as-posts/ folder file to /wp-content/plugins/

2. Activate the plugin at your blog's Admin -> Plugins screen

3. Go to Plugin Settings under admin menu settings -> Import Tweets as Posts

4. Enter Twitter OAuth Keys (consumer key, consumer secret, access token, access token secret) in plugin settings fields (See FAQs)

6. Also make the following fields settings as per your requirements:
   <ul>
<li>Twitter ID</li>
<li>Tweets Title</li>
<li>No. of Tweets to Import</li>
<li>Tweets Imports Time Interval</li>
<li>Assigned Category</li>
<li>Post Status</li>
<li>Import Retweets</li>
</ul>

Note: All fields are required to work this plugin more efficiently.


== Upgrade Notice ==

= No Upgrade =


== Screenshots ==
...

== Changelog ==
No change till now.


== Frequently Asked Questions ==

= How do I get Twitter OAuth keys? =
To create your Twitter OAuth API Keys for the first time, just go through the steps:
<ol>
<li>Go to https://dev.twitter.com/apps/new and log in, if necessary</li>
<li>Supply the necessary required fields, accept the TOS, and solve the CAPTCHA.</li>
<li>Submit the form</li>
<li>Copy the consumer key and consumer secret from the screen into your application</li>
<li>Ensure that your application is configured correctly with the permission level you need (read-only, read-write, read-write-with-direct messages).</li>
<li>On the application's detail page, invoke the "Your access token" feature to automatically negotiate the access token at the permission level you need.</li>
<li>Copy the indicated access token and access token secret from the screen into your application. Be sure and configure your application as needed before attempting the "your access token" step.</li>
</ol>

=Can I schedule twitter feed check/import interval time?  =
Yes, you can specify the schedule time in field called "Tweets Imports Time Interval" under plugin settings.

= Send your queries =
http://chandankumar.in
