<?php 
	// No direct access
if( !defined( 'ABSPATH' ) ){
	die();
}
?>
<div id="cvwp-video-settings" class="cvwp">
<?php 
	if( $options['video']['video_id'] && $options['video']['source'] ):
?>
	<?php if( cvwp_theme_embed_enabled() ):?>
		<p class="cvwp-note">
			<span class="description"><?php _e( 'Your theme is compatible with the plugin and you enabled the option to allow the theme to do all embedding. No settings here for now.', 'videographywp' );?></span>
			<a href="<?php menu_page_url( 'cvwp-settings' );?>" class="button"><?php _e( 'Plugin Settings', 'videographywp' );?></a>
		</p>
	<?php elseif ( !cvwp_is_embed_allowed() && !__cvwp_disallow_plugin_embeds() ):?>
		<p class="cvwp-warning">
			<span class="description"><?php _e( 'Please note that embedding is disallowed from plugin options.', 'videographywp' );?></span>
			<a href="<?php menu_page_url( 'cvwp-settings' );?>" class="button"><?php _e( 'Plugin Settings', 'videographywp' );?></a>
		</p>
	<?php else: // theme compatibility not enabled, show embedding video settings ?>
	
	<?php if( __cvwp_disallow_plugin_embeds() ):?>
		<p class="cvwp-warning">
			<span class="description"><?php _e( "Plugin embedding disallowed by plugin filter used in theme or other plugin. Unless embedding is done by theme, videos won't be visible.", 'videographywp' );?></span>
		</p>
	<?php endif;?>
		
	<input type="hidden" name="cvwp_post[video][source]" value="<?php echo $options['video']['source'];?>" />
	<input type="hidden" name="cvwp_post[video][video_id]" value="<?php echo $options['video']['video_id'];?>" />
	<input type="hidden" name="cvwp_post[video][duration]" value="<?php echo $options['video']['duration'];?>" />
	<?php wp_nonce_field( 'cvwp-save-post-video-options', 'cvwp_options_nonce' );?>
	<table class="form-table" id="cvwp-video-settings-table">
		<tbody>
			<?php if( !__cvwp_disallow_plugin_embeds() ):?>
			<tr>
				<th><label for="cvwp-embed-position"><?php _e( 'Embed position', 'videographywp' );?>:</label></th>
				<td>
					<?php if( cvwp_is_override_set( 'embed_position' ) ):?>						
						<span class="description no-config">
							<?php printf( __( '<strong>Not configurable.</strong> Option can be changed globally from plugin %sSettings%s page.', 'videographywp' ), '<a href="' . menu_page_url( 'cvwp-settings', false ) . '">', '</a>' );?>
						</span>					
					<?php else:?>
						<?php 
							cvwp_select_embed_position(array(
								'name' 		=> 'cvwp_post[embed_position]',
								'id' 		=> 'cvwp-embed-position',
								'selected' 	=> $options['embed_position']
							));
						?>
						<span class="description"><?php _e( 'Choose where to embed the video into the post content.', 'videographywp' );?></span>
					<?php endif;?>
					
					
					<?php $cls = 'shortcode' == $options['embed_position'] ? '' : 'hide-if-js ';?>
					<p class="<?php echo $cls;?>cvwp-note" id="cvwp-embed-position-notice">
						<span class="description"><?php _e( 'To place video into post content, simply put shortcode <code>[cvwp_video_position]</code> where you want the video to be embedded.', 'videographywp' );?></span>
					</p>
					
					<?php $cls = 'button' == $options['embed_position'] ? '' : 'hide-if-js ';?>
					<p class="<?php echo $cls;?>cvwp-note" id="cvwp-embed-button-notice">
						<span class="description"><?php _e( 'To place button into post content use shortcode <code>[cvwp_video_button text="Play video" title="Play video" class="my-css-class"]</code> where you want the button to display.', 'videographywp' );?></span>
					</p>					
					
					<?php 
						$cls = 'no_embed' == $options['embed_position'] ? '' : 'hide-if-js ';
						$noembed_cls = 'no_embed' == $options['embed_position'] ? 'hide-if-js ' : '';
					?>
					<p class="<?php echo $cls;?>cvwp-warning" id="cvwp-no-embed-notice">
						<span class="description"><?php _e( '<strong>Please note</strong>: Video won\'t be embedded by the plugin.', 'videographywp' );?></span>
					</p>
					
					<?php $cls = 'featured_image' == $options['embed_position'] ? '' : 'hide-if-js ';?>
					<p class="<?php echo $cls;?>cvwp-note" id="cvwp-featured-image-position-notice">
						<span class="description"><?php _e( "It's essential to have a featured image selected for this post; without it, the video cannot be embedded.", 'videographywp' );?></span>
					</p>
					
				</td>
			</tr>
			<?php endif; // if( !__cvwp_disallow_plugin_embeds() )?>
			
			<?php 
				$has_lazy = apply_filters( 'cvwp_show_lazy_embed_settings', true );
				if( $has_lazy ):
			?>
			<tr class="<?php echo $noembed_cls;?>cvwp-no-embed-hide">
				<th><label for="cvwp-lazy-load"><?php _e('Lazy load', 'videographywp');?></label>:</th>
				<td>
					<?php if( cvwp_is_override_set( 'lazy_load' ) ):?>
						<span class="description no-config">
							<?php printf( __( '<strong>Not configurable.</strong> Option can be changed globally from plugin %sSettings%s page.', 'videographywp' ), '<a href="' . menu_page_url( 'cvwp-settings', false ) . '">', '</a>' );?>
						</span>
					<?php else:?>
						<input type="checkbox" name="cvwp_post[lazy_load]" value="1" id="cvwp-lazy-load"<?php cvwp_check( (bool) $options['lazy_load'] );?> />
						<span class="description"><?php _e('when checked, will improve page load time by displaying images that can be clicked to embed video', 'videographywp');?></span>
					<?php endif;?>
				</td>
			</tr>
			<?php else:?>
				<?php cvwp_plugin_message( "Lazy load option disabled by filter 'cvwp_show_lazy_embed_settings'.", true );?>
			<?php endif; // if( $has_lazy ) ?>
			
			
			<?php 
				$has_size = apply_filters( 'cvwp_show_embed_size_settings' , true );
				if( $has_size ):
			?>
			<!-- Video player options -->	
			<tr class="<?php echo $noembed_cls;?>cvwp-no-embed-hide">
				<th><label for="cvwp-width"><?php _e('Player size', 'videographywp');?>:</label></th>
				<td class="cvwp-player-aspect-options">
					<label for="cvwp-video-aspect"><?php _e('Aspect ratio', 'videographywp')?>:</label>
					<?php 
						cvwp_select_aspect_ratio(array(
							'name' => 'cvwp_post[video][aspect]',
							'id' => 'cvwp-video-aspect',
							'selected' => $options['video']['aspect']
						));
					?>
					<?php 
						/**
						 * Filter video embed width to allow developer to specify his own width 
						 * in case the theme uses some fixed sizes.
						 * 
						 * @var $width - the width set by the user
						 * @var $options - all video options set on post
						 */
						$width = apply_filters( 'cvwp_embed_width' , $options['video']['width'], $options );
						// store disabled attribute in this variable in certain cases
						$disabled = '';
						
						// check for overrides
						if( cvwp_is_override_set( 'video_width' ) && !has_filter( 'cvwp_embed_width' ) ){
							$width = cvwp_get_override_value( 'video_width' );
							$disabled = ' disabled="disabled"';
						}else if( has_filter( 'cvwp_embed_width' ) ){
							$disabled = ' disabled="disabled"';						
						}						
					?>
					<label for="cvwp-width"><?php _e('Width', 'videographywp');?>:</label> <input class="cvwp_video_width small-text" size="2" type="number" step="5" min="0" id="cvwp-width" name="cvwp_post[video][width]" value="<?php echo $width;?>"<?php echo $disabled;?> /> px |
					<?php _e('Height', 'videographywp');?>: <span class="cvwp_video_height"><?php echo cvwp_player_height( $options['video']['aspect'], $width );?></span> px
					<?php if( cvwp_is_override_set( 'video_width' ) && !has_filter('cvwp_embed_width') ):?>
						<p class="description no-config">
							<?php printf( __( '<strong>Width is not configurable.</strong> Option can be changed globally from plugin %sSettings%s page.', 'videographywp' ), '<a href="' . menu_page_url( 'cvwp-settings', false ) . '">', '</a>' );?>
						</p>
					<?php elseif( has_filter('cvwp_embed_width') ):?>
						<p class="description">
							<?php _e( 'Width is not configurable. Value is set by plugin filter in either theme or other plugin.', 'videographywp' );?>
						</p>
					<?php endif;?>	
				</td>			
			</tr>
			<?php else:?>
				<?php cvwp_plugin_message( "Embed size disabled by filter 'cvwp_show_embed_size_settings'.", true );?>
			<?php endif;?>
			
			<?php 
				$has_volume = apply_filters( 'cvwp_show_volume_settings' , true );
				if( $has_volume ):
			?>
			<tr class="<?php echo $noembed_cls;?>cvwp-no-embed-hide">
				<th><label for="cvwp-volume"><?php _e( 'Volume', 'videographywp' );?>:</label></th>
				<td>
					<?php if( cvwp_is_override_set( 'video_volume' ) ):?>
						<span class="description no-config">
							<?php printf( __( '<strong>Not configurable.</strong> Option can be changed globally from plugin %sSettings%s page.', 'videographywp' ), '<a href="' . menu_page_url( 'cvwp-settings', false ) . '">', '</a>' );?>
						</span>
					<?php else:?>
						<input class="small-text" type="number" step="5" min="0" value="<?php echo $options['video']['volume'];?>" name="cvwp_post[video][volume]" id="cvwp-volume" size="2" />
						<span class="description"><?php _e('playback volume (between 0 and 100)', 'videographywp');?></span>
					<?php endif;?>	
				</td>
			</tr>
			<?php else:?>
				<?php cvwp_plugin_message( "Volume settings disabled by filter 'cvwp_show_volume_settings'." , true);?>
			<?php endif;?>
			
			<?php 
				$has_fullscreen = apply_filters( 'cvwp_show_fullscreen_settings' , true );
				if( $has_fullscreen ):
			?>
			<tr class="<?php echo $noembed_cls;?>cvwp-no-embed-hide">
				<th><label for="cvwp-fullscreen"><?php _e( 'Allow full screen', 'videographywp' );?>:</label></th>
				<td>
					<?php if( cvwp_is_override_set( 'video_fs' ) ):?>
						<span class="description no-config">
							<?php printf( __( '<strong>Not configurable.</strong> Option can be changed globally from plugin %sSettings%s page.', 'videographywp' ), '<a href="' . menu_page_url( 'cvwp-settings', false ) . '">', '</a>' );?>
						</span>
					<?php else:?>
						<input type="checkbox" value="1" name="cvwp_post[video][fullscreen]" id="cvwp-fullscreen"<?php cvwp_check( (bool) $options['video']['fullscreen'] );?> />
						<span class="description"><?php _e('allow the player to go fullscreen', 'videographywp');?></span>
					<?php endif;?>
				</td>
			</tr>
			<?php else:?>
				<?php cvwp_plugin_message( "Fullscreen settings disabled by filter 'cvwp_show_fullscreen_settings'." , true);?>
			<?php endif;?>

            <?php
				$has_loop = apply_filters( 'cvwp_show_loop_settings' , true );
				if( $has_loop ):
			?>
			<tr class="<?php echo $noembed_cls;?>cvwp-no-embed-hide">
				<th><label for="cvwp-loop"><?php _e( 'Allow video loop', 'cvwp' );?>:</label></th>
				<td>
					<?php if( cvwp_is_override_set( 'video_loop' ) ):?>
						<span class="description no-config">
							<?php printf( __( '<strong>Not configurable.</strong> Option can be changed globally from plugin %sSettings%s page.', 'cvwp' ), '<a href="' . menu_page_url( 'cvwp-settings', false ) . '">', '</a>' );?>
						</span>
					<?php else:?>
						<input type="checkbox" value="1" name="cvwp_post[video][loop]" id="cvwp-fullscreen"<?php cvwp_check( (bool) $options['video']['loop'] );?> />
						<span class="description"><?php _e('allow the player to loop the video', 'cvwp');?></span>
					<?php endif;?>
				</td>
			</tr>
			<?php else:?>
				<?php cvwp_plugin_message( "Loop settings disabled by filter 'cvwp_show_loop_settings'." , true);?>
			<?php endif;?>

		</tbody>
	</table>
	<?php endif; // endif theme compatibility enabled?>
	
	<div class="<?php echo $noembed_cls;?>cvwp-no-embed-hide">
	<?php if( !cvwp_theme_embed_enabled() && ( cvwp_is_embed_allowed() || __cvwp_disallow_plugin_embeds() ) ):?>	
		<!-- YouTube settings -->
		<div id="cvwp-youtube">
			<h4><?php _e('YouTube settings', 'videographywp');?></h4>
			<table class="form_table">
				<tbody>
					<tr>
						<th><label for="cvwp-nocookie"><?php _e( 'No cookies video embed', 'cvwp' );?>:</label></th>
						<td>
							<input type="checkbox" value="1" name="cvwp_post[video][nocookie]" id="cvwp-nocookie"<?php cvwp_check( (bool) $options['video']['nocookie'] );?> />
							<span class="description"><?php _e('embed video from cookieless domain', 'cvwp');?></span>
						</td>
					</tr>
                	<tr>
						<th><label for="cvwp-controls"><?php _e( 'Controls', 'videographywp' );?>:</label></th>
						<td>
							<input type="checkbox" value="1" name="cvwp_post[video][controls]" id="cvwp-controls"<?php cvwp_check( (bool) $options['video']['controls'] );?> />
							<span class="description"><?php _e('show video controls in player', 'videographywp');?></span>
						</td>
					</tr>
					<tr>
						<th><label for="cvwp-autohide"><?php _e( 'Auto hide controls', 'videographywp' );?>:</label></th>
						<td>
							<input type="checkbox" value="1" name="cvwp_post[video][autohide]" id="cvwp-autohide"<?php cvwp_check( (bool) $options['video']['autohide'] );?> />
							<span class="description"><?php _e('hide controls when video is playing', 'videographywp');?></span>
						</td>
					</tr>
					<tr>
						<th><label for="cvwp-annotations"><?php _e( 'Hide annotations', 'videographywp' );?>:</label></th>
						<td>
							<input type="checkbox" value="1" name="cvwp_post[video][iv_load_policy]" id="cvwp-annotations"<?php checked( (int) $options['video']['iv_load_policy'], 3 );?> />
							<span class="description"><?php _e('hide annotations placed in videos', 'videographywp');?></span>
						</td>
					</tr>
					<tr>
						<th><label for="cvwp-modestbranding"><?php _e( 'Hide YouTube logo', 'videographywp' );?>:</label></th>
						<td>
							<input type="checkbox" value="1" name="cvwp_post[video][modestbranding]" id="cvwp-modestbranding"<?php cvwp_check( (bool) $options['video']['modestbranding'] );?> />
							<span class="description"><?php _e('when checked will show logo on video', 'videographywp');?></span>
						</td>
					</tr>
				</tbody>
			</table>
			<p><a class="button" href="#" id="cvwp-update-player"><?php _e('Preview YouTube video changes', 'videographywp')?></a></p>
		</div><!-- #cvwp-youtube -->
	<?php endif; //endif theme embedding check ?>	
		<!-- Live video embed -->
		<h4><?php _e('Video', 'videographywp');?></h4>
		<div id="cvwp-video-output">
			<?php 
				cvwp_video_output( false, false, false );
			?>		
		</div>
	</div><!-- .cvwp-no-embed-hide -->	
<?php else:?>
<?php _e( 'No video attached. Query and attach videos from video query panel.', 'videographywp' );?>	
<?php 
	endif;// end video checking
?>
</div><!-- #cvwp-video-settings -->