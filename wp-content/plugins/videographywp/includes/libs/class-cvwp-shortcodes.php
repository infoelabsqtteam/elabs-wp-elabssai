<?php
// No direct include
if( !defined('ABSPATH') ){
	die();
}

/**
 * Shortcodes class. Implements all plugin shortcodes
 *
 * @since 1.0
 * @package VideographyWP plugin
 */
class CVWP_Shortcodes{
	
	/**
	 * @var instance
	 **/
	private static $instance = null;
	
	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new CVWP_Shortcodes();
		}
		return self::$instance;
	}
	
	/**
	 * Constructor, implements the shortcodes
	 */
	public function register_shortcodes(){
		$shortcodes = $this->shortcodes();
		foreach( $shortcodes as $tag => $data ){
			add_shortcode( $tag , $data['callback'] );
		}
	}
	
	/**
	 * Contains all shortcodes implementations
	 *
	 * @param strong $shortcode - return a single shortcode by key
	 * @return array
	 */
	private function shortcodes( $shortcode = false ){
		$shortcodes = array();	
		
		// remove this and replace with your own shortcodes
		$shortcodes['cvwp_video_position'] = array(
			'callback' => array( self::$instance, 'cb_video_position' ),
			'atts' => array(
			)
		);
		
		$shortcodes['cvwp_video_button'] = array(
			'callback' => array( self::$instance, 'cb_video_button' ),
			'atts' => array(
				'text' 	=> __('Play video', 'videographywp'),
				'title' => __('Play video', 'videographywp'),
				'class' 	=> '',
				'template' 	=> 'btn btn-lg btn-default'
			)
		);
		
		if( $shortcode ){
			if( array_key_exists( $shortcode , $shortcodes ) ){
				return $shortcodes[ $shortcode ];
			}else{
				return false;
			}
		}		
		return $shortcodes;
	}
	
	/**
	 * Video position by shortcode
	 *
	 * @param array $atts
	 * @return string;
	 */
	public function cb_video_position( $atts = array() ){
		// get shortcode details
		$data = $this->shortcodes( 'cvwp_video_position' );
		// merge user attributes with defaults
		$atts = shortcode_atts( 
			$data['atts'], 
			$atts 
		);
		// check current post
		global $post;
		if( !$post ){
			return;
		}	

		// check if plugin embedding is allowed
		if( !cvwp_is_embed_allowed() ){
			if( __cvwp_disallow_plugin_embeds() ){
				$message = cvwp_plugin_message( 'Automatic video embedding prevented by plugin filter set in theme or other plugin.' );
			}else{
				$message = cvwp_plugin_message( 'Automatic video embedding prevented by plugin options.' );
			}
			return $message;
		}
		
		// get options
		$options = cvwp_get_post_options( $post->ID );
		// check if video is set to be displayed with shortcode
		if( !cvwp_has_video() || !isset( $options['embed_position'] ) || 'shortcode' != $options['embed_position'] ){
			return;
		}

		/**
		 * Filter to allow videos to be embedded by the plugin.
		 * @var boolean
		 */
		$allow = apply_filters( 'cvwp_allow_video_embed' , true, $post, $options['embed_position'] );
		
		if( !$allow ){
			return cvwp_plugin_message( 'Video embed dissalowed by filter.' );
		}
		
		$output = cvwp_video_output( '', '', true, false );
		
		// return output
		return $output;
	}
	
	/**
	 * Video button
	 *
	 * @param array $atts
	 * @return string HTML
	 */
	public function cb_video_button( $atts = array() ){
		// get shortcode details
		$data = $this->shortcodes( 'cvwp_video_button' );
		// merge user attributes with defaults
		$atts = shortcode_atts( 
			$data['atts'], 
			$atts 
		);
		// check current post
		global $post;
		if( !$post ){
			return;
		}	

		// check if plugin embedding is allowed
		if( !cvwp_is_embed_allowed() ){
			if( __cvwp_disallow_plugin_embeds() ){
				$message = cvwp_plugin_message( 'Automatic video embedding prevented by plugin filter set in theme or other plugin.' );
			}else{
				$message = cvwp_plugin_message( 'Automatic video embedding prevented by plugin options.' );
			}
			return $message;
		}
		
		// get options
		$options = cvwp_get_post_options( $post->ID );
		// check if video is set to be displayed with shortcode
		if( !isset( $options['embed_position'] ) || 'button' != $options['embed_position'] ){
			return;
		}
		
		/**
		 * Filter to allow videos to be embedded by the plugin.
		 * @var boolean
		 */
		$allow = apply_filters( 'cvwp_allow_video_button' , true, $post, $options['embed_position'] );
		
		if( !$allow ){
			return cvwp_plugin_message( 'Video button dissalowed by filter.' );
		}
		
		$el_data = cvwp_data_attr( $post->ID, array( 'duration' ) );	
		$class = array( 'cvwp-video-button' );
		if( !empty( $atts['class'] ) ){
			$class[] = esc_attr( $atts['class'] );
		}
		if( !empty( $atts['template'] ) ){
			$class[] = esc_attr( $atts['template'] );
		}
				
		$trigger = sprintf( 
			'<a href="%s" title="%s" class="cvwp-video-button %s" %s>%s</a>', 
			'#', 
			esc_attr( $atts['title'] ), 
			implode( ' ', $class ),
			implode( ' ', $el_data ),			
			esc_attr( $atts['text'] ) 
		);
		
		// enqueue modal script and styles
		wp_enqueue_script(
			'cvwp-jquery-modal',
			cvwp_get_uri( 'assets/libs/jquery-modal/jquery.modal.min.js' ),
			array( 'jquery' ),
			'0.5.5'
		);
		
		wp_enqueue_script(
			'cvwp-video-player',
			cvwp_get_uri( 'assets/front-end/js/video-player2' . __cvwp_js_file_suffix() . '.js' ),
			array( 'jquery' )
		);
		
		wp_enqueue_style(
			'cvwp-jquery-modal',
			cvwp_get_uri( 'assets/libs/jquery-modal/jquery.modal.css' ),
			null,
			'0.5.5'
		);
		
		wp_enqueue_style(
			'cvwp-video-player',
			cvwp_get_uri( 'assets/front-end/css/video-player.css' )
		);
		
		return $trigger;
	}
	
	/**
	 * Returns all registered shortcodes
	 * @return array
	 */
	static function get_shortcodes(){
		return self::init()->shortcodes();
	}
}
CVWP_Shortcodes::init()->register_shortcodes();