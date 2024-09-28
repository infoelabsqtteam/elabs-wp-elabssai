<?php
// No direct include
if( !defined('ABSPATH') ){
	die();
}

/**
 * Returns all registered shortcodes
 * @return array
 */
function cvwp_get_shortcodes(){
	return CVWP_Shortcodes::get_shortcodes();
}

/**
 * Query the video details for a given video ID on a given source.
 * $args
 * 	- source: the video source ( youtube or vimeo )
 * 	- video_id: the ID of the video
 * 
 * @uses CVWP_Video_Query class
 * 
 * @param array $args
 * @return mixed - WP_Error or array of video details
 */
function cvwp_query_video( $args ){	
	if( !class_exists('CVWP_Video_Query') ){
		require_once cvwp_path( 'includes/libs/class-cvwp-media-query.php' );
	}
	
	$video_query 	= new CVWP_Video_Query( $args );
	$video 			= $video_query->get_result();
	$options 		= $video_query->get_default_options();
	
	return array( 'video' => $video, 'options' => $options );	
}

/**
 * Check if current page uses Gutenberg or classic editor
 * Adapted from https://github.com/Freemius/wordpress-sdk/commit/8a87d389c647b4588bfe96fc7d420d62a48cbac5
 *
 * @return bool
 */
function cvwp_is_gutenberg_page() {
	if ( function_exists( 'is_gutenberg_page' ) &&
	     is_gutenberg_page()
	) {
		// The Gutenberg plugin is on.
		return true;
	}
	$current_screen = get_current_screen();
	if ( method_exists( $current_screen, 'is_block_editor' ) &&
	     $current_screen->is_block_editor()
	) {
		// Gutenberg page on 5+.
		return true;
	}
	return false;
}

/**
 * Returns default plugin post options
 *
 * @return array
 */
function cvwp_post_defaults(){
	if( !class_exists( 'CVWP_Post_Options' ) ){
		require_once cvwp_path( 'includes/libs/class-cvwp-options.php' );
	}
	
	$o = CVWP_Post_Options::init();
	return $o->get_defaults();
}

/**
 * Outputs checked="checked" if given value is true.
 * Useful for checkboxes
 *
 * @param bool $val
 * @param bool $echo
 * @return string
 */
function cvwp_check( $val, $echo = true ){
	$checked = '';
	if( is_bool( $val ) && $val ){
		$checked = ' checked="checked"';
	}
	if( $echo ){
		echo $checked;
	}else{
		return $checked;
	}	
}

/**
 * Outputs an HTML checkbox 
 *
 * @param string $field_name
 * @param boolean $value
 * @return string
 */
function cvwp_checkbox( $field_name, $value, $extra_attr = array(), $echo = true ){	
	$output 	= '<input type="checkbox" name="%s" id="%s" value="1" %s %s />';
	$checked 	= cvwp_check( (bool)$value, false ); 
	
	$attrs = '';
	if( $extra_attr ){
		$att = array();
		foreach( $extra_attr as $param => $value ){
			$att[] = esc_attr( $param ) . '="' . esc_attr( $value ) . '"';
		}
		$attrs = implode( ' ', $att );
	}
	$result = sprintf( $output, esc_attr( $field_name ), esc_attr( $field_name ), $checked, $attrs );	
	
	if( $echo ){
		echo $result;
	}
	return $result;
}

/**
 * Returns a checkbox field for a given plugin option settings field name.
 *
 * @param string $field_name
 * @param boolean $echo
 * @return string
 */
function cvwp_settings_checkbox( $field_name, $option_set = 'settings', $extra_attr = array(), $echo = true ){
	$options = cvwp_get_options( $option_set );
	if( !array_key_exists( $field_name , $options ) ){
		trigger_error( sprintf( 'Unknow field name: %s ', $field_name ) , E_USER_ERROR );
		return;
	}
	
	$value = $options[ $field_name ];
	$output = cvwp_checkbox( $field_name, $value, $extra_attr, false );
	if( $echo ){
		echo $output;
	}
	return $output;
}

/**
 * Returns a list of checkboxes for a given set of options
 * @param array $attr - attributes for displaying the checkboxes
 */
function cvwp_checkboxes( $attr ){
	$defaults = array(
		'name' 		=> false,
		'id'		=> '',
		'selected' 	=> array(),
		'options' 	=> array(),
		'separator' => '<br />',
		'echo'		=> true
	);
	extract( wp_parse_args($attr, $defaults), EXTR_SKIP );
	
	if( !$options ){
		return false;
	}
	if( !is_array( $selected ) ){
		$seelected = array();
	}
	if( empty( $id ) ){
		$id = $name;
	}
	
	$output = '';
	foreach( $options as $value => $label ){
		$checked = in_array($value, $selected) ? 'checked="checked"' : '';
		$el_id = esc_attr( $id . $value );
		$output .= sprintf(
			'<input type="checkbox" name="%1$s" value="%2$s" id="%3$s" %4$s /><label for="%3$s">%5$s</label>%6$s',
			$name . '[]',
			$value,
			$el_id,
			$checked,
			$label,
			$separator
		);
	}
	
	if( $echo ){
		echo $output;
	}
	return $output;
}

/**
 * Return a list of checkboxes corresponding to all current registered post types
 * @param array $attr
 */
function cvwp_post_types_checkboxes( $attr = array() ){

	$defaults = array(
		'name' 		=> 'post_types',
		'selected' 	=> array(),
		'echo' 		=> true
	);

	$args = wp_parse_args($attr, $defaults);
	$cpt = get_post_types( array(
		'public' 	=> true,
		'_builtin' 	=> false
	), 'objects' );

	$options = array(
		'post' => __( 'Post', 'cvwp' ),
		'page' => __( 'Page', 'cvwp' )
	);
	foreach( $cpt as $post_type => $obj ){
		if( 'product' == $post_type ){
			continue;
		}else {
			$options[ $post_type ] = $obj->label;
		}
	}

	$args['options'] = $options;
	return cvwp_checkboxes($args);
}

/**
 * Displays video sources radio buttons
 *
 * @uses CVWP_Video_Query()
 *
 * @param array $args
 * 					- name : variable name
 * 					- id : field ID
 * 					- selected: selected options
 * 					- echo : echo output
 * 					- separator: radio fields separator
 * 
 * @return string - HTML output
 */
function cvwp_video_sources_checkboxes( $args ){
	$defaults = array(
		'name' 		=> 'cvwp_video_source',
		'id' 		=> false,
		'selected' 	=> false,
		'echo' 		=> true,
		'separator' => '<br />'
	);
	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
	
	$sources = cvwp_get_video_sources();
	
	if( !$id ){
		$id = $name;		
	}
	
	$output = '';	
	foreach( $sources as $source_id => $source ){
		$checked = $source_id == $selected ? ' checked="checked"' : false;
		$el_id = $id . '-' . $source_id;
		
		$output .= sprintf( '<input type="radio" name="%1$s" id="%2$s" value="%3$s"  /> <label for="%2$s">%4$s</label>%5$s',
			$name,
			$el_id,
			$source_id,
			$source['details']['name'],
			$separator 
		);		
	}
	
	if( $echo ){
		echo $output;
	}else{
		return $output;
	}	
}

/**
 * Display select box
 * @param array $args - see $defaults in function
 * @param bool $echo
 */
function cvwp_dropdown( $args = array() ){
	
	$defaults = array(
		'options' 	=> array(),
		'name'		=> false,
		'id'		=> false,
		'class'		=> '',
		'selected'	=> false,
		'use_keys'	=> true,
		'hide_if_empty' => true,
		'show_option_none' => __('No options', 'videographywp'),
		'select_opt'	=> __('Choose', 'videographywp'),
		'select_opt_style' => false,
		'attrs'	=> '',
		'echo' => true
	);
	
	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
	
	if( $hide_if_empty  && !$options && !$show_option_none){
		return;
	}
	
	if( !$id ){
		$id = $name;		
	}
	
	$output = sprintf( '<select autocomplete="off" name="%1$s" id="%2$s" class="%3$s" %4$s>', esc_attr( $name ), esc_attr( $id ), esc_attr( $class ), $attrs );
	if( !$options && $show_option_none ){
		$output .= '<option value="">' . $show_option_none . '</option>';	
	}elseif( $select_opt ){		
		$output .= '<option value=""'. ( $select_opt_style ? ' style="' . $select_opt_style . '"' : '' ) .'>' . $select_opt . '</option>';	
	}	
	
	foreach( $options as $val => $text ){
		$opt = '<option value="%1$s"%2$s>%3$s</option>';
		$value = $use_keys ? $val : $text;
		$c = $use_keys ? $val == $selected : $text == $selected;
		$checked = $c ? ' selected="selected"' : '';		
		$output .= sprintf($opt, $value, $checked, $text);		
	}
	
	$output .= '</select>';
	
	if( $echo ){
		echo $output;
	}
	
	return $output;
}

/**
 * Outputs a select box for choosing the aspect ratio for videos
 *
 * @param array $args
 * @return string
 */
function cvwp_select_aspect_ratio( $args = array() ){
	
	$default = array(
		'name' 			=> 'aspect_ratio',
		'id' 			=> false,
		'selected' 		=> false,
		'echo' 			=> true,
		'select' 		=> false,
		'select_opt'	=> false,
		'class' 		=> 'cvwp_video_aspect_ratio'
	);
	$args = wp_parse_args($args, $default);
	$args['options'] = array(
		'16x9' 		=> '16:9',
		'4x3' 		=> '4:3',
		'2.35x1' 	=> '2.35:1',
		'1x1'		=> '1:1'
	);
	$output = cvwp_dropdown( $args );	
	return $output;
}

/**
 * Outputs a select box for choosing available video embed position
 *
 * @param array $args
 * @return string - HTML
 */
function cvwp_select_embed_position( $args = array() ){
	
	$default = array(
		'name' 			=> 'embed_position',
		'id' 			=> false,
		'selected' 		=> false,
		'echo' 			=> true,
		'select' 		=> false,
		'select_opt'	=> false,
		'class' 		=> 'cvwp_embed_position'
	);
	$args = wp_parse_args($args, $default);
	$args['options'] = array(
		'featured_image'	=> __( 'Replace featured image', 'videographywp' ),
		'above_content' 	=> __( 'Above content', 'videographywp' ),
		'below_content' 	=> __( 'Below content', 'videographywp' ),
		'shortcode' 		=> __( 'Shortcode - embed', 'videographywp' ),
		'button'			=> __( 'Shortcode - button', 'videographywp' ),
		'no_embed'			=> __( 'Do not embed', 'videographywp' )	
	);
	$output = cvwp_dropdown( $args );	
	return $output;	
}

/**
 * Determine video ID and provider based on given URL.
 * 
 * @param $url - video URL
 * @return false/array - false if video URL couldn't be understood, array in case video was detected
 **/
function cvwp_get_provider( $url ) {
	// providers
    $providers = array(
        // YouTube.
        '#https://(www\.)?youtube\.com/watch\?v=([^&]+)#i' => [
            'youtube',
            true,
        ],
        '#https://(www\.)?youtube\.com/playlist.*#i' => [
            'youtube',
            true,
        ],
        '#https://youtu\.be/([^\?]+)#i'                  => [
            'youtube',
            true,
        ],
		'#https?://((m|www)\.)?youtube\.com/shorts/([^\?]+)#i' => [
			'youtube',
			true,
		],
    );

    /**
	 * Add extra regex's to determine the provider and video ID.
	 * Regex format:
	 * 
	 * regex => array( 'source_name', 'is_regex' )
	 * 
	 * @var array
	 */
	$providers = apply_filters( 'cvwp_providers', $providers );
	// store the results
	$result = false;
	
	foreach ( $providers as $matchmask => $data ) {
		list( $provider, $regex ) = $data;

		// Turn the asterisk-type provider URLs into regex
		if ( !$regex ) {
			$matchmask = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $matchmask ), '#' ) ) . '#i';
			$matchmask = preg_replace( '|^#http\\\://|', '#https?\://', $matchmask );
		}
		
		if ( preg_match( $matchmask, $url, $matches ) ) {
			$result = array(
				'provider' => $provider,
				'video_id' => end( $matches )
			);			
			break;
		}
	}
	return $result;
}

/**
 * Checks if theme is compatible with the plugin and if theme embedding setting is enabled.
 *
 * @return boolean
 */
function cvwp_theme_embed_enabled(){
	return false;
}

/**
 * Checks if a given option is overriden from plugin settings.
 *
 * @param string $option - option name
 * @return boolean - option is overriden(true) or isn't (false)
 */
function cvwp_is_override_set( $option ){
	return false;
}

/**
 * Gets post option value overriden by user from plugin settings
 *
 * @param string $option
 * @return mixed
 */
function cvwp_get_override_value( $option ){
	return false;
}

/**
 * Returns a string of themes concatenated by comma.
 *
 * @return string
 */
function cvwp_compatible_themes_list(){
	$themes = cvwp_get_compatible_themes();
	$t = array();
	foreach( $themes as $theme ){
		$t[] = sprintf( '<a href="%s" target="_blank">%s</a>', $theme['url'], $theme['theme_name'] );
	}
	
	return implode(', ', $t);
}

function cvwp_get_compatible_themes(){
	$themes = array(
			'truemag' => array(
					'theme_name' 	=> 'TrueMag',
					'url'			=> 'http://www.themeforest.net/item/true-mag-wordpress-theme-for-video-and-magazine/6755267/?ref=cboiangiu'
			),
			'newstube' => array(
					'theme_name' 	=> 'NewsTube',
					'url'			=> 'http://themeforest.net/item/newstube-magazine-blog-video/12132780/?ref=cboiangiu'
			),
			'avada' => array(
					'theme_name' 	=> 'Avada',
					'url'			=> 'http://themeforest.net/item/avada-responsive-multipurpose-theme/2833226/?ref=cboiangiu'
			),
			'goodwork' => array(
					'theme_name' 	=> 'Goodwork',
					'url'			=> 'http://themeforest.net/item/goodwork-modern-responsive-multipurpose-wordpress-theme/4574698/?ref=cboiangiu'
			),
			'simplemag' => array(
					'theme_name' => 'SimpleMag',
					'url' => 'http://themeforest.net/item/simplemag-magazine-theme-for-creative-stuff/4923427/?ref=cboiangiu'
			),
			'sahifa' => array(
					'theme_name' 	=> 'Sahifa',
					'url'			=> 'http://themeforest.net/item/sahifa-responsive-wordpress-newsmagazineblog/2819356/?ref=cboiangiu'
			),
			'wave' => array(
					'theme_name' 	=> 'Wave',
					'url'			=> 'http://themeforest.net/item/wave-video-theme-for-wordpress/45855/?ref=cboiangiu'
			),
			'detube' => array(
					'theme_name' 	=> 'DeTube',			// theme name to display
					'url'			=> 'http://themeforest.net/item/detube-professional-video-wordpress-theme/2664497/?ref=cboiangiu'
			),
			'video' => array(
					'theme_name' 	=> 'Video',
					'url'			=> 'http://templatic.com/freethemes/video'
			),
			'beetube' => array(
					'theme_name' 	=> 'Beetube',
					'url'			=> 'http://themeforest.net/item/beetube-video-wordpress-theme/7055188/?ref=cboiangiu'
			),
			'videotube' => array(
					'theme_name' => 'VideoTube',
					'url' => 'http://themeforest.net/item/videotube-a-responsive-video-wordpress-theme/7214445/?ref=cboiangiu'
			),
			'videotouch' => array(
					'theme_name' => 'VideoTouch',
					'url' => 'http://themeforest.net/item/videotouch-video-wordpress-theme/9340715/?ref=cboiangiu'
			)
	);
	return $themes;
}
/**
 * Returns a string of video sources concatenated by comma.
 *
 * @return string
 */
function cvwp_video_platforms_list(){
	$sources = cvwp_get_video_sources();
	$t = array();
	foreach( $sources as $source ){
		$t[] = sprintf( '<a href="%s" target="_blank">%s</a>', $source['details']['url'], $source['details']['name'] );
	}
	return implode(', ', $t);
}

/**
 * Returns a string of video sources concatenated by comma.
 *
 * @return string
 */
function cvwp_pro_video_platforms_list(){
	$sources = array(
		'vimeo' => array(
			'details' => array(
					'name' => 'Vimeo',
					'url' => 'http://www.vimeo.com'
			)
		),			
		'dailymotion' => array(
			'details' => array(
				'name' 	=> 'Dailymotion',
				'url' 	=> 'http://www.dailymotion.com'
			)
		),
		'vine' => array(
			'details' => array(
				'name' 	=> 'Vine',
				'url'	=> 'https://vine.co/'
			)
		)
	);
	$t = array();
	foreach( $sources as $source ){
		$t[] = sprintf( '<a href="%s" target="_blank">%s</a>', $source['details']['url'], $source['details']['name'] );
	}
	return implode(', ', $t);
}

/**
 * Plugin URL
 * @param string $path
 * @return string
 */
function cvwp_plugin_url( $path = '' ){
	$url = 'http://videographywp.com/';	
	$campaign = array(
		'utm_source' 	=> 'plugin_lite',
		'utm_medium' 	=> 'doc_link',
		'utm_campaign' 	=> 'videographywp_lite'
	);
	
	$path = empty( $path ) ? $path : trailingslashit( $path );	
	return add_query_arg( $campaign, $url . $path );
}