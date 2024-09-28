<?php
// No direct include
if( ! defined( 'ABSPATH' ) ){
	die();
}

/**
 * Compatibility with WooCommerce.
 * Will embed video into the product image gallery.
 * 
 * @author CodeFlavors
 */
class CVWP_Woocommerce{

	/**
	 * Constructor, sets up all needed hooks
	 */
	public function __construct(){
        // hook to WooCommerce init
        add_action( 'woocommerce_init', array(
            $this,
            'init'
        ) );
	}

	/**
	 * Action "woocommerce_init" callback.
	 * Sets all filters and actions needed to make the plugin compatible with WooCommerce
	 */
	public function init(){
		new CVWP_Woocommerce_Admin();
	}
}


class CVWP_Woocommerce_Admin{
	/**
	 * CVWP_Woocommerce_Admin constructor.
	 */
    public function __construct() {
        add_filter( 'cvwp_register_plugin_settings_tab', array( $this, 'plugin_settings_tab' ) );
    }

	/**
     * Add WooCommerce tab to plugin settings
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function plugin_settings_tab( $tabs ){
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

	    $tabs['cvwp_woocommerce'] = array(
			'title' => __( 'WooCommerce <sup>(PRO)</sup>', 'videographywp' ),
			'callback' => array( $this, 'tab_output' )
		);
		return $tabs;
	}

	/**
	 * Plugin settings WooCommerce tab output
	 */
	public function tab_output(){
		?>
        <h4><i class="dashicons dashicons-products"></i> <?php _e( 'WooCommerce <sup>PRO feature</sup>', 'videographywp' );?></h4>

        <p>
            <?php _e( 'Congratulations! It seems that you are using WooCommerce.', 'videographywp' );?><br />
            <?php _e( 'If you plan on using videos in your product galleries, VideographyWP PRO can help you with this.', 'videographywp' );?><br />
        </p>

        <p>
            <a class="button cvwp-video-button"
               href="#"
               data-source="youtube"
               data-video_id="ZzYw2cSlyOM"
               data-width="900"
               data-controls="1"
               data-aspect="16x9"
               data-volume="55"
               data-ssl="0"
               data-lazy_load="0">
	            <?php _e( 'Watch how you can add videos to WooCommerce product galleries', 'videographywp' );?>
            </a>
        </p>

        <h4><i class="dashicons dashicons-welcome-learn-more"></i> <?php _e( 'Additional resources', 'videographywp' );?></h4>
        <p><?php _e( 'Below you will find links to documentation, plugin usage and tutorials.', 'videographywp' );?></p>

        <ul>
            <li><a target="_blank" href="http://demo.videographywp.com"><?php _e( 'Test VideographyWP PRO live', 'videographywp' );?></a></li>
            <li><a target="_blank" href="<?php echo cvwp_plugin_url( 'woocommerce-product-video/' );?>"><?php _e( 'How to add a video into a WooCommerce product gallery', 'videographywp' );?></a></li>
            <li><a target="_blank" href="<?php echo cvwp_plugin_url( 'documentation/woocommerce/woocommerce-wp-theme-compatibility/' );?>"><?php _e( 'Learn more about WooCommerce theme compatibility', 'videographywp' );?></a></li>
        </ul>

        <h4><i class="dashicons dashicons-layout"></i> <?php _e( 'Third party compatibility', 'videographywp' );?></h4>
        <p>
            <?php _e( 'By default, the plugin is compatible with any WooCommerce enabled WordPress theme that uses the default WooCommerce product gallery output and JavaScript.', 'videographywp' );?><br />
            <?php _e( 'Given that themes are allowed to modify the product gallery and implement different customizations, we are striving to offer compatibility with all themes requested by our users.', 'videographywp' );?><br />

        </p>
        <p class="description cvwp-notice">
            <?php
                printf( __( "For more information about compatibility with WooCommerce, please don't hesitate to %scontact us%s.", 'videographywp' ),
                    '<a href="' . cvwp_plugin_url( 'contact' ) . '">',
                    '</a>'
                );
            ?>
        </p>
        <?php
	}
}