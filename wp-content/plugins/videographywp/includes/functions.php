<?php
// No direct access
if( !defined( 'ABSPATH' ) ){
	die();
}

// add WP 3.9.0 wp_normalize_path if unavailable
if( !function_exists('wp_normalize_path') ){
	/**
	 * Normalize a filesystem path.
	 *
	 * Replaces backslashes with forward slashes for Windows systems,
	 * and ensures no duplicate slashes exist.
	 *
	 * @since 3.9.0
	 *
	 * @param string $path Path to normalize.
	 * @return string Normalized path.
	 */
	function wp_normalize_path( $path ) {
		$path = str_replace( '\\', '/', $path );
		$path = preg_replace( '|/+|','/', $path );
		return $path;
	}
}

/**
 * Returns absolute path within the plugin for a given relative path.
 *
 * @param string $rel_path
 * @return string - complete absolute path within the plugin
 */
function cvwp_path( $rel_path = '' ){
	$path = path_join( CVWP_PATH, $rel_path );
	return wp_normalize_path( $path );	
}

/**
 * Generates a complete URL to files located inside the plugin folder.
 * 
 * @param string $rel_path - relative path to file
 * @return string - complete URL to file
 */
function cvwp_get_uri( $rel_path = '' ){
	$uri 	= is_ssl() ? str_replace('http://', 'https://', CVWP_URL) : CVWP_URL;	
	$path 	= path_join( $uri, $rel_path );
	return $path;
}

/**
 * Get a plugin options set
 * 
 * @param string $option. Values:
 
 * - plugin_details : get the plugin details set on plugin activation
 * - settings: get the plugin settings set in plugin Settings page
 * - apis: various api keys
 * - updated: update versions
 * 
 * @return array
 */
function cvwp_get_options( $key = false ){
	if( !class_exists( 'CVWP_Plugin_Options' ) ){
		require_once cvwp_path( 'includes/libs/class-cvwp-options.php' );
	}
	
	$o = CVWP_Plugin_Options::init();
	
	if( !$key ){
		return $o->get_option();
	}
	return $o->get_option( $key );
}

/**
 * Updates an option
 * @param string $key - key to update
 * @param mixed $value - value to update with
 */
function cvwp_update_options( $key, $value ){
	if( !class_exists( 'CVWP_Plugin_Options' ) ){
		require_once cvwp_path( 'includes/libs/class-cvwp-options.php' );
	}
		
	$o = CVWP_Plugin_Options::init();
	return $o->update_option( $key, $value );
}

/**********************************
 * Post video options
 **********************************/

/**
 * Get post video options.
 * @param bool/int $post_id - id of post to get options from
 */
function cvwp_get_post_options( $post_id = false ){
	if( !$post_id ){
		global $post;
		if( !$post ){
			return false;
		}
		$post_id = $post->ID;
	}
	
	if( !class_exists( 'CVWP_Post_Options' ) ){
		require_once cvwp_path( 'includes/libs/class-cvwp-options.php' );
	}
	
	$o = CVWP_Post_Options::init();
	$opt = $o->get_option( $post_id );
	
	/**
	 * Filter on post settings retrieval
	 * 
	 * @param int $options - array of post options
	 * @param object $post - post that has the options retrieved for
	 */
	$options = apply_filters( 'cvwp_get_post_options', $opt, $post_id );
	
	return $options;
}

/**
 * Update video options with given values
 * @param int $post_id
 * @param array $value
 */
function cvwp_update_post_options( $post_id, $value ){
	if( !class_exists( 'CVWP_Post_Options' ) ){
		require_once cvwp_path( 'includes/libs/class-cvwp-options.php' );
	}
	
	$o = CVWP_Post_Options::init();
	return $o->update_option( $post_id, $value );
}


/**
 * Returns a formatted time MM:SS from a given number of seconds
 * 
 * @param int $seconds - number of seconds
 * @param string $msg_none - a message to be displayed if seconds is <= 0
 * @return string
 */
function cvwp_video_duration( $seconds, $msg_none = '' ){
	$seconds = absint( $seconds );
	
	if( $seconds <= 0 ){
		return $msg_none;
	}
	
	$h = floor( $seconds / 3600 );
	$m = floor( $seconds % 3600 / 60 );
	$s = floor( $seconds %3600 % 60 );
	
	return ( ($h > 0 ? $h . ":" : "") . ( ($m < 10 ? "0" : "") . $m . ":" ) . ($s < 10 ? "0" : "") . $s);
}

/**
 * Calculate player height from given aspect ratio and width
 * @param string $aspect_ratio
 * @param int $width
 */
function cvwp_player_height( $aspect_ratio, $width ){
	$width = absint($width);
	$height = 0;
	switch( $aspect_ratio ){
		case '4x3':
			$height = floor( ($width * 3) / 4 );
		break;
		case '2.35x1':
			$height = floor( $width / 2.35 );
		break;	
		case '1x1':
			$height = $width;
		break;	
		case '16x9':
		default:	
			$height = floor( ($width * 9) / 16 );
		break;	
	}
	return $height;
}

/**
 * Output the container for the video
 * @param array $video
 */
function cvwp_video_output( $before = '', $after = '', $with_assets = true, $echo = true ){	
	global $post;
	if( !$post ){
		if( current_user_can( 'manage_options' ) ){
			trigger_error( 'Could not embed video because no post was found.', E_USER_ERROR );
		}
		return;
	}
	
	// check if post has video attached to it
	if( !cvwp_has_video() ){
		return;	
	}
	
	// get post options
	$options = cvwp_get_post_options( $post->ID );
	// set video variable
	$video = $options['video'];
	
	// set video size
	/**
	 * Filter video embed width to allow developer to specify his own width 
	 * in case the theme uses some fixed sizes.
	 * 
	 * @var $width - the width set by the user
	 * @var $options - all video options set on post
	 */
	$width = apply_filters( 'cvwp_embed_width' , $video['width'], $options );
	
	$height 	= cvwp_player_height( $video['aspect'] , $width );
	$exclude 	= array();//array('width', 'aspect');
	
	$styles = array(
		'width: 100%',
		'height:' . $height . 'px',
		'max-width:' . $width . 'px'  
	);
	
	// get HTML element data
	$el_data = cvwp_data_attr( $post->ID, $exclude );
		
	/**
	 * Filter that can force lazy load without taking into account 
	 * the user option.
	 * @var bool
	 */
	$lazy_load = apply_filters( 'cvwp_lazy_load' , $options['lazy_load'] );
	
	if( $lazy_load && !is_admin() ){
		$el_data[] = 'data-lazy_load="1"';
		// image URL
		$attachment_id = get_post_thumbnail_id();
		$img = wp_get_attachment_image_src( $attachment_id, 'full' );
		if( $img ){
			$styles[] = 'background-image:url( ' . $img[0] . ' )';
		}		
	}
	
	// start creating the output
	$output = $before . '<div class="cvwp-video-player" style="' . implode('; ', $styles) . '" ' . implode(' ', $el_data) . '>';
	if( $lazy_load && !is_admin() ){
		$output.= '<a href="#" class="cvwp-load-video" title="' . esc_attr( $post->post_title ) . '"></a>';
	}	
	$output.= '</div>' . $after;
	if( $echo ){
		echo $output;
	}
	
	
	
	if( $with_assets ){
		wp_enqueue_style(
			'cvwp-video-player',
			cvwp_get_uri( 'assets/front-end/css/video-player.css' )
		);
		
		wp_enqueue_script(
			'cvwp-video-player',
			cvwp_get_uri( 'assets/front-end/js/video-player2' . __cvwp_js_file_suffix() . '.js' ),
			array( 'jquery' )
		);
		
		if( !is_admin() ){
			wp_localize_script(
				'cvwp-video-player',
				'cvwp_video_options',
				array(
					'embed' => '.cvwp-video-player'
				)
			);
		}
				
		/**
		 * Video player script action. Allows third party plugins to load
		 * other assets.
		 */
		do_action( 'cvwp_embed_video_script_enqueue' );		
	}
	
	return $output;	
}

/**
 * Determines th esuffix for JavaScript files: .dev for developer files and .min for minified files
 * @return string
 */
function __cvwp_js_file_suffix(){
	return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.dev' : '.min';
}

/**
 * Based on a given post ID, it will retrieve the post video options and 
 * create and array of data-... attributes to be used on HTML elements
 * to pass data to JavaScript.
 *
 * @param int $post_id
 * @param array $exclude
 * @return array
 */
function cvwp_data_attr( $post_id = false, $exclude = array() ){
	if( !$post_id ){
		global $post;
		if( !$post ){
			return array();
		}
		$post_id = $post->ID;
	}
	
	$options = cvwp_get_post_options( $post_id );
	if( !isset( $options['video'] ) ){
		return array();
	}
	
	$video = $options['video'];	
	if( !is_array( $exclude ) ){
		$exclude = array();
	}
	
	$el_data = array();
	foreach( $video as $k => $v ){
		if( in_array( $k, $exclude ) ){
			continue;
		}
		// booleans get converted to 0 or 1
		if( is_bool( $v ) ){
			$v = (int) $v;
		}
		
		$el_data[] = 'data-' . $k . '="' . $v . '"';
	}
	
	$el_data[] 	= 'data-ssl="' . (int) is_ssl() . '"';
	$height 	= cvwp_player_height( $video['aspect'] , $video['width'] );
	$el_data[] 	= 'data-height="' . $height . '"';
	
	return $el_data;
}

/**
 * Outputs HTML commented messages. Useful for debugging but not limited
 * to it.
 *
 * @param string $message
 * @param bool $echo
 * @return string
 */
function cvwp_plugin_message( $message, $echo = false ){
	/**
	 * Filter that can be used to prevent all plugin messages from being displayed into page as HTML comments.
	 * @param boolean
	 */
	$allow = apply_filters( 'cvwp_allow_plugin_messages', true );

	if( !$allow ){
		return null;
	}

	$message = sprintf( '<!-- VideographyWP Plugin Message: %s -->', $message );
	if( $echo ){
		echo $message;
	}
	return $message;
}

/**
 * Returns all registered video sources
 *
 * @return array
 */
function cvwp_get_video_sources(){
	if( !class_exists('CVWP_Video_Query') ){
		require_once cvwp_path( 'includes/libs/class-cvwp-media-query.php' );
	}
	$video_query 	= new CVWP_Video_Query( false );
	$sources 		= $video_query->get_video_sources();
	return $sources;
}

/**
 * Get details about a given video source
 *
 * @uses cvwp_get_video_sources()
 *
 * @param string $source
 * @return array
 */
function cvwp_get_video_source( $source ){
	$sources = cvwp_get_video_sources();
	if( is_array( $sources ) && array_key_exists( $source , $sources ) ){
		return $sources[ $source ];
	}
	return false;
}

/**
 * Checks if theme is compatible with the plugin
 *
 * @uses class CVWP_Compatibility  
 * @return boolean false/array
 */
function cvwp_theme_is_compatible(){
	// get template details
	$theme = wp_get_theme();
	if( is_object( $theme ) ){
		// check if it's child theme
		if( is_object( $theme->parent() ) ){
			// set theme to parent
			$theme = $theme->parent();
		}
	}else{
		return false;
	}
	
	$theme_name = strtolower( $theme->Name );
	$themes = cvwp_get_compatible_themes();
	
	// check if theme is supported
	if( is_array( $themes ) && array_key_exists( $theme_name, $themes ) ){
		return true;
	}	
	return false;
}

/**
 * Returns whether theme embeds option is on
 * 
 * @uses cvwp_get_options()
 * @return boolean
 */
function cvwp_theme_embed_allowed(){
	return false;
}

/**
 * Function that implements a filter that will globally disallow the plugin
 * from doing any embedding.
 *
 * @return boolean - embedding disallowed(true) or allowed(false)
 */
function __cvwp_disallow_plugin_embeds(){	
	return (bool) apply_filters( 'cvwp_disallow_plugin_embeds' , false );
}

/**
 * Generate video ID based on post ID.
 * Will display any video for a registered video source
 * @param bool $post_id
 *
 * @return array|bool|string
 */
function cvwp_get_video_url( $post_id = false ){
	if( !$post_id ){
		global $post;
		if( !$post ){
			return false;
		}
		$post_id = $post->ID;
	}

	$options = cvwp_get_post_options( $post_id );
	if( !isset( $options['video'] ) ){
		return false;
	}

	$video = $options['video'];
	$sources = cvwp_get_video_sources();
	if( !array_key_exists( $video['source'], $sources ) ){
		return false;
	}

	return sprintf( $sources[ $video['source'] ]['url'], $video['video_id'] );
}