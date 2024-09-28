<?php
// No direct include
if( !defined('ABSPATH') ){
	die();
}

// add Ajax Actions class
if( !class_exists( 'CVWP_Ajax_Actions' ) ){
	require_once cvwp_path( 'includes/admin/libs/cvwp-ajax-actions.php' );
}

if( !class_exists( 'CVWP_Review_Callout' ) ){
	require_once cvwp_path( 'includes/admin/libs/review-callout.class.php' );
}

/**
 * Admin class. Implements all plugin administration
 *
 * @since 1.0
 * @package VideographyWP plugin
 */
class CVWP_Admin extends CVWP_Ajax_Actions{

	/**
	 * @var instance
	 **/
	private static $instance = null;
	
	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new CVWP_Admin;
		}
		return self::$instance;
	}
	
	/**
	 * Constructor, instantiates hooks and filters
	 * 
	 * @uses parent::construct()
	 * @uses add_action()
	 */
	protected function __construct(){
		// register parent AJAX actions
		parent::__construct();
		
		// admin menu
		add_action( 'admin_menu' , array( $this, 'admin_menu' )  );
		
		// add the video query meta box
		add_action( 'add_meta_boxes' , array( $this, 'register_meta_boxes' ), 10, 2  );
		
		// save user video options
		add_action( 'save_post', array( $this, 'save_video_options' ), 10, 3 );
		
		// add Settings link to plugin actions
		add_filter('plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2);
		
		// admin notice
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		
		// admin init
		add_action( 'admin_init', array( $this, 'redirect_about_page' ) );
		
		// add a filter to detect if Lite is installed and remove activation link and add a message
		add_filter('plugin_row_meta', array( $this, 'plugin_meta' ), 10, 2);
		
		add_action( 'admin_init', array( $this, 'review_notice' ) );
	}
	
	/**
	 * Add plugin admin menu
	 * 
	 * @uses add_options_page()
	 * 
	 * @return void
	 */
	public function admin_menu() {
		
		// add page to Settings menu
		$settings_page = add_options_page(
			__( 'VideographyWP Lite', 'videographywp' ), 
			__( 'VideographyWP Lite', 'videographywp' ), 
			'manage_options', 
			'cvwp-settings',
			array( $this, 'settings_page' )
		);		
		add_action( 'load-' . $settings_page, array( $this, 'on_load_settings_page' ) );
		
		// about plugin page
		$about_page = add_submenu_page(
			'',
			__( 'About VideographyWP', 'videographywp' ), 
			__( 'About VideographyWP' ), 
			'manage_options', 
			'cvwp-about',
			array( $this, 'about_page' )
		);
		add_action( 'load-' . $about_page, array( $this, 'on_load_about_page' ) );
	}	
	
	/**
	 * Admin menu page load callback
	 * 
	 * @uses check_admin_referer()
	 * @uses cvwp_update_options()
	 * @uses wp_redirect()
	 * @uses $this->enqueue_script()
	 * @uses $this->enqueue_style()
	 * 
	 * Plugin settings page on load callback
	 * @return void
	 */
	public function on_load_settings_page(){

		if( isset( $_POST['cvwp-nonce'] ) ){
			check_admin_referer( 'cvwp-save-plugin-options', 'cvwp-nonce' );
			
			if( !isset( $_POST['post_types'] ) ){
				$_POST['post_types'] = array();
			}

			cvwp_update_options( 'settings' , $_POST );
			cvwp_update_options( 'apis' , array( 'youtube_key' => trim( $_POST['apis_youtube_key'] ) ) );
			
			wp_redirect( menu_page_url( 'cvwp-settings', false ) );
			die();
		}

		// enqueue modal script and styles
		wp_register_script(
			'cvwp-jquery-modal',
			cvwp_get_uri( 'assets/libs/jquery-modal/jquery.modal.min.js' ),
			array( 'jquery' ),
			'0.5.5'
		);

		wp_enqueue_style(
			'cvwp-jquery-modal',
			cvwp_get_uri( 'assets/libs/jquery-modal/jquery.modal.css' ),
			null,
			'0.5.5'
		);

		wp_enqueue_script(
			'cvwp-video-player',
			cvwp_get_uri( 'assets/front-end/js/video-player2' . __cvwp_js_file_suffix() . '.js' ),
			array( 'jquery', 'cvwp-jquery-modal' )
		);
		
		$handle = $this->enqueue_script( 'settings', array( 'jquery', 'jquery-ui-tabs' ) );
		$data = array(
		    'ajax' => parent::get_ajax_action( 'test_yt_key' ),
            'messages' => array(
                'loading' => __( '... processing request, please wait', 'videographywp' ),
                'still_loading' => __( '... still processing, please be patient', 'videographywp' ),
                'error' => __( 'There was a processing error, please try again.', 'videographywp' ),
                'empty' => __( 'Please provide an API key.', 'videographywp' ),
            )
        );

		wp_localize_script( $handle, 'CVWP_SETTINGS_DATA', $data );

		$this->enqueue_style( 'page-settings' );
		
	}
	
	/**
	 * Admin menu page callback
	 * 
	 * Settings page output
	 * 
	 * @uses cvwp_get_options()
	 * 
	 * @return void
	 */
	public function settings_page(){
		// plugin options
		$options 	= cvwp_get_options( 'settings' );		
		// api keys
		$apis 		= cvwp_get_options( 'apis' );

		$extra_tabs = apply_filters( 'cvwp_register_plugin_settings_tab', array() );

		// include template
		$path = $this->template_path( 'settings' );
		include_once $path;
	}

	/**
	 * Checks if Elementor PRO is installed
	 * @return bool
	 */
	private function is_elementor_pro(){
		return defined('ELEMENTOR_PRO_VERSION') && class_exists('\ElementorPro\Plugin');
	}

	/**
	 * Admin menu page load callback
	 * 
	 * 
	 * Plugin about page on load callback
	 * @return void
	 */
	public function on_load_about_page(){
		$this->enqueue_style( 'page-about' );		
	}
	
	/**
	 * Admin menu page callback
	 * 
	 * Outputs about page on plugin activation
	 *
	 * @return void
	 */
	public function about_page(){
		
		$path = $this->template_path( 'about' );
		include_once $path;
	}
	
	/**
	 * Redirects to about page on admin_init when plugin has just been activated
	 *
	 * @return void
	 */
	public function redirect_about_page(){
		if( !current_user_can( 'manage_options' ) ){
			return;
		}
		
		if( !get_transient( 'cvwp_about_page_activated' ) ){
			return;			
		}
		
		delete_transient( 'cvwp_about_page_activated' );
		wp_redirect( menu_page_url( 'cvwp-about', false ) );
		die();
	}
	
	/**
	 * Action callback
	 * 
	 * Saves video options onpost save action.
	 *
	 * @param integer $post_id
	 * @param object $post
	 * @param boolean $update
	 * @return void
	 */
	public function save_video_options( $post_id, $post, $update ){
		if( !isset( $_POST['cvwp_options_nonce'] ) || !wp_verify_nonce( $_POST['cvwp_options_nonce'], 'cvwp-save-post-video-options' ) ){
			return;
		}
		
		$video_options = $_POST['cvwp_post'];
		cvwp_update_post_options( $post_id, $video_options );
	}
	
	/**
	 * Implements plugin meta boxes to be displayed on post edit screen
	 *
	 * @uses add_meta_box()
	 * @uses cvwp_get_options()
	 *
	 * @param string $post_type
	 * @param object $post
	 * @return void
	 */
	public function register_meta_boxes( $post_type, $post ){
		// show only on allowed post types
		$options = cvwp_get_options( 'settings' );
		if( !in_array( $post_type, $options['post_types'] ) ){
			return;
		}
		
		add_meta_box(
			'cvwp-video-query', 
			__( 'VideographyWP - query video', 'videographywp' ), 
			array( $this, 'video_query_meta_box' ), 
			$post_type,
			'side',// $context
			'high' // $priority
		);		
		
		add_meta_box(
			'cvwp-video-options',
			__( 'VideographyWP - video settings', 'videographywp' ),
			array( $this, 'video_settings_meta_box' ),
			$post_type,
			'normal',
			'high'
		);
		
		wp_enqueue_script(
			'cvwp-video-player',
			cvwp_get_uri( 'assets/front-end/js/video-player2' . __cvwp_js_file_suffix() . '.js' ),
			array( 'jquery' )
		);
		
		// enqueue the video query script
		$handle 	= $this->enqueue_script( 'video-query', array( 'jquery' ) );
		// get ajax call details
		$add_video_ajax		= parent::get_ajax_action( 'video_query' );
		$remove_video_ajax 	= parent::get_ajax_action( 'remove_video' );
		
		// add some messages
		$messages = array(
			'empty_video_query' => __('Please select source and enter video ID.', 'videographywp'),
			'empty_video_url' 	=> __('Please provide a video URL.', 'videographywp'),
			'loading_video' 	=> __('Querying for video ...', 'videographywp'),
			'querying_video'	=> __('Not done yet, please wait...', 'videographywp'),
			'query_error' 		=> __('There was an error, please try again', 'videographywp'),
			'removing_video'	=> __('Removing video ...', 'videographywp'),
			'video_removed_message' => __('Video removed.', 'videographywp')
		);
		
		// check theme compatibility
		$selectors = false;
		
		// output JS action and nonce for the AJAX call
		wp_localize_script(
			$handle, 
			'cvwp_query',
			array(
				'add_video' 	=> $add_video_ajax,
				'remove_video' 	=> $remove_video_ajax,
				'messages' 		=> $messages,
				'selectors' 	=> $selectors,
				'is_gutenberg'  => absint( cvwp_is_gutenberg_page() )
			)
		);	
		// enqueue some styling
		$this->enqueue_style( 'post-video' );
	}
	
	/**
	 * Meta box callback
	 * 
	 * Display video query post meta box
	 *
	 * @param object $post
	 * @return void
	 */
	public function video_query_meta_box( $post ){
		// get the post video options
		$options = cvwp_get_post_options( $post->ID );
		$api_keys = cvwp_get_options( 'apis' );
		
		// include template
		$path = $this->template_path( 'video-query', 'metabox' );
		include_once $path;
	}
	
	/**
	 * Meta box callback
	 * 
	 * Display video settings post meta box
	 *
	 * @param object $post
	 * @return void
	 */
	public function video_settings_meta_box( $post ){
		// get the post video options
		$options = cvwp_get_post_options( $post->ID );
		
		if( $options['video']['video_id'] && $options['video']['source'] ){
			/**
			 * Run action on video settings meta box display
			 * @param $post - post object being displayed
			 */
			do_action( 'cvwp_video_settings_metabox', $post->ID );
		}		
		
		// include template
		$path = $this->template_path( 'video-settings', 'metabox' );
		include_once $path;
	}
	
	/**
	 * Add extra actions links to plugin row in plugins page
	 * @param array $links
	 * @param string $file
	 */
	public function plugin_action_links( $links, $file ){
		// add Settings link to plugin actions
		$plugin_file = plugin_basename( CVWP_PATH . '/index.php' );
		if( $file == $plugin_file ){
			array_unshift( $links, sprintf( '<a href="%s">%s</a>', menu_page_url( 'cvwp-settings' , false ), __('Settings', 'videographywp') ) );
		}
		
		// check if Lite is installed and disable activate link
		$pro_file = 'videographywp-pro/index.php';
		if( $file == $pro_file ){
			unset( $links['activate'] );
		}
		
		return $links;
	}
	
	/**
	 * Add meta description to plugin row in plugins page
	 * @param array $meta
	 * @param string $file
	 */
	public function plugin_meta( $meta, $file ){
		// check if Lite is installed and disable activate link
		$lite_file = 'videographywp-pro/index.php';
	
		if( $file == $lite_file ){
			$meta[] = '<span class="file-error">' . __('To activate VideographyWP PRO you must first deactivate VideographyWP Lite.', 'videographywp') . '</span>';
		}
		return $meta;
	}
	
	/**
	 * Display an admin notice to let users know where to set up the plugin preferences.
	 * @return void
	 */
	public function admin_notices(){
		// Don't show the connect notice anywhere but the plugins.php after activating
		$screen = get_current_screen();
		// get options
		$options = cvwp_get_options( 'settings' );
		// if warnings are disabled, stop here
		if( !$options['show_warnings'] ){
			return;
		}
?>
<?php if( 'plugins' == $screen->parent_base && current_user_can( 'manage_options' ) ):?>
<div id="cvwp-message" class="updated notice is-dismissible">
	<p><?php printf( __( 'You should set up your VideographyWP video %spreferences%s.', 'videographywp' ), '<a href="' . menu_page_url( 'cvwp-settings', false ) . '">',  '</a>' );?></p>
</div>
<?php endif;?>

<?php if( isset( $screen->post_type ) && in_array( $screen->post_type , $options['post_types'] ) ):?>
	<?php if( cvwp_theme_is_compatible() && !cvwp_theme_embed_enabled() ):?>
	<div id="cvwp-message" class="updated notice notice-success is-dismissible">
		<p><?php printf( __( 'Your current active theme is compatible with VideographyWP plugin. %s can import video details directly into your theme custom fields.', 'videographywp' ), '<a href="' . cvwp_plugin_url() . '"><strong>VideographyWP<sup>PRO</sup></strong></a>' );?></p>
	</div>
	<?php elseif( !cvwp_theme_is_compatible() && !cvwp_is_embed_allowed() ):?>
		<?php if( __cvwp_disallow_plugin_embeds() ):?>
			<div id="cvwp-message" class="updated notice notice-success is-dismissible">
				<p><?php _e( "A filter set in your WordPress Theme or a plugin has disabled embedding by VideographyWP plugin.", 'videographywp' );?></p>
			</div>
		<?php else:?>
			<div id="cvwp-message" class="error is-dismissible">
				<p><?php printf( __( "Please note that embedding by VideographyWP plugin is not allowed so your videos won't be embedded. You should enable the embed option in plugin %ssettings%s.", 'videographywp' ), '<a href="' . menu_page_url( 'cvwp-settings', false ) . '">',  '</a>' );?></p>
			</div>
		<?php endif;?>	
	<?php endif;?>
	
<?php endif;?>

<?php
	}
	
	/**
	 * Admin init callback that displays plugin review reminder
	 */
	public function review_notice(){
		
		$m = "It's great to see that you've been using <strong>VideographyWP</strong> plugin for a while now. Hopefully you're happy with it! <br>If so, would you consider leaving a positive review? It really helps to support the plugin and helps others to discover it too!";
		$user = new CVWP_User( 'cvwp_ignore_notice_nag' );
		$message = new CVWP_Message( $m , 'https://wordpress.org/support/plugin/videographywp/reviews/#new-post' );
		new CVWP_Review_Callout( 'cvwp_plugin_review_callout' , $message, $user );		
	}
	
	/**
	 * Returns a plugin page template file path.
	 * Template pages should be stored in plugin folder views and
	 * named like: page-NAME_OF_PAGE.php
	 * 
	 * Function use: $this->template_path( 'NAME_OF_PAGE' )  
	 * 
	 * @param string $page
	 * @return string
	 */
	protected function template_path( $template, $type = 'page' ){
		$page = preg_replace( '|([^a-z\-\_])|' , '', $template);
		$prefix = 'page' == $type ? 'page' : 'metabox';		
		$path = cvwp_path( sprintf( 'views/%s-%s.php', $prefix, $template ) );
		return $path;
	}
	
	/**
	 * Enqueue admin specific scripts
	 *
	 * @param string $script
	 * @param array $dependency
	 * @return string - script handle
	 */
	protected function enqueue_script( $script, $dependency = array() ){
		$script = preg_replace( '|([^a-z\-\_\.])|' , '', $script);
		$url 	= cvwp_get_uri( sprintf( 'assets/back-end/js/%s.js', $script ), $script );
		$handle = 'cvwm_' . $script;
		wp_enqueue_script(
			$handle,
			$url,
			$dependency,
			CVWP_VERSION
		);
		return $handle;
	}

	/**
	 * Enqueue admin specific style
	 *
	 * @param string $style
	 * @param array $dependency
	 * @return string - style handle
	 */
	protected function enqueue_style( $style, $dependency = array() ){
		$style 	= preg_replace( '|([^a-z\-\_\.])|' , '', $style);
		$url 	= cvwp_get_uri( sprintf( 'assets/back-end/css/%s.css', $style ), $style );
		$handle = 'cvwm_' . $style;
		wp_enqueue_style(
			$handle,
			$url,
			$dependency,
			CVWP_VERSION
		);
		return $handle;
	}
}
CVWP_Admin::init();