<?php 
/*
Plugin Name: VideographyWP Lite
Plugin URI: https://videographywp.com
Description: Add featured YouTube videos to any WordPress post and additionally import title, description and featured image.
Version: 1.0.17
Author: CodeFlavors
Author URI: https://videographywp.com
Domain Path: /languages
Text Domain: videographywp
*/	

// No direct access
if( !defined( 'ABSPATH' ) ){
	die();
}

if( defined( 'CVWP_PATH' ) ){
	/**
	 * Display a notice if both Lite and PRO versions are active
	 */
	function cvwp_double_ver_notice(){
		$plugin = plugin_basename( __FILE__ );
		$deactivate_url = wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin, 'deactivate-plugin_' . $plugin );
		?>
		<div class="notice notice-error is-dismissible">
	        <p><?php printf( __( 'You have installed <strong>VideographyWP PRO</strong> by CodeFlavors. You should %sdeactivate VideographyWP Lite%s to benefit of all the extra PRO features.', 'videographywp' ), '<a href="' . $deactivate_url . '">', '</a>' ); ?></p>
	    </div>
		<?php
	}	
	add_action( 'admin_notices', 'cvwp_double_ver_notice' );
	return;
}

/**
 * Plugin path
 * @var string
 */
define( 'CVWP_PATH'		, plugin_dir_path(__FILE__) );
/**
 * Plugin URL
 * @var string
 */
define( 'CVWP_URL'		, plugin_dir_url(__FILE__) );
/**
 * Plugin version
 * @var string
 */
define( 'CVWP_VERSION'	, '1.0.17');
/**
 * Query videos by video URL
 * @var boolean
 */
define( 'CVWP_GET_BY_URL', true ); // when true, video queries will be performed by providing the video URL

// plugin function
require_once CVWP_PATH . 'includes/functions.php';
// templating functions
require_once cvwp_path( 'includes/template.php' );

/**
 * Plugin class. Starts and sets the plugin.
 *
 * @since 1.0
 * @package VideographyWP plugin
 */
class CVWP_Plugin{	
	/**
	 * Constructor
	 */
	static function start(){	
		// init function
		add_action( 'init', array( 'CVWP_Plugin', 'on_init' ), -999);
		
		add_filter( 'the_content', array( 'CVWP_Plugin', 'filter_the_content' ), -999 );
		
		add_filter( 'post_thumbnail_html', array( 'CVWP_Plugin', 'filter_thumbnail_html' ), 999, 5 );
		
		// load the widgets
		add_action( 'widgets_init', array( 'CVWP_Plugin', 'load_widgets' ) );
		
		// plugin activation hook
		register_activation_hook(  __FILE__,  array( 'CVWP_Plugin', 'install' ) );		
	}
	
	/**
	 * Action 'init' callback.
	 * @return void
	 */
	static function on_init(){
		// start shortcodes
		require_once cvwp_path( 'includes/libs/class-cvwp-shortcodes.php' );

		// include WooCommerce compatibility
		require_once cvwp_path( 'includes/libs/woocommerce/class-cvwp-woocommerce.php' );
		new CVWP_Woocommerce();

		require_once cvwp_path( 'includes/libs/class-amp.php' );
		new CVWP_Amp();

		require_once cvwp_path( 'includes/libs/elementor/class-cvwp-elementor.php' );
		new CVWP_Elementor();

		// only for admin area
		if( is_admin() ){
			// localization - needed only for admin area
			load_plugin_textdomain( 'videographywp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			
			// add admin specific functions
			require_once cvwp_path( 'includes/admin/functions.php' );
			
			// add administration management
			require_once cvwp_path( 'includes/admin/libs/class-cvwp-admin.php' );
		}				
	}
	
	/**
	 * Filter the content to embed the video into the post content,
	 * above or below it according to user settings.
	 *
	 * @param string $content
	 * @return string
	 */
	static function filter_the_content( $content ){
		global $post;
		if( !$post ){
			return $content;
		}
		
		// check if plugin embedding is allowed
		if( !cvwp_is_embed_allowed() ){
			if( __cvwp_disallow_plugin_embeds() ){
				$message = cvwp_plugin_message( 'Automatic video embedding prevented by plugin filter set in theme or other plugin.' );
			}else{
				$message = cvwp_plugin_message( 'Automatic video embedding prevented by plugin options.' );
			}	
			return $message . "\n" . $content;
		}
		
		// get post options
		$options = cvwp_get_post_options( $post->ID );
		
		if( !cvwp_has_video() ){
			return $content;
		}

		/**
		 * Filter to allow videos to be embedded by the plugin.
		 * @var boolean
		 */
		$allow = apply_filters( 'cvwp_allow_video_embed' , true, $post, $options['embed_position'] );

		if( !$allow ){
			$message = cvwp_plugin_message( 'Automatic video embedding prevented by plugin filter.' );
			return $message . "\n" . $content;
		}

		if( !in_array( $options['embed_position'] , array('above_content', 'below_content') ) ){
			/**
			 * Apply a filter on content that allows its modification.
			 * The plugin uses this filter to add the video URL on AMP pages if location is not
			 * above or below the content (in which case, AMP doesn't embed the video URL)
			 *
			 * @param string $content
			 * @param WP_Post $post
			 * @param array $options
			 */
			$content = apply_filters( 'cvwp_video_outside_content', $content, $post, $options['embed_position'] );
			return $content;
		}

		/**
		 * Filter that allows altering the embedding of the video
		 * @var string HTML
		 */
		$output = apply_filters( 'cvwp_video_embed_html', cvwp_video_output( '', '', true, false ), $post, $options['embed_position'] );

		if( 'above_content' == $options['embed_position'] ){
			$content = $output . "\n" . $content;
		}else{
			$content .= "\n" . $output;
		}
		
		return $content;
	}

	/**
	 * Filter post thumbnails and embed videos in case the setting in on for the current post.
	 * Use only first 2 parameters because there are themes that apply this filter with only first 2
	 * parameters and this caused errors and doesn't display the product gallery.
	 *
	 * @param string $html
	 * @param int $post_id
	 * @param int $post_thumbnail_id
	 * @param array $size
	 * @param array $attr
	 * @return string
	 */
	static function filter_thumbnail_html( $html, $post_id /*, $post_thumbnail_id, $size, $attr*/ ){
		// check if plugin embedding is allowed
		if( !cvwp_is_embed_allowed() ){
			if( __cvwp_disallow_plugin_embeds() ){
				$message = cvwp_plugin_message( 'Automatic video embedding prevented by plugin filter set in theme or other plugin.' );
			}else{
				$message = cvwp_plugin_message( 'Automatic video embedding prevented by plugin options.' );
			}
			return $message . "\n" . $html;
		}

		// get post options
		$options = cvwp_get_post_options( $post_id );

		if( !cvwp_has_video() || 'featured_image' != $options['embed_position'] ){
			return $html;
		}

		/**
		 * Filter to allow videos to be embedded by the plugin.
		 * @var boolean
		 */
		$allow = apply_filters( 'cvwp_allow_video_embed' , true, get_post( $post_id ), $options['embed_position'] );

		if( !$allow ){
			$message = cvwp_plugin_message( 'Automatic video embedding prevented by plugin filter.' );
			return $message . "\n" . $html;
		}

		/**
		 * Filter that allows altering the embedding of the video
		 * @var string HTML
		 */
		$html = apply_filters( 'cvwp_video_embed_html', cvwp_video_output( '', '', true, false ), get_post( $post_id ), $options['embed_position'] );

		return $html;
	}
	
	/**
	 * Starts the plugin widgets.
	 * @return void
	 */
	static function load_widgets(){
		// no widgets so far
		return;
	}
	
	/**
	 * Plugin activation hook callback function.
	 * Performs any maintenance actions needed to be done on activation.
	 */
	static function install(){
		// get current plugin details
		$plugin_details = cvwp_get_options( 'plugin_details' );
		
		// update plugin info
		$update_details = array(
			'version' 			=> CVWP_VERSION,
			'previous_version' 	=> $plugin_details['version'],
			'wp_version' 		=> get_bloginfo( 'version', 'raw' ),
			'activated_on' 		=> current_time( 'mysql' )
		);
		cvwp_update_options( 'plugin_details' , $update_details );
		
		// set a transient on plugin activation to allow it to redirect user to about page and get more details about the plugin
		self::activate_about_page();
	}
	
	/**
	 * On activation it will set up a transient that flags the plugin 
	 * it was just activated and will redirect user to about plugin page.
	 *
	 * @return void
	 */
	static function activate_about_page(){
		set_transient( 'cvwp_about_page_activated' , time(), 30 );
	}
}

CVWP_Plugin::start();