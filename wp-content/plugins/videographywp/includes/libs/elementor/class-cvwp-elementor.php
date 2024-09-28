<?php
/**
 * Developed by CodeFlavors.
 * Last modified 4/11/19 2:56 PM.
 * Copyright (c) 2019.
 */

/**
 * Project videographywp-pro
 * Created 4/11/2019
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class CVWP_Elementor
 */
class CVWP_Elementor{
	/**
	 * CVWP_Elementor constructor.
	 */
	public function __construct() {
		add_filter( 'elementor/widget/render_content', array( $this, 'render' ), 10, 2 );
	}

	/**
	 * Callback for Elementor render filter. Replaces the featured image with the video embed.
	 *
	 * @see https://code.elementor.com/php-hooks/#elementorwidgetrender_content
	 * @see \ElementorPro\Modules\ThemeBuilder\Widgets\Post_Featured_Image
	 *
	 * @param $content
	 * @param $widget
	 *
	 * @return string
	 */
	public function render( $content, $widget ){
		if( 'theme-post-featured-image' != $widget->get_name() ){
			return $content;
		}

		return CVWP_Plugin::filter_thumbnail_html( $content, get_the_ID() );
	}
}


