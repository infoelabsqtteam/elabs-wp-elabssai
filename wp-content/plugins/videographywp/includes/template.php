<?php
// No direct access
if( !defined( 'ABSPATH' ) ){
	die();
}

/**
 * Checks if the current post has a video attached to it.
 *
 * @return boolean - true if video is found, false if not found
 */
function cvwp_has_video( $post = false ){
	if( !$post ){
		$post_id = get_the_ID();
	}else{
		if( is_object( $post ) ){
			$post_id = $post->ID;
		}else{
			$post_id = absint( $post );
		}
	}	
	
	if( !$post_id ){
		return false;
	}
	
	$options = cvwp_get_post_options( $post_id );
	if( !empty( $options['video']['video_id'] ) && !empty( $options['video']['source'] ) ){
		return true;
	}
	return false;
}

/**
 * Checks if plugin options allow automatic embedding
 *
 * @return boolean
 */
function cvwp_is_embed_allowed(){
	
	// check if embedding by plugin isn't prevented globally by filter
	$disallowed = __cvwp_disallow_plugin_embeds();
	if( $disallowed ){
		return false;
	}
	
	$settings = cvwp_get_options( 'settings' );
	// embedding not allowed by user settings, stop here
	if( !$settings['plugin_embedding'] ){
		return false;
	}
	
	// from here on, only non-admin area pages get checked
	if( is_admin() ){
		return true;
	}
	
	// check if single post embedding is enabled
	if( $settings['single_post_embedding'] && !is_singular() ){
		return false;
	}
	
	// check if embedding dissalowed by post options
	$options = cvwp_get_post_options();
	if( $options ){
		if( 'no_embed' == $options['embed_position'] ){
			return false;
		}
	}
	
	return true;
}

/**
 * Embeds the current post video
 * 
 * @uses get_the_id()
 * @uses cvwp_has_video()
 * @uses cvwp_get_post_options()
 * @uses cvwp_video_output()
 * 
 * @param string $before - HTML before the video output
 * @param string $after - HTML after the video output
 * @return bool
 */
function cvwp_embed_video( $before = '', $after = '' ){
	$post_id = get_the_id();
	if( !$post_id || !cvwp_has_video() ){
		return false;
	}
	
	$options = cvwp_get_post_options( $post_id );
	cvwp_video_output( $before, $after, true, true );
	return true;
}

/**
 * Generate a formatted ( HH:MM:SS ) video duration for current video post
 * in the loop.
 * 
 * @uses cvwp_get_video_duration()
 * @uses cvwp_video_duration()
 * 
 * @param string $before - string before the output
 * @param string $after - string after the output
 * @param boolean $echo - echo output (true)
 * @return string/false
 */
function cvwp_the_video_duration( $before = '', $after = '', $echo = true ){
	$duration = cvwp_get_the_video_duration();	
	if( !$duration ){
		return false;
	}
	$formatted = cvwp_video_duration( $duration, false );		
	$output = $before . $formatted  .$after;
	if( $echo ){
		echo $output;
	}
	return $output;
}

/**
 * Returns the current post video duration
 * 
 * @uses cvwp_has_video()
 * @uses cvwp_get_post_options()
 * @uses absint()
 * 
 * @return int - seconds
 */
function cvwp_get_the_video_duration(){
	if( !cvwp_has_video() ){
		return;
	}
	
	$options = cvwp_get_post_options();
	return absint( $options['video']['duration'] );
}

/**
 * Outputs the current post video source name
 *
 * @param string $before - string before output
 * @param string $after - string after output
 * @param bool $echo - echo output (true)
 * @return string
 */
function cvwp_the_video_source( $before = '', $after = '', $echo = true ){
	$source = cvwp_get_the_video_source();
	if( !$source ){
		return;
	}
	
	$source = cvwp_get_video_source( $source );
	if( !$source ){
		return;
	}
	
	$source_name = $source['details']['name'];
	$output = $before . $source_name . $after;
	
	if( $echo ){
		echo $output;
	}	
	return $output;
}

/**
 * Returns the video source ( default values: youtube, vine, vimeo, dailymotion )
 *
 * @return string
 */
function cvwp_get_the_video_source(){
	if( !cvwp_has_video() ){
		return;
	}
	
	$options = cvwp_get_post_options();
	return $options['video']['source'];
}

/**
 * Outputs or returns the current post video URL
 *
 * @param string $return_empty - string to return in case URL isn't found
 * @param boolean $echo - echo the output
 * @return boolean/string
 */
function cvwp_the_video_url( $return_empty = '', $echo = true ){
	$url = cvwp_get_the_video_url();
	if( !$url ){
		$url = $return_empty;
	}
	
	if( $echo ){
		echo $url;
	}
	return $url;
}

/**
 * Returns a video URL. If no post ID is specified, the plugin
 * will use the current post ID from WP loop
 * 
 * @uses cvwp_has_video()
 * @uses get_the_ID()
 * @uses cvwp_get_post_options()
 * @uses cvwp_get_video_source()
 * 
 * @param int $post_id
 * @return boolean/string - video URL if success, false if something isn't found
 */
function cvwp_get_the_video_url( $post_id = false ){
	// no video attach, bail out
	if( !cvwp_has_video( $post_id ) ){
		return false;
	}
	// get post ID if none is set
	if( !$post_id ){
		$post_id = get_the_ID();
	}
	// get post options
	$options = cvwp_get_post_options( $post_id );
	$source = cvwp_get_video_source( $options['video']['source'] );
	// source not found, bail out
	if( !$source ){
		return false;
	}
	
	$url = sprintf( $source['url'], $options['video']['video_id'] );
	return $url;
}
