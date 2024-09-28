<?php
// No direct include
if( ! defined( 'ABSPATH' ) ){
	die();
}

class CVWP_Amp{

	public function __construct() {
		add_action( 'pre_amp_render_post', array( $this, 'amp_render_post' ), 10, 1 );
	}

	/**
	 *
	 * @internal
	 */
	public function amp_render_post(){
		add_filter( 'cvwp_video_embed_html', array( $this, 'filter_video_output' ), 10, 3 );
		add_filter( 'cvwp_video_outside_content', array( $this, 'filter_content_output' ), 10, 3  );
	}

	/**
	 * @internal
	 *
	 * @param string $output
	 * @param WP_Post $post
	 * @return string
	 */
	public function filter_video_output( $output, WP_Post $post, $position ){
		if( 'featured_image' == $position ){
			return;
		}
		return "\n" . cvwp_get_video_url( $post->ID ) . "\n";
	}

	/**
	 * @param $content
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function filter_content_output( $content, WP_Post $post, $position ){
		if( 'featured_image' != $position ) {
			remove_filter( 'cvwp_video_embed_html', array( $this, 'filter_video_output' ), 10 );
		}

		return "\n" . cvwp_get_video_url( $post->ID ) . "\n" . $content;
	}

}