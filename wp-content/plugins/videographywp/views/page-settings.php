<?php 
	// No direct access
if( !defined( 'ABSPATH' ) ){
	die();
}
?>
<div class="wrap">
	<h2><?php _e( 'VideographyWP - plugin settings', 'videographywp' );?></h2>
	<form method="post" action="">
		<?php wp_nonce_field( 'cvwp-save-plugin-options', 'cvwp-nonce' );?>
		<div id="cvwp-tabs" class="cvwp-tabs" data-storage_var="cvwp_settings_tab">
			<ul class="cvwp-tab-labels">
				<li><a href="#cvwp-embed"><?php _e( 'Embed options', 'videographywp' );?></a></li>
				<li><a href="#cvwp-settings"><?php _e( 'Settings', 'videographywp' );?></a></li>
				<li><a href="#cvwp-apis"><?php _e( 'APIs', 'videographywp' );?></a></li>
				<li><a href="#cvwp-help"><?php _e( 'Info & Help', 'videographywp' );?></a></li>
			<?php foreach( $extra_tabs as $tab_id => $hook ):?>
				<li><a href="#<?php echo $tab_id;?>"><?php echo $hook['title'];?></a></li>
			<?php endforeach;?>
			</ul>
			
			<!-- Embed options -->
			<div id="cvwp-embed" class="cvwp-panel hide-if-js">
				<h4><i class="dashicons dashicons-video-alt3"></i> <?php _e( 'Automatic embed', 'videographywp' );?></h4>
				<table class="form-table">
					<tbody>
						<?php if( cvwp_theme_is_compatible() ):?>
						<tr>
							<th><label for="embed_for_theme"><?php _e( 'Allow theme to handle all embedding', 'videographywp' );?>:</label></th>
							<td class="toggler">
								<span class="description"><?php printf( __( 'Option available only in %s', 'videographywp' ), '<a href="' . cvwp_plugin_url() . '"><strong>VideographyWP<sup>PRO</sup></strong></a>' );?></span>
							</td>
						</tr>
						<?php endif;?>	
					
						<tr class="toggle plugin_embed">
							<th><label for="plugin_embedding"><?php _e( 'Allow embedding', 'videographywp' );?>:</label></th>
							<td class="toggler">
								<?php if( __cvwp_disallow_plugin_embeds() ):?>
									<span class="cvwp-warning"><?php _e( 'Automatic plugin embedding disallowed by plugin filter used in theme or other plugin.', 'videographywp' );?></span><br />
									<span class="description">
										<?php _e( "To allow automatic plugin embedding make sure that your theme doesn't set filter <code>cvwp_disallow_plugin_embeds</code> to return true.", 'videographywp' );?>
									</span>
								<?php else:?>
									<?php cvwp_settings_checkbox( 'plugin_embedding', 'settings', array( 'data-selector' => '.toggle.single_embed' ) );?>
									<span class="description"><?php _e( "When checked, the plugin will automatically embed videos into posts." , 'videographywp' );?></span>
									<span class="description"><?php _e( "Uncheck this option if you plan to do the embedding from your theme/child theme." , 'videographywp' );?></span>
								<?php endif;?>
							</td>
						</tr>
												
						<tr class="toggle plugin_embed single_embed">
							<th><label for="single_post_embedding"><?php _e( 'Embed only on single post pages', 'videographywp' );?>:</label></th>
							<td>
								<?php if( __cvwp_disallow_plugin_embeds() ):?>
									<span class="cvwp-warning"><?php _e( 'Automatic plugin embedding disallowed by plugin filter used in theme or other plugin.', 'videographywp' );?></span><br />
								<?php else:?>
									<?php cvwp_settings_checkbox( 'single_post_embedding' );?>
									<span class="description"><?php _e( "When checked, the plugin will automatically embed videos only when viewed on single page." , 'videographywp' );?></span>
									<span class="description"><?php _e( "Uncheck this option if you want to embed on archive pages too." , 'videographywp' );?></span>
									<?php if( $this->is_elementor_pro() ):?>
                                        <p class="cvwp-notice">
											<?php _e( "Please note, if using Elementor PRO post archive templates and want to embed videos in archive pages, this option won't work.", 'cvwp' );?>
                                        </p>
									<?php endif;?>
								<?php endif;?>
							</td>
						</tr>						
											
					</tbody>
				</table>
				
				<h4><i class="dashicons dashicons-admin-generic"></i><?php _e( 'Global embedding options', 'videographywp' );?></h4>
				<p class="description">
					<?php _e( 'By enabling these global embedding options, you will apply the same embed settings to all posts created by the plugin.<br />This is useful if you need videos to be embedded exactly the same everywhere in your website.', 'videographywp' );?>
				</p>
				<table class="form-table" id="global_options">
					<tbody>
						<!-- Embed position -->
						<tr>
							<th><label for="set_embed_position"><?php _e( 'Override embed position', 'videographywp' );?>:</label></th>
							<td class="toggler">
								<span class="description"><?php printf( __( 'Option available only in %s', 'videographywp' ), '<a href="' . cvwp_plugin_url() . '"><strong>VideographyWP<sup>PRO</sup></strong></a>' );?></span>								
							</td>
						</tr>
												
						<!-- Lazy load -->
						<tr>
							<th><label for="set_lazy_load"><?php _e( 'Override lazy load', 'videographywp' );?>:</label></th>
							<td class="toggler">
								<span class="description"><?php printf( __( 'Option available only in %s', 'videographywp' ), '<a href="' . cvwp_plugin_url() . '"><strong>VideographyWP<sup>PRO</sup></strong></a>' );?></span>
							</td>
						</tr>
						
						<!-- Video width -->
						<tr>
							<th><label for="set_video_width"><?php _e( 'Override video width', 'videographywp' );?>:</label></th>
							<td class="toggler">
								<span class="description"><?php printf( __( 'Option available only in %s', 'videographywp' ), '<a href="' . cvwp_plugin_url() . '"><strong>VideographyWP<sup>PRO</sup></strong></a>' );?></span>
							</td>
						</tr>
						
						<!-- Video volume -->
						<tr>
							<th><label for="set_video_volume"><?php _e( 'Override video volume', 'videographywp' );?>:</label></th>
							<td class="toggler">
								<span class="description"><?php printf( __( 'Option available only in %s', 'videographywp' ), '<a href="' . cvwp_plugin_url() . '"><strong>VideographyWP<sup>PRO</sup></strong></a>' );?></span>
							</td>
						</tr>
						
						<!-- Video fullscreen -->
						<tr>
							<th><label for="set_video_fs"><?php _e( 'Override video fullscreen', 'videographywp' );?>:</label></th>
							<td class="toggler">
								<span class="description"><?php printf( __( 'Option available only in %s', 'videographywp' ), '<a href="' . cvwp_plugin_url() . '"><strong>VideographyWP<sup>PRO</sup></strong></a>' );?></span>
							</td>
						</tr>

                        <!-- Video loop -->
						<tr>
							<th><label for="set_video_fs"><?php _e( 'Override video loop', 'videographywp' );?>:</label></th>
							<td class="toggler">
								<span class="description"><?php printf( __( 'Option available only in %s', 'videographywp' ), '<a href="' . cvwp_plugin_url() . '"><strong>VideographyWP<sup>PRO</sup></strong></a>' );?></span>
							</td>
						</tr>
					</tbody>
				</table>	
				
				<?php submit_button( __( 'Save settings', 'videographywp' ) );?>
			</div>
			
			<!-- Settings -->
			<div id="cvwp-settings" class="cvwp-panel hide-if-js">
				<h4><i class="dashicons dashicons-admin-generic"></i> <?php _e( 'Maintenance settings', 'videographywp' );?></h4>
				<table class="form-table">
					<tbody>
                        <tr>
                            <th><label for=""><?php _e( 'Allow for post type', 'cvwp' );?>:</label></th>
	                        <?php
	                        $post_checkboxes = cvwp_post_types_checkboxes(array(
		                        'name' => 'post_types',
		                        'echo' => false,
		                        'selected' => $options['post_types']
	                        ));
	                        ?>
                            <td>
                                <?php echo $post_checkboxes;?>
                                <?php if( post_type_exists('product') ): ?>
                                    <input type="checkbox" disabled="disabled" /> <span class="description"><?php printf( __( 'WooCommerce products available only in %s', 'videographywp' ), '<a href="' . cvwp_plugin_url() . '"><strong>VideographyWP<sup>PRO</sup></strong></a>' );?></span>
                                <?php endif;?>
                            </td>
                        </tr>
                        <tr>
							<th><label for="complete_uninstall"><?php _e('Complete uninstall', 'videographywp');?>:</label></th>
							<td>
								<?php cvwp_settings_checkbox( 'complete_uninstall' );?>
								<span class="description"><?php _e( 'If checked, when uninstalling the plugin you can remove it completely, including all custom fields created by the plugin and all other data.', 'videographywp' );?></span>
							</td>
						</tr>	
						<tr>
							<th><label for="show_warnings"><?php _e('Show warnings', 'videographywp');?>:</label></th>
							<td>
								<?php cvwp_settings_checkbox( 'show_warnings' );?>
								<span class="description"><?php _e( 'If checked, will display warnings under certain conditions alerting users to enable settings or review options.', 'videographywp' );?></span>
							</td>
						</tr>
											
					</tbody>
				</table>
				<?php submit_button( __( 'Save settings', 'videographywp' ) );?>
			</div>
			
			<!-- Plugin APIs -->
			<div id="cvwp-apis" class="cvwp-panel hide-if-js">
				<h4><i class="dashicons dashicons-admin-network"></i> <?php _e('APIs credentials', 'videographywp');?></h4>
				<table class="form-table">
					<tbody>
						<tr>
							<td colspan="2">
								<p class="description">
									<?php _e( 'Before being able to import YouTube videos, you must enter your YouTube API server key.', 'videographywp' );?><br />
									<?php printf( __( 'More details about how you can get your API key can be found %shere%s.' , 'videographywp' ), '<a href="' . cvwp_plugin_url( 'documentation/getting-started/licence-api-keys/' ) . '" target="_blank">', '</a>') ;?>
								</p>
							</td>
						</tr>
						<tr>
							<th><label for="apis-youtube-key"><?php _e( 'YouTube server key', 'videographywp' );?>:</label></th>
							<td>
                                <input type="text" name="apis_youtube_key" id="apis-youtube-key" value="<?php echo esc_attr( $apis['youtube_key'] );?>" autocomplete="off" size="60" />
								<a class="button" href="#" id="cvwp-test-yt-key" ><?php esc_attr_e( 'Test YouTube key', 'videographywp' );?></a>
                                <div id="cvwp-test-yt-key-response" class="idle"></div>
								<p class="description">
									<a class="cvwp-video-button"
									   href="#"
									   data-source="youtube"
									   data-video_id="6xye7Fddttk"
									   data-width="900"
									   data-aspect="16x9"
									   data-volume="55"
									   data-ssl="0"
									   data-controls="1"
									   data-lazy_load="0">
										<?php _e( 'Show me how to set YouTube key', 'videographywp' );?>
									</a>
								</p>
                            </td>
						</tr>
					</tbody>
				</table>
				<?php submit_button( __('Save settings', 'videographywp') );?>
			</div>
			
			<!-- Info & Help -->
			<div id="cvwp-help" class="cvwp-panel hide-if-js">
				<h4><i class="dashicons dashicons-admin-tools"></i> <?php _e( 'WP theme compatibility', 'videographywp' );?></h4>
				<p class="description cvwp-notice">				
					<?php _e( 'All video embedding is handled by the plugin.', 'videographywp'  );?>
					<?php printf( __( 'WordPress theme compatibility if available only in %s.', 'videographywp' ), '<strong><a href="' . cvwp_plugin_url() . '">VideographyWP<sup>PRO</sup></a></strong>');?><br />
				</p>
				
				<p>
					<?php printf( __('The plugin can import videos from the following platforms: %s.'), cvwp_video_platforms_list() );?><br />
					<?php printf( __('Except for YouTube, %1$s can import from the following plaforms: %2$s.'), '<strong><a href="' . cvwp_plugin_url() . '">VideographyWP<sup>PRO</sup></a></strong>', cvwp_pro_video_platforms_list() );?>
				</p>
				
				<p>
					<?php printf( __('Also, %1$s is compatible by default with all the following premium and free WordPress themes: %2$s.', 'videographywp'), '<strong><a href="' . cvwp_plugin_url() . '">VideographyWP<sup>PRO</sup></a></strong>', cvwp_compatible_themes_list() );?>
				</p>
				
				<h4><i class="dashicons dashicons-welcome-learn-more"></i> <?php _e( 'Additional resources', 'videographywp' );?></h4>
				<p><?php _e( 'Below you will find links to documentation, plugin usage and tutorials.', 'videographywp' );?></p>
				
				<ul>
					<li><a target="_blank" href="<?php echo cvwp_plugin_url( 'documentation/getting-started/plugin-settings/' );?>"><?php _e( 'Plugin settings explained', 'videographywp' );?></a></li>
					<li><a target="_blank" href="<?php echo cvwp_plugin_url( 'documentation/getting-started/licence-api-keys/' );?>"><?php _e( 'How to get licence key and YouTube API key', 'videographywp' );?></a></li>
					<li><a target="_blank" href="<?php echo cvwp_plugin_url( 'documentation/getting-started/importing-videos/' );?>"><?php _e( 'How to import videos with VideographyWP', 'videographywp' );?></a></li>
					<li><a target="_blank" href="<?php echo cvwp_plugin_url( 'documentation/basic-tutorials/how-to-make-all-video-embeds-look-the-same/' );?>"><?php _e( 'How embed override options work', 'videographywp' );?></a></li>
					<li><a target="_blank" href="<?php echo cvwp_plugin_url( 'documentation/intermediate-tutorials/wordpress-theme-compatibility/' );?>"><?php _e( 'How to make my video theme compatible with VideographyWP', 'videographywp' );?></a></li>
					<li><a target="_blank" href="<?php echo cvwp_plugin_url( 'documentation/advanced-tutorials/integrate-videographywp-with-my-theme/' );?>"><?php _e( 'How to use VideographyWP in your WordPress theme', 'videographywp' );?></a></li>
				</ul>				
			</div>

            <?php foreach( $extra_tabs as $tab_id => $hook ):?>
                <div id="<?php echo $tab_id;?>" class="cvwp-panel hide-if-js">
                    <?php call_user_func( $hook['callback'] );?>
                </div>
			<?php endforeach;?>

		</div>
		
		<!--
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for=""></label></th>
					<td></td>
				</tr>
			</tbody>
		</table>
		-->
	</form>
</div>