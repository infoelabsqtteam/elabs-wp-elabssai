<?php
// No direct access
if( !defined( 'ABSPATH' ) ){
	die();
}

/**
 * General options management class
 */
abstract class CVWP_Options{
	/**
	 * Store option name
	 * @var string
	 */
	private $option_name;
	/**
	 * Store option default values
	 * @var mixed
	 */
	private $option_default;
	/**
	 * Type of option to retrieve. Possible values: post_meta or wp_option
	 * @var string
	 */
	private $option_type;
	/**
	 * The post ID if retrieveing post meta
	 * @var int
	 */
	private $post_id;
	/**
	 * Stores the retrieved option
	 * @var mixed
	 */
	private $option = NULL;
	
	/**
	 * Class constructor. Takes an array of arguments:
	 * 
	 * - option_name: name of the option to be retrieved
	 * - option_default: default option value (optional)
	 * - option_type: the type of option to retrieve (can be post_meta for posts or wp_option for general options)
	 * - post_id: in case post meta is retrieved, it will need the post ID to retrieve meta from
	 * 
	 * @param unknown_type $args
	 */
	protected function __construct( $args = array() ){
		// defaults
		$defaults = array(
			'option_name' 		=> false,
			'option_default' 	=> false,
			'option_type' 		=> false, // possible values: post_meta or wp_option
			'post_id'			=> false, // optional for post meta
		);
		
		if( !$args ){
			trigger_error( 'No class arguments specified.', E_USER_NOTICE );
			return;
		}
		
		// mix the two, arguments with defaults
		$args = wp_parse_args( $args, $defaults );
		// check option name
		if( !$args['option_name'] ){
			trigger_error( 'No option name specified.', E_USER_NOTICE);
			return;
		}
		// check option type
		if( !$args['option_type'] ){
			trigger_error( 'No option type specified', E_USER_NOTICE);
			return;
		}
				
		$this->option_name 		= $args['option_name'];
		$this->option_default 	= $args['option_default'];
		$this->option_type 		= $args['option_type'];
		$this->post_id			= absint( $args['post_id'] );
	}
	
	/**
	 * Get option
	 */
	public function get_the_option(){
		if( !is_null( $this->option ) ){
			return $this->option;
		}		
		$option = NULL;		
		switch( $this->option_type ){
			// get WP option
			case 'wp_option':
				$option = get_option( $this->option_name, $this->option_default );
			break;
			// get post option
			case 'post_meta':
				$option = get_post_meta( $this->post_id, $this->option_name, true );	
			break;
			// only 2 types allowed, return error for anything else
			default:
				trigger_error( 'No option type specified', E_USER_NOTICE);
			break;	
		}
		
		if( !is_null($option) && is_array( $this->option_default ) ){
			if( !is_array( $option ) ){
				return $this->option_default;
			}
			$option = wp_parse_args( (array)$option, $this->option_default );
		}
		$this->option = $option;	
		return $option;		
	}
	
	/**
	 * Updates the option with a given value
	 * @param mixed $value
	 */
	public function update_the_option( $value ){
		if( !$value ){
			trigger_error( 'No value passed to be saved.', E_USER_WARNING);
			return;
		}
		switch( $this->option_type ){
			// get WP option
			case 'wp_option':
				$result = update_option( $this->option_name, $value );
			break;
			// get post option
			case 'post_meta':
				//$result = update_post_meta( $this->post_id, $this->option_name, $value );		
				$result = update_metadata('post', $this->post_id, $this->option_name, $value );			
			break;
			// only 2 types allowed, return error for anything else
			default:
				trigger_error( 'No option type specified', E_USER_NOTICE);
			break;	
		}

		if( isset( $result ) && $result ){
			$this->reset_option();
			return $result;
		}
		return false;
	}
	
	/**
	 * On option update, it resets $this->option with the new values
	 */
	private function reset_option(){
		$this->option = null;
		$this->get_the_option();
	}
	
	/**
	 * Setter - sets a new post ID
	 * @param int $post_id
	 */
	public function set_post_id( $post_id ){
		if( is_object( $post_id ) ){
			$post_id = $post_id->ID;
		}
		
		if( $this->post_id != $post_id ){
			$this->post_id = absint( $post_id );
			$this->reset_option();
		}
	}
	
	/**
	 * Getter - returns the current post id
	 */
	public function get_post_id(){
		return $this->post_id;
	}
	
	/**
	 * Returns default values of option
	 * @return array
	 */
	public function get_defaults(){
		return $this->option_default;
	}
}


/**
 * Manages options setting/getting for posts 
 */
class CVWP_Post_Options extends CVWP_Options{
	// store default values
	private $defaults;
	
	/**
	 * @var instance
	 **/
	private static $instance = null;
	
	static function init( $post_id = false ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new CVWP_Post_Options( $post_id );
		}
		return self::$instance;
	}
	/**
	 * Constructor
	 * @param int $post_id
	 */
	protected function __construct( $post_id = false ){
		
		$this->defaults = array(
			/**
			 * Position where to embed the video. Options:
			 * above_content	- video will be embedded above the post content
			 * below_content	- video will be embedded below the post content
			 * shortcode		- video will be embedded at the position of the video shortcode into the post content
			 * no_embed			- prevent video embedding
			 * featured_image	- embed in place of the featured image
			 */
			'embed_position' => 'above_content',
			// when enabled, will load post image first instead of embedding video directly
			'lazy_load'	=> true,
			
			/**
			 * Video specific options
			 */
			'video' => array(
				'source' 	 => '', // video source
				'video_id' 	 => '', // video ID
				'duration'	 => 0,
				'width'		 => '900', // video width
				'aspect'	 => '16x9', // video aspect ration ( 4x3, 16x9, 23.5x1 )
				'volume'	 => 30, // default playback volume
				'fullscreen' => true, // allow fullscreen
				'loop'      => false, // video loop; available only for YouTube and Vimeo
				'image'		 => '',
				// Vimeo
				'title'		=> false, // show video title in player
				'byline'	=> false, // show author line in player
				'portrait'	=> false, // show author portrait in player
				'color'		=> '', // controls color
				// YouTube
				'controls' 		 => true, // show controls
				'autohide' 		 => true, // autohide controls when player starts playing
				'iv_load_policy' => true,  // hide annotations (3) or show them (1)
				'modestbranding' => true, // show YouTube logo on controls bar(0) or on video (1)
				'nocookie' => false, // cookieless embed; when true, it will try to embed from coockieless domain if platform allows it
				// Dailymotion
				'dm_info' 		=> true, // Shows videos information (title/author) on the start screen (defaults to 1)
				'dm_logo' 		=> true, // Allows to hide or show the Dailymotion logo (defaults to 1)
				'dm_related' 	=> true // Shows related videos at the end of the video. Default value is 1
			)
		);
		
		/**
		 * Filter that allows extending of default post options
		 * @var array
		 */
		$extend_defaults = apply_filters( 'cvwp_post_options_defaults' , array() );
		if( $extend_defaults && is_array( $extend_defaults ) ){
			$this->defaults = array_merge( $extend_defaults, $this->defaults );
		}
		
		// parent class arguments
		$args = array(
			'post_id' 			=> $post_id,
			'option_name' 		=> '_cvwp_video_settings',
			'option_type'		=> 'post_meta',
			'option_default'	=> 	$this->defaults
		);		
		parent::__construct( $args );
		
	}
	
	/**
	 * Get the post options. If passing a post ID, the parent class will be refreshed and 
	 * the option will be retrieved for the given ID.
	 * 
	 * @param bool/int $post_id
	 */
	public function get_option( $post_id = false ){
		if( $post_id ){
			parent::set_post_id( $post_id );
		}
		
		$post_id = parent::get_post_id();
		$post = get_post( $post_id, ARRAY_A );
		
		$options = parent::get_the_option();
		
		foreach( $options as $k => $v ){
			if( is_array( $v ) ){
				$options[ $k ] = wp_parse_args( $v, $this->defaults[ $k ] );
			}
		}
		
		return $options;
	}
	
	/**
	 * Updates post options for given post id
	 * @param int $post_id
	 * @param array $value
	 */
	public function update_option( $post_id, $value ){
		if( !current_user_can( 'edit_posts', $post_id ) ){
			wp_die( __('You are not allowed to do this.', 'videographywp'), __('Not allowed', 'videographywp') );
		}
		
		$defaults = $this->get_option( $post_id );
		
		foreach( $this->defaults as $k => $v ){
			
			if( is_array( $v ) ){
				foreach( $v as $kk => $vv ){
					if( is_numeric( $vv ) ){
						if( isset( $value[ $k ][ $kk ] ) ){
							$defaults[ $k ][ $kk ] = (int)$value[ $k ][ $kk ];
						}
					}
					if( is_bool( $vv ) ){
						if( 'iv_load_policy' == $kk ){
							$defaults[ $k ][ $kk ] = isset( $value[ $k ][ $kk ] ) ? 3 : 1;
							continue;
						}						
						$defaults[ $k ][ $kk ] = isset( $value[ $k ][ $kk ] );
					}
					if( isset( $value[ $k ][ $kk ] ) ){
						$defaults[ $k ][ $kk ] = $value[ $k ][ $kk ];
					}
				}				
				continue;
			}
						
			if( is_numeric( $v ) ){
				if( isset( $value[ $k ] ) ){
					$defaults[ $k ] = (int)$value[ $k ];
				}
			}
			if( is_bool( $v ) ){
				$defaults[ $k ] = isset( $value[ $k ] ) && $value[ $k ];
			}
			if( isset( $value[ $k ] ) ){
				$defaults[ $k ] = $value[ $k ];
			}			
		}
		
		/**
		 * Action on post settings save
		 * 
		 * @param int $post_id - id of post being saved
		 * @param array $defaults - the new options
		 * @param arrat $value - the values passed to be saved
		 */
		do_action('cvwp_save_post_options', $post_id, $defaults, $value);
		
		return parent::update_the_option( $defaults );
	}
}

/**
 * Class to manage plugin options
 *
 */
class CVWP_Plugin_Options extends CVWP_Options {
	/**
	 * Stores the default values
	 * @var array
	 */
	private $defaults;
	
	/**
	 * @var instance
	 **/
	private static $instance = null;
	
	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new CVWP_Plugin_Options;
		}
		return self::$instance;
	}
	
	/**
	 * Constructor, instantiates the parent class
	 */
	protected function __construct(){
		
		$this->defaults = array(
			/**
			 * Stores plugin details on activation. Helpful on plugin updates to allow the plugin
			 * to update settings depending on version (if needed)
			 */
			'plugin_details' => array(
				'version' 			=> '', // plugin version installed
				'previous_version'	=> '', // stores previous plugin version if any
				'wp_version' 		=> '', // wp version on plugin activation
				'activated_on'		=> '', // date of activation				
			),
			/**
			 * Plugin general settings
			 */
			'settings' => array(
				'complete_uninstall' 	=> false, // perform a complete unistall
				'show_warnings'			=> true, // when true will show some warnings in WP admin in certain cases
				'post_types'		 	=> array( 'post', 'page' ), // allow on custom post types

				// embedding
				'plugin_embedding' 		=> true, // when true, the plugin will handle all embedding automatically
				'single_post_embedding' => true, // when true, the plugin will automatically embed videos only on single post
				'embed_for_theme' 		=> false, // when true, all already imported videos will be embedded using the 
			),
			/**
			 * Stores different api keys
			 */
			'apis' => array(
				'youtube_key' => ''
			)
		);
		
		$args = array(
			'option_name' 		=> 'cvwp_plugin_options',
			'option_default' 	=> $this->defaults,
			'option_type'		=> 'wp_option'
		);		
		parent::__construct( $args );
	}
	
	/**
	 * Get a key from plugin options. Possible values:
	 * 
	 * - plugin_details : get the plugin details set on plugin activation
	 * - settings: get the plugin settings set in plugin Settings page
	 * - apis: various api keys
	 * - updated: update versions
	 */
	public function get_option( $key = false ){		
		$option = parent::get_the_option();
		
		if( !$key ){
			return $option;
		}
		
		if( array_key_exists($key, $option) ){
			return wp_parse_args( $option[ $key ], $this->defaults[ $key ]);
		}else{
			trigger_error( sprintf( 'Key %s not found in plugin options.', $key), E_USER_NOTICE);
		}		
	}
	
	/**
	 * Updates a given key in plugin options
	 * @param string $key
	 * @param mixed $value
	 */
	public function update_option( $key, $value ){
		
		if( !current_user_can( 'manage_options' ) ){
			wp_die( __('You are not allowed to do this.', 'videographywp'), __('Not allowed', 'videographywp') );
		}
		
		if( !$key ){
			trigger_error( 'No option key specified.', E_USER_WARNING );
			return false;
		}
		if( !$value ){
			trigger_error( sprintf( 'No value specified for option key %s', $key), E_USER_WARNING );
			return false;
		}
		if( !array_key_exists($key, $this->defaults) ){
			trigger_error( sprintf( 'Key %s not found in options', $key), E_USER_WARNING );
			return false;
		}
		// get the defaults
		$defaults = $this->defaults[ $key ];
		// get all stored options	
		$option = $this->get_option();
		
		// processing the entered data
		foreach ( $defaults as $k => $v ){
			if( is_numeric( $v ) ){
				if( isset( $value[ $k ] ) ){
					$defaults[ $k ] = (int)$value[ $k ];
				}
			}
			if( is_bool( $v ) ){
				$defaults[ $k ] = isset( $value[ $k ] ) && $value[ $k ];
			}
			if( isset( $value[ $k ] ) ){
				$defaults[ $k ] = $value[ $k ];
			}			
		}
		
		/**
		 * Filter the values to be saved
		 * 
		 * @param $defaults: values processed by default
		 * @param $value : raw value
		 * @param $key : options key in plugin options to be updated
		 * @param $defaults: the processed values
		 * 
		 * @var array
		 */
		$defaults = apply_filters('cvwp-set-options_' . $key, $defaults, $value, $key, $option[ $key ]);
		
		// in case of errors, applied filters should return a WP error
		if( is_wp_error( $defaults ) ){
			return $defaults;
		}
		
		$option[ $key ] = $defaults;
		$updated = parent::update_the_option( $option );
		
		/**
		 * Action after saving options
		 * 
		 * @param $defaults: values processed by default
		 * @param $value : raw value
		 * @param $key : options key in plugin options to be updated
		 * @param $defaults: the processed values
		 * 
		 * @var array
		 */
		do_action( 'cvwp-updated-options_' . $key, $defaults, $value, $key, $option[ $key ] );
		
		return $updated;
	}
}