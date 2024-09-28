<?php 
	// No direct access
if( !defined( 'ABSPATH' ) ){
	die();
}
?>
<div class="wrap about-wrap">
	<h1><?php _e( 'Welcome to VideographyWP', 'videographywp' );?> <?php echo CVWP_VERSION;?></h1>
	<div class="about-text"><?php _e( 'Congratulations! You just installed the most versatile video plugin for WordPress.', 'videographywp' );?></div>
	<div class="wp-badge cvwp-page-logo"><?php printf( __( 'Version %s', 'videographywp' ), CVWP_VERSION );?></div>
	<p class="cvwp-page-actions">
		<a href="<?php menu_page_url( 'cvwp-settings' );?>" class="button button-primary"><?php _e( 'Plugin settings', 'videographywp' );?></a>
		<a href="<?php echo cvwp_plugin_url( 'documents/getting-started/' );?>" target="_blank"><?php _e( 'Docs & Tuts', 'videographywp' );?></a>
	</p>
	
	<div class="feature-section two-col">
		<div class="col">
			<img src="<?php echo cvwp_get_uri( 'assets/back-end/images/promo/video-import.png' );?>" />
		</div>
		
		<div class="col">
			<h3><?php _e( 'The easiest way to add videos into posts', 'videographywp' ); ?></h3>
			<p><?php printf( __( 'Add %s videos into posts and choose whether you want to import title, description, featured image and video.', 'videographywp' ), cvwp_video_platforms_list() );?></p>
			<p><?php _e( 'Change embedding options right from the post editing screens to make your videos fit perfectly into your design.', 'videographywp' );?></p>
			<p><?php _e( "One plugin to handle all aspects related to your videos.", 'videographywp' ) ;?></p>
		</div>		
	</div>
	
	<div class="changelog feature-section three-col">
		<div class="col">
			<img src="//s.w.org/images/core/4.2/theme-switcher.png" />
			<h3><?php _e( 'Switch compatible themes', 'videographywp' ); ?></h3>
			<p><?php _e( "Switching between compatible themes won't require you to re-import videos. The plugin will know what goes where and your videos will look great.", 'videographywp' ); ?></p>
		</div>
		<div class="col">
			<img src="<?php echo cvwp_get_uri( 'assets/back-end/images/promo/post-embed.png' );?>" />
			<h3><?php _e( 'Fully control plugin embeds', 'videographywp' ); ?></h3>
			<p><?php _e( 'Change embedding options individually or set global settings that will apply to all imported videos with just a few clicks.', 'videographywp' ); ?></p>
		</div>
		<div class="col last-feature">
			<img src="<?php echo cvwp_get_uri( 'assets/back-end/images/promo/post-types.png' );?>" />
			<h3><?php _e( 'Enable on post types', 'videographywp' ); ?></h3>
			<p><?php _e( "Don't want videos into all your post types? Don't worry! You can select where to implement the plugin's functionality.", 'videographywp' ); ?></p>
		</div>
	</div>
	
	<div class="changelog under-the-hood feature-list">
		<h3><?php _e( 'Under the Hood' ); ?></h3>
	
		<div class="feature-section col two-col">
			<div>
				<h4><?php _e( 'Plenty of actions and filters', 'videographywp' ); ?></h4>
				<p><?php _e( "No default compatibility with your theme? No worry! It's easy to make your theme compatible by using just a few of the available filters.", 'videographywp' ); ?></p>
	
				<h4><?php _e( 'Options overriding', 'videographywp' ); ?></h4>
				<p><?php _e( 'Need to make some defaults permanent? Maybe video width or full screen option? You can.', 'videographywp' );?></p>
			</div>
			<div class="last-feature">
				<h4><?php _e( 'Need extra video sources?', 'videographywp' ); ?></h4>
				<p><?php _e( "While we can't guarantee that ALL video sharing platforms will work with the plugin, most likely, they will. Just let us know.", 'videographywp' );?></p>
	
				<h4><?php _e( 'Need some video info?', 'videographywp' ); ?></h4>
				<p><?php _e( 'Want to customize some of your WP theme video template files? We have template functions that will help you with that.', 'videographywp' );?></p>
			</div>
	
		<hr />
	
		<div class="return-to-dashboard">
			<a href="<?php menu_page_url( 'cvwp-settings' ); ?>"><?php _e( 'Plugin Settings', 'videographywp' );?></a>
		</div>
	</div>
	</div>
</div>