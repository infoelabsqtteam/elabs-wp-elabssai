=== WordPress Featured Video - VideographyWP ===
Contributors: codeflavors, constantin.boiangiu
Tags:  video plugin, vimeo, video, featured video, YouTube
Requires at least: 5.6
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.17

WordPress featured video plugin that allows you to create video posts from YouTube videos. 

== Description ==

VideographyWP is a [WordPress featured video plugin](https://videographywp.com?utm_source=wordpressorg&utm_medium=readme_url&utm_campaign=videographywp "WordPress featured video plugin - VideographyWP") that can import video content from YouTube into your WordPress post and embed the video automatically, optionally replacing the post featured image.

[WordPress featured video plugin](https://videographywp.com?utm_source=wordpressorg&utm_medium=readme_url&utm_campaign=videographywp "WordPress featured video plugin") can **automatically fill the post title and post content** with the details retrieved from YouTube and it can also **set the post featured image automatically** allowing you to avoid having to copy/paste the details.

**Please note that Vimeo and Dailymotion videos can only be imported by VideographyWP PRO.**

https://www.youtube.com/watch?v=cXPJqf9OlKE

This WordPress featured video plugin automates the process of attaching videos to posts or pages. By offering plenty of embedding options for each individual video platform, the plugin allows you to customize the look of your embeds to better suit your needs. 

WordPress featured video can be displayed above or below the post content, it can replace the post featured image with a video or embed the video by using shortcodes that you can place anywhere in your post content where you want the video to be displayed.

**Features**

* Responsive WordPress featured video;
* Imports all video content (video title, featured image, content);
* Multiple embedding methods: lazy load, regular embedding, modal window, button;
* Lots of embedding options (video size, volume, video loop, platform specific settings);
* Multiple embedding positions: above or below content, replace post featured image, shortcode placement, etc.

**PRO version additional features**

* [WooCommerce product video](https://videographywp.com/woocommerce-product-video/?utm_source=wordpressorg&utm_medium=readme_woo&utm_campaign=videographywp "WooCommerce product video - VideographypWP");
* Compatible with any WooCommerce WordPress theme that follows standards and uses the default WooCommerce product gallery;
* Compatible with WooCommerce product gallery of premium themes Flatsome (by UX-themes), Basel (by xtemos), Unicon (by minti), Patron (created by Themedy, powered by Genesis) and Bridge (by QODE);
* Additional video sources (Vimeo, Dailymotion and Vine);
* WooCommerce product gallery specific video settings.

**Theme compatibility**

This WordPress video plugin is compatible with any WP theme that follows the coding standards. 

**Links:**

* [Plugin settings explained](https://videographywp.com/documentation/getting-started/plugin-settings/?utm_source=wordpressorg&utm_medium=readme_docs&utm_campaign=videographywp "VideographyWP settings");
* [How to get YouTube API key](https://videographywp.com/documentation/getting-started/licence-api-keys/?utm_source=wordpressorg&utm_medium=readme_docs&utm_campaign=videographywp "How to get VideographyWP YouTube API key");
* [How to import videos with VideographyWP](https://videographywp.com/documentation/getting-started/importing-videos/?utm_source=wordpressorg&utm_medium=readme_docs&utm_campaign=videographywp "How to import videos with VideographyWP");
* [How to use VideographyWP in your WordPress theme](https://videographywp.com/documentation/advanced-tutorials/integrate-videographywp-with-my-theme/?utm_source=wordpressorg&utm_medium=readme_docs&utm_campaign=videographywp "How to use VideographyWP in your WordPress theme");

== Installation ==

**Before updating, make sure you back up all your custom-made themes.**

* Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly;
* Activate the plugin through the 'Plugins' screen in WordPress;
* Use the Settings->VideographyWP Lite screen to configure the plugin.
* Set your YouTube API key

https://youtu.be/6xye7Fddttk

== Changelog ==
= 1.0.17 =
- Solved a bug that prevented the about page to be viewed.

= 1.0.16 =
- Solved deprecation notices for PHP 8.3.+ and checked compatibility with WordPress 6.5.

= 1.0.15 =
- Query YouTube videos without providing an API key (some limitation apply: image will be low resolution and video description won't be retrieved);
- Replaced usage of block editor JavaScript functions that were under deprecation notice;
- Added message under video importer when using it without an API key;
- Added a warning message on the post edit screen when the video position is set to replace the featured image. This informs the user that they must set a featured image for the post in order for the video to replace it;
- Allow YouTube key testing in plugin Settings without having to save the settings first.

= 1.0.14 =
- Solved a bug when querying YouTube videos using the share link (ie. https://youtu.be/abc123?feature=shared) which resulted in the video being reported as not found.

= 1.0.13 =
- Solved bug when querying YouTube shorts with GET variable in the URL (ie. https://youtube.com/shorts/abc123?feature=shared)

= 1.0.12 =
- Added support for YouTube shorts.

= 1.0.11 =
- Added filter "cvwp_allow_plugin_messages" that can be used to prevent the plugin from displaying messages into the page source code. [See the example](https://gist.github.com/constantin-b/7731ab13932dd754064d9a610e2670a2).

= 1.0.10 =
- Solved a bug when editing a post with an YouTube video attached to it which caused annotations option to not be saved.

= 1.0.9 =
- Added compatibility with Elementor PRO post templates which allows replacement of featured image with featured VideographyWP Lite video when using the templates to display posts.

= 1.0.8 =
- Solved a bug that was attaching multiple video query events to "Query video" button when "Remove attached video" was clicked.

= 1.0.7 =
Solved a bug involving Gutenberg detection that in some cases was failing.

= 1.0.6 =
Added option to loop video;
Added option to embed videos without cookies;
Added option to add featured video on all public post types except WooCommerce products.

= 1.0.5 =
Added YouTube API key verification in plugin Settings page under APIs tab;
Added video tutorial to YouTube API key tab in plugin Settings page that shows how the API key can be generated;
Added new Settings tab "WooCommerce" that advertises PRO plugin functionality to embed videos in WooCommerce product gallery.

= 1.0.4 =
Solved a bug that generated a "Bad request" error when a query was made for YouTube video (caused by white space inserted before or after the API key in plugin settings);
Solved a display issue in post edit screen that caused a over sized loading icon to be displayed while video was loaded.

= 1.0.3 =
Solved a JS bug that prevented the post title, content and featured image to be set in post edit screen if the video was attached to the post in a previous step;
Solved jQuery migrate notices;
Converted administration plugin settings fields for numbers (ie. video volume, video width) from type text to type number;
Added compatibility with Gutenberg editor (classic editor will work as well);
Added more detailed error messages when a query for a video can't be performed.

= 1.0.2 =
Added support for plugin "AMP for WordPress"

= 1.0.1 =
Added a friendly, dismissible reminder to leave a review on WordPress.org

= 1.0 =

Initial release.