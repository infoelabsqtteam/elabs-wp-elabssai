<?php 
	// No direct access
if( !defined( 'ABSPATH' ) ){
	die();
}

/**
 * @var array $options	Post options.
 * @var array $api_keys	Plugin API keys option.
 */
?>
<div id="cvwp-video-query-container">	
	<div id="cvwp-video-query-messages"></div>
	<table class="form_table" width="100%">
		<tbody>
			<?php 
				if( !$options['video']['video_id'] || !$options['video']['source'] ):
			?>
			<?php if( !defined( 'CVWP_GET_BY_URL' ) || !CVWP_GET_BY_URL ):?>
			<tr>
				<th scope="row"><label for="cvwp_video_source_yt"><?php _e('Video source', 'videographywp');?>:</label></th>
				<td>
					<?php 
						cvwp_video_sources_checkboxes(array(
							'name' 		=> 'cvwp_video_source',
							'id' 		=> 'cvwp_video_source',
							'selected' 	=> false 
						));					
					?>				
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cvwp_video_id"><?php _e('Video ID', 'videographywp');?>:</label></th>
				<td>
					<input type="text" name="cvwp_video_id" id="cvwp_video_id" size="15" />
				</td>
			</tr>
			<?php else:?>
			<tr>
				<th scope="row"><label for="cvwp_video_url"><?php _e('Video URL', 'videographywp');?>*:</label></th>
				<td>
					<input type="text" name="cvwp_video_url" id="cvwp_video_url" size="17" />									
				</td>
			</tr>	
			<tr>
				<td colspan="2">
					<p class="description">*<?php printf( __( 'Allowed video sources: %s.', 'videographywp' ), cvwp_video_platforms_list() );?></p>
				</td>
			</tr>
			<?php endif;?>
			<?php 
				else:
			?>
			<tr>
				<td colspan="2">
					<p>
						<strong><?php _e('Video source', 'videographywp');?>:</strong> <?php echo ucfirst( $options['video']['source'] );?><br />
						<strong><?php _e('Video ID', 'videographywp')?>:</strong> <?php echo $options['video']['video_id'];?><br />
						<strong><?php _e('Duration', 'videographywp')?>:</strong> <?php echo cvwp_video_duration( $options['video']['duration'], __('unknown', 'videographywp') );?><br />
						<strong><?php _e('URL', 'videographywp')?>:</strong> <span class="cvwp-video-url-output"><a href="<?php cvwp_the_video_url( __('unknown', 'videographywp') )?>" target="_blank"><?php cvwp_the_video_url( __('unknown', 'videographywp') );?></a></span>
					</p>
					<input type="hidden" name="cvwp_video_source" id="cvwp_video_source" value="<?php echo $options['video']['source'];?>" />
					<input type="hidden" name="cvwp_video_id" id="cvwp_video_id" value="<?php echo $options['video']['video_id'];?>" />
					<a class="button" id="cvwp-remove-video"><?php _e('Remove attached video', 'videographywp');?></a>
				</td>
			</tr>				
			<?php 
				endif; // end checking if video already attached
			?>
			
			<tr>
				<td colspan="2">
					<hr />
					<input type="checkbox" name="cvwp_set_title" id="cvwp_set_title" value="1" autocomplete="off" />
					<label for="cvwp_set_title"><?php _e('Set video title as title', 'videographywp');?></label>
				</td>
			</tr>
			<?php if( !empty( $api_keys['youtube_key'] ) ) :?>
			<tr>
				<td colspan="2">
					<input type="checkbox" name="cvwp_set_content" id="cvwp_set_content" value="1" autocomplete="off" <?php if ( empty( $api_keys['youtube_key'] ) ) :?>disabled="disabled"<?php endif;?> />
					<label for="cvwp_set_content"><?php _e('Set video content as content', 'videographywp'); ?>
					</label>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td colspan="2">
					<input type="checkbox" name="cvwp_set_image" id="cvwp_set_image" value="1" autocomplete="off" />
					<label for="cvwp_set_image"><?php _e('Set image as featured image', 'videographywp');?></label>
				</td>
			</tr>
			<?php 
				if( !$options['video']['video_id'] || !$options['video']['source'] ):
			?>
			<tr>
				<td colspan="2">
					<input type="checkbox" name="cvwp_set_video" id="cvwp_set_video" value="1" autocomplete="off" />
					<label for="cvwp_set_video"><?php _e('Attach video to post', 'videographywp');?></label>
				</td>
			</tr>
			<?php endif;?>
			<tr>
				<td colspan="2">				
					<p>
						<input type="button" class="button secondary" name="video-query" value="<?php esc_attr_e( 'Query video', 'videographywp' );?>" id="cvwp-video-query-btn" />
					</p>
					<?php if ( empty( $api_keys['youtube_key'] ) ) :?>
						<p class="description">
							<span style="color: red;"><?php _e( 'Without an API key, the image will be low quality and the video description will not be retrieved.', 'videography' );?></span>
						</p>
					<?php endif;?>
				</td>
			</tr>		
		</tbody>
	</table>	
</div>	