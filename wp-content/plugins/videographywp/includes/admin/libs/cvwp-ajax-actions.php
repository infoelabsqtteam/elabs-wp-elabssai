<?php
// No direct include
if( !defined('ABSPATH') ){
	die();
}

/**
 * Admin class. Implements all AJAX Actions
 * Extended by CVWP_Admin() class
 * 
 * @since 1.0
 * @package VideographyWP plugin
 */
abstract class CVWP_Ajax_Actions{
	/**
	 * Constructor, initializes all AJAX actions
	 */
	protected function __construct(){
		// get the actions
		$actions = $this->actions();
		// add wp actions
		foreach( $actions as $action ){
			add_action( 'wp_ajax_' . $action['action'], $action['callback'] );
		}	
	}
	
	/**
	 * Defines all admin AJAX actions implemented by the plugin.
	 * @return array
	 */
	private function actions(){
		// holds all actions
		$actions = array();
		
		// add video query action
		$actions['video_query'] = array(
			// wp ajax action name
			'action' 	=> 'cvwp-query-video',
			// callback function
			'callback' 	=> array( $this, 'query_video' ),
			// nonce name and action
			'nonce' 	=> array(
				'name' 		=> 'cvwp_ajax_nonce',
				'action' 	=> 'cvwp-video-query'
			)
		);
		
		// remove video query action
		$actions['remove_video'] = array(
			'action' 	=> 'cvwp-remove-video',
			'callback' 	=> array( $this, 'remove_video' ),
			'nonce'		=> array(
				'name' 		=> 'cvwp-remove-nonce',
				'action'	=> 'cvwp-remove-video-nonce'
			)
		);

		$actions['test_yt_key'] = array(
			'action' => 'cvwp-test-yt-key',
			'callback' => array( $this, 'test_yt_key' ),
			'nonce' => array(
				'name' => 'cvwp-yt-key',
				'action' => 'cvwp-test-yt-key'
			)
		);

		return $actions;
	}
	
	/**
	 * Video query AJAX action callback
	 * 
	 * @uses $this->get_action_data()
	 * @uses check_ajax_referer()
	 * @uses wp_send_json_error()
	 * @uses wp_send_json_success()
	 * 
	 * @return void
	 */
	public function query_video(){
		// get the action details
		$action = $this->get_action_data( 'video_query' );
		// check referer
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );
		
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : -1;		
		
		if( !current_user_can( 'edit_post', $_POST['post_id'] ) ){
			wp_die( -1 );
		}
		
		if( defined('CVWP_GET_BY_URL') && CVWP_GET_BY_URL ){
			if( isset( $_POST['video_url'] ) ){
				$provider = cvwp_get_provider( $_POST['video_url'] );
				if( $provider ){
					$_POST['video_source'] = $provider['provider'];
					$_POST['video_id'] = $provider['video_id'];
				}else{
					$message = __( 'Video source and ID could not be identified into the URL you passed.', 'videographywp' );
					wp_send_json_error( $message );
					die();
				}				
			}
		}
		
		// query the video
		$args = array(
			'source' 	=> !isset( $_POST['video_source'] ) 	? false : $_POST['video_source'],
			'video_id' 	=> !isset( $_POST['video_id'] ) 		? false : $_POST['video_id']
		);
		
		// returns a combined array containing the default options for the given source and the video details
		$video_data = cvwp_query_video( $args );
		
		$video = $video_data['video'];
		// if anything went wrong, get the error message
		if( is_wp_error( $video ) ){
			$message = $video->get_error_message();
			$data = wp_remote_retrieve_body( $video->get_error_data() );
			if( $data ){
				$data = json_decode( $data, true );
				if( isset( $data['error']['message'] ) ){
					$message .= ' (' . $data['error']['message'] . ')';
				}
			}

			wp_send_json_error( $message );
			die();
		}
		
		// add some extra processed attributes that are used for themes compatibility
		$video['h_duration'] = cvwp_video_duration( $video['duration'] );
		$source = cvwp_get_video_source( $video['source'] );
		$video['url'] = sprintf( $source['url'], $video['video_id'] );
		// set video aspect
		$aspect = 'vine' == $video['source'] ? '1x1' : '16x9';		
		$width = 960;
		$height = cvwp_player_height( $aspect, $width );
		$video['height'] = $height;
		$video['embed'] = sprintf( $source['embed'], $video['video_id'], $width, $height );
		
		// create the response
		$response = array(
			'video' => $video,
			'post_id' => $post_id
		);
		
		// set the post thumbnail
		if( isset( $_POST['set_thumbnail'] ) && $_POST['set_thumbnail'] ){			
			$attachment_id = $this->import_thumbnail( $post_id, $video );
			
			if( $attachment_id ){
				// set image as featured for current post
				update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
				// create the return output
				remove_all_filters( 'admin_post_thumbnail_html' );
				$response['thumbnail'] = _wp_post_thumbnail_html( $attachment_id, $post_id );
				$response['attachment_id'] = $attachment_id;
			}
		}

		do_action( 'cvwp_ajax_video_query', $post_id );

		if( isset( $_POST['set_video'] ) && $_POST['set_video'] ){
			// set source defaults
			$vid_option = $video_data['options'];
			// add video details to option
			$vid_option['video']['source']   = $video['source'];
			$vid_option['video']['video_id'] = $video['video_id'];
			$vid_option['video']['duration'] = $video['duration'];
			$vid_option['video']['image'] 	 = $video['image'];

			// get default
			$option = cvwp_post_defaults();
			$option['video'] = array_merge( $option['video'], $vid_option['video'] );
			
			// set post option
			$result = cvwp_update_post_options( $_POST['post_id'], $option);
			
			if( $result ){
				$options = cvwp_get_post_options( $post_id );
				// set the post; needed in metaboxes
				global $post;
				$post = get_post( $post_id );
				
				ob_start();

				/**
				 * Run action on video settings meta box display
				 * @param $post - post object being displayed
				 */
				do_action( 'cvwp_video_settings_metabox', $post_id );
				
				include cvwp_path('views/metabox-video-settings.php');				
				$output = ob_get_clean();
				$response['video_settings'] = $output;
				
				ob_start();
				$api_keys = cvwp_get_options( 'apis' );
				include cvwp_path('views/metabox-video-query.php');				
				$output = ob_get_clean();
				$response['video_query'] = $output;
				
			}			
		};
		
		wp_send_json_success( $response );
		
		// always die
		die();
	}
	
	/**
	 * Detach video AJAX action callback
	 * 
	 * @uses $this->get_action_data()
	 * @uses check_ajax_referer()
	 * @uses wp_send_json_error()
	 * @uses wp_send_json_success()
	 * 
	 * @return void
	 */
	public function remove_video(){
		// get the action details
		$action = $this->get_action_data( 'remove_video' );
		// check referer
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );
		
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : -1;		
		
		if( !current_user_can( 'edit_post', $post_id ) ){
			wp_die( -1 );
		}
		
		$result = cvwp_update_post_options( $post_id, array(
			'video' => array(
				'source' 	=> '',
				'video_id' 	=> '',
				'duration'	=> 0
			)		
		));
		
		
		$options = cvwp_get_post_options( $post_id );
		ob_start();
		$api_keys = cvwp_get_options( 'apis' );
		include cvwp_path('views/metabox-video-query.php');
		$output = ob_get_clean();
		$response['video_query'] = $output;					
				
		wp_send_json_success( $response );		
		
		// always die
		die();
	}
	
	// HELPERS ---------------------------------------------------------------------
	
	/**
	 * Imports an image from a given URL into WP Media and sets it as featured image
	 * for the given post id.
	 * 
	 * @param int $post_id
	 * @param array $video
	 */
	private function import_thumbnail( $post_id, $video ){
		
		/**
		 * Filter on import start. Can be used to set a different attachment rather 
		 * than importing the image. Useful to avoid duplicates.
		 * 
		 * @param int $post_id - id of the post that will have the image attached to it
		 * @param array $video - array of video details
		 */
		$attachment_html = apply_filters('cvwp-image-imported', false, $post_id, $video);
		if( $attachment_html ){
			return $attachment_html;
		}
		
		if( is_array( $video['image'] ) ){
			$img = end( $video['image'] );
			$video['image'] = $img['url'];
		}
		
		/**
		 * Filter the image URL. Allows for example getting images of different sizes than the ones 
		 * that the plugin registers.
		 * 
		 * @param string $image_url	URL of image that will be imported
		 * @param int $post_id		ID of the post that will have the image attached to it
		 * @param array $video		Array of video details
		 */
		$image_url = apply_filters('cvwp-remote-image-url', $video['image'], $post_id, $video);
		
		// if max resolution query wasn't successful, try to get the registered image size
		$response = wp_remote_get( $image_url, array( 'sslverify' => false ) );	
		if( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$image_contents = $response['body'];
		$image_type 	= wp_remote_retrieve_header( $response, 'content-type' );
		
		// Translate MIME type into an extension
		if ( $image_type == 'image/jpeg' ){
			$image_extension = '.jpg';
		}elseif ( $image_type == 'image/png' ){
			$image_extension = '.png';
		}
		
		if( !isset( $image_extension ) ){
			return false;
		}
		
		// Construct a file name using post slug and extension
		$fn = sanitize_file_name( ( basename( remove_accents( preg_replace('/[^a-z0-9A-Z\-\s]/u', '', $video['title']) ) ) ) . $image_extension );
		
		/**
		 * Imported image filename filter.
		 * 
		 * @param string $fn - filename
		 * @param string $image_extension - image extension (.png, .jpg ...)
		 * @param int $post_id - id of post that will have the image attached to it
		 * @param array $video - array containing all video detais
		 */
		$new_filename = apply_filters('cvwp-remote-image-filename', $fn, $image_extension, $post_id, $video);
		
		// Save the image bits using the new filename
		$upload = wp_upload_bits( $new_filename, null, $image_contents );
		if ( $upload['error'] ) {
			return false;
		}
			
		$filename 	= $upload['file'];
		$wp_filetype = wp_check_filetype( basename( $filename ), null );
		$attachment = array(
			'post_mime_type'	=> $wp_filetype['type'],
			'post_title'		=> apply_filters( 'cvwp-remote-image-post-title', $video['title'] . ' - ' . $video['source'], $post_id, $video ),
			'post_content'		=> '',
			'post_status'		=> 'inherit',
			'guid'				=> $upload['url']
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		// you must first include the image.php file
		// for the function wp_generate_attachment_metadata() to work
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id, $attach_data );
	
		/**
		 * Action on remote image import finish.
		 * 
		 * @param int $attach_id - attachment image ID
		 * @param int $post_id - id of the post having the image attached to it
		 * @param array $video - array of video details
		 */
		do_action('cvwp-remote-image-processed', $attach_id, $post_id, $video);

		return $attach_id;		
	}

	/**
	 * Makes a check on the YouTube key and returns the errors
	 */
	public function test_yt_key(){
		// get the action details
		$action = $this->get_action_data( 'test_yt_key' );
		// check referer
		check_ajax_referer( $action['nonce']['action'], $action['nonce']['name'] );

		$api_keys = cvwp_get_options( 'apis' );
		//$url = 'https://www.googleapis.com/youtube/v3/videos?part=id&key=' . trim( $api_keys['youtube_key'] ) . '&chart=mostPopular';
		
		$api_key = esc_attr( trim( $_POST['api_key'] ) );
		$url = 'https://www.googleapis.com/youtube/v3/videos?part=id&key=' . $api_key . '&chart=mostPopular';
		
		$response = wp_remote_get( $url );
		if( 200 == wp_remote_retrieve_response_code( $response ) ){
			$message = __( "Congratulations, your key is active and working properly. ", 'videographywp' );
			if( $api_key !== $api_keys['youtube_key'] ){
				$message .= __( 'Remember to click "Save" to store this new key in your settings.', 'videographywp' );
			}
			
			wp_send_json_success( ['message' => $message] );
		}else{
			wp_send_json_error(
				array(
					'message' => __( 'Your key failed the test. Below is the entire response returned by YouTube API.', 'videographywp' ),
					'data' => print_r( wp_remote_retrieve_body( $response ), true )
				)
			);
		}

		die();
	}

	/**
	 * Gets all details of a given action from registered actions.
	 * Triggers user error if action isn't found.
	 * 
	 * @param string $key
	 * @return array
	 */
	private function get_action_data( $key ){
		$actions = $this->actions();
		if( array_key_exists( $key, $actions ) ){
			return $actions[ $key ];
		}else{
			trigger_error( sprintf( __( 'Action %s not found.'), $key ), E_USER_WARNING);
		}
	}
	
	/**
	 * Generates the WP AJAX action details to be used into scripts
	 * to make AJAX requests
	 * 
	 * @uses $this->get_action_data()
	 * @uses wp_create_nonce()
	 * 
	 * @param string $key
	 * @return array
	 */
	public function get_ajax_action( $key ){
		$data = $this->get_action_data( $key );
		if( !$data ){
			return;
		}
		
		$nonce = wp_create_nonce( $data['nonce']['action'] );
		$result = array(
			'action' => $data['action'],
			'nonce'  => array(
				'name' 	=> $data['nonce']['name'],
				'value' => $nonce
			)
		);
		return $result;
	}
}