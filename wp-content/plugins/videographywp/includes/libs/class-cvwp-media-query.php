<?php
// No direct access
if( !defined( 'ABSPATH' ) ){
	die();
}

/**
 * Media query abstract class. 
 * Must be implemented by child classes.
 * Do queries to different video platforms APIs
 */
abstract class CVWP_Media_Query{
	/**
	 * Store errors
	 */
	private $errors;
	/**
	 * Store the result of the API query
	 */
	private $result;
	/**
	 * Stores any default options set by the video source.
	 * @var array
	 */
	private $source_options = array();
	
	/**
	 * Must be implemented in child classes. Must return an array containing the sources
	 * that can be queried for content.
	 */
	abstract protected function media_sources();
	
	/**
	 * Constructor; takes as argument an array containing the media source key
	 * implemented by child class using above media_sources() method and the query
	 * string (video ID, image URL, etc) to be used when API query is made.
	 * 
	 * @uses $this->register_error()
	 * @uses is_wp_error()
	 * @uses wp_remote_get()
	 * @uses wp_remote_retrieve_response_code()
	 * 
	 * @param array $args
	 * - source: the registered source to be queried
	 * - query: the query to be made to the API endpoint
	 */
	public function __construct( $args ){
		
		$defaults = array(
			'source' 	=> false,
			'query'		=> false
		);
		
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		// if no source, register error and stop
		if( !$source ){
			return $this->register_error( 'cvwp-media-query-no-source', __('No media source specified', 'videographywp') );
		}
		// no video ID specified, register error and stop
		if( !$query ){
			return $this->register_error('cvwp-media-query-no-id', __('No media query ID/URL specified', 'videographywp'));
		}
		
		// source not identified, register error and stop
		$source_data = $this->get_source( $source );
		if( is_wp_error( $source_data ) ){
			return $source_data;
		}
		
		// create the request
		$request_url = sprintf( $source_data['query'], $query );
		$response = wp_remote_get( $request_url );
		
		// query returned WP error, register errors and stop
		if( is_wp_error( $response ) ){
			$codes = $response->get_error_codes();
			foreach( $codes as $code ){
				$this->register_error( $code, $response->get_error_code( $code ) );
			}
			return;
		}
		
		// media not found
		if( 404 == wp_remote_retrieve_response_code( $response ) ){
			return $this->register_error( 'cvwp-media-not-found', __('The media was not found.', 'videographywp'), $response );
		}
		
		// request returned fail error code, register error and stop
		if( 200 != wp_remote_retrieve_response_code( $response ) ){
			return $this->register_error( 'cvwp-media-request-fail', __('Request failed.', 'videographywp'), $response );
		}
		
		$content 		= json_decode( wp_remote_retrieve_body( $response ), true );
		$this->result 	= $this->format_entry( $content, $source, $source_data, $query ) ;
		// set source defaults if any
		$this->source_options 	= isset( $source_data['options'] ) ? $source_data['options'] : array();	
	}
	
	/**
	 * Formats entries from different sources into a unified array
	 * 
	 * @uses $this->get_array_value()
	 * 
	 * @param array $content - video details retrieved from video platform
	 * @param string $source - source name
	 * @param array $source_data - source details
	 * @return array
	 */
	protected function format_entry( $content, $source, $source_data, $query = false ){
		// start the result
		$result = array(
			'source' => $source
		);
		foreach( $source_data['mapping'] as $key => $result_key ){
			// missing keys are set by default to false
			if( !$result_key ){
				$result[ $key ] = false;
				continue;
			}
			
			// some info might be stored into a multidimensional array, get it here
			if( is_array( $result_key ) ){
				$result[ $key ] = $this->get_array_value( $result_key, $content );
				continue;
			}
			
			// default processing
			$result[ $key ] = isset( $content[ $result_key ] ) ? $content[ $result_key ] : false;
		}
		
		return $result;
	}
	
	/**
	 * Given an array of keys $keys, it will return the coresponding value from 
	 * the multidimensional array $raw_entry
	 * 
	 * @param array $keys - array of keys from a multidimensional array
	 * @param array $raw_entry - multidimensional array
	 */
	protected function get_array_value( $keys, $raw_entry ){
		
		$r = array();
		if( isset( $raw_entry[ $keys[0] ] ) ){
			$r = $raw_entry[ $keys[0] ];
			unset( $keys[0] );
		}else{
			return false;
		}
		
		foreach( $keys as $key ){
			if( isset( $r[ $key ] ) ){
				$r = $r[ $key ];
			}
		}
		
		return $r;	
	}
	
	/**
	 * Registers an error
	 * 
	 * @uses WP_Error() class
	 * 
	 * @param string $code - error code
	 * @param string $message - error message
	 * 
	 * @return WP_Error
	 */
	protected function register_error( $code, $message, $data = false ){
		if( !is_wp_error( $this->errors ) ){
			$this->errors = new WP_Error();
		}
		$this->errors->add($code, $message, $data);
		return $this->errors;
	}
	
	/**
	 * Get the details of a given video source
	 * 
	 * @uses $this->media_sources()
	 * 
	 * @param string $source - a registered video source ( vimeo, youtube, etc )
	 * @return array
	 */
	private function get_source( $source ){
		
		$sources = (array) $this->media_sources();
		if( !array_key_exists( $source , $sources ) ){
			return $this->register_error('cvwp-media-query', __('Source not available', 'videographywp'));
		}
		
		return $sources[ $source ];		
	}
	
	/**
	 * Returns the result or WP error in case of failing
	 * 
	 * @uses is_wp_error()
	 * 
	 * @return mixed - result is either video details array or WP_Error object
	 */
	public function get_result(){
		if( is_wp_error( $this->errors ) ){
			return $this->errors;
		}
		return $this->result;
	}
	
	/**
	 * Get the registered video sources.
	 * 
	 * @uses $this->media_sources()
	 * 
	 * @return array
	 */
	public function get_sources(){
		return $this->media_sources();
	}

	/**
	 * Returns the source options if any
	 *
	 * @return array
	 */
	public function get_default_options(){
		return (array)$this->source_options;
	}
}

/**
 * Query various video APIs
 * 
 * @uses CVWP_Media_Query
 */
class CVWP_Video_Query extends CVWP_Media_Query{
	/**
	 * Constructor. Arguments array must have this form:
	 * 
	 * array(
	 * 		'source' 	=> 'source key from media_sources() method',
	 * 		'video_id' 	=> 'query parameter value'
	 * )
	 */	
	public function __construct( $args ){
		$defaults = array(
			'source' 	=> false, // the source to query videos from
			'video_id' 	=> false, // ID of the video 
		);
		$args = wp_parse_args( $args, $defaults );
		
		$params = array(
			'source' => $args['source'],
			'query' => $args['video_id'] 
		);
		parent::__construct( $params );
	}
	
	/**
	 * Set video media sources
	 */
	protected function media_sources(){
		
		$api_keys = cvwp_get_options( 'apis' );
		
		// registered sources
		$sources = array(
			'youtube' => array(
				'details' => array(
					'name' => 'YouTube',
					'url' => 'http://www.youtube.com'
				),
				// API V3 query
				'query' => 'https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,status&id=%s&key=' . trim( $api_keys['youtube_key'] ),
				'mapping' => array(
					'video_id' 		=> 'id',
					'title' 		=> array( 'snippet', 'title' ),
					'description' 	=> array( 'snippet', 'description' ),
					'uploaded' 		=> array( 'snippet', 'publishedAt' ),
					'image' 		=> array( 'snippet', 'thumbnails', 'standard', 'url' ),
					'duration' 		=> array( 'contentDetails', 'duration' ),
					'width' 		=> false,
					'height' 		=> false
				),
				'embed' => '<iframe src="https://www.youtube.com/embed/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen></iframe>',
				'url'	=> 'https://www.youtube.com/watch?v=%s'
			)
		);
		
		/**
		 * Make query to oEmbed if the API key is empty.
		 */
		if ( empty( $api_keys['youtube_key'] ) ) {
			$sources['youtube']['query']   = 'https://www.youtube.com/oembed?url=https%%3A%%2F%%2Fwww.youtube.com%%2Fwatch%%3Fv%%3D%s&format=json';
			$sources['youtube']['mapping'] = [
				'video_id'    => false,
				'title'       => 'title',
				'description' => false,
				'uploaded'    => false,
				'image'       => 'thumbnail_url',
				'duration'    => false,
				'width'       => 'width',
				'height'      => 'height',
			];
		}
		
		return $sources;	
	}
	
	/**
	 * Formats entries from different sources into a unified array
	 * @param array $content - video details retrieved from video platform
	 * @param string $source - source name
	 * @param array $source_data - source details
	 */
	protected function format_entry( $content, $source, $source_data, $video_id = false ){
		switch ( $source ) {
			case 'youtube':
				if ( isset( $content['items'][0] ) ) {
					$content = $content['items'][0];
				} else {
					$api_keys = cvwp_get_options( 'apis' );
					if ( ! empty( $api_keys['youtube_key'] ) ) {
						return new WP_Error(
							'cvwp-video-not-found',
							__(
								'Your video couldn\'t be found',
								'videographywp-pro'
							)
						);
					}
				}
				break;
		}
		
		// start the result
		$result = array(
			'source' => $source
		);
		foreach( $source_data['mapping'] as $key => $result_key ){
			// some feeds might not return the video ID. Pass it from the params
			if( 'video_id' == $key && !$result_key ){
				$result[ $key ] = $video_id;
				continue;
			}
			
			// missing keys are set by default to false
			if( !$result_key ){
				$result[ $key ] = false;
				continue;
			}
			
			// process YouTube thumbnail different
			if( 'image' == $key ){
				$thumbs = $this->get_array_value( array( 'snippet', 'thumbnails' ) , $content );
				if( $thumbs ){
					$img = end( $thumbs );
					$result[ $key ] = $img['url'];
					continue;
				}
			}
			
			// some info might be stored into a multidimensional array, get it here
			if( is_array( $result_key ) ){
				if( 'duration' == $key ){
					$value = $this->_iso_to_timestamp( $this->get_array_value( $result_key, $content ) );
				}else{
					$value = $this->get_array_value( $result_key, $content );
				}
				
				$result[ $key ] = $value;
				continue;
			}
			
			// default processing
			$result[ $key ] = $content[ $result_key ];
		}
		
		return $result;
	}
	
	/**
	 * Converts ISO time ( ie: PT1H30M55S ) to timestamp
	 * 
	 * @param string $iso_time - ISO time
	 * @return int - seconds
	 */
	private function _iso_to_timestamp( $iso_time ){
		preg_match_all('|([0-9]+)([a-z])|Ui', $iso_time, $matches);
		if( isset( $matches[2] ) ){
			$seconds = 0;
			foreach( $matches[2] as $key => $unit ){
				$multiply = 1;
				switch( $unit ){
					case 'M':
						$multiply = 60;
					break;	
					case 'H':
						$multiply = 3600;
					break;	
				}
				$seconds += $multiply * $matches[1][ $key ];
			}
		}
		return $seconds;
	}
	
	/**
	 * Returns registered video sources
	 */
	public function get_video_sources(){
		return parent::get_sources();
	}
}