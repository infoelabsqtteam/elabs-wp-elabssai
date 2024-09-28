/**
 * 
 */
;(function($){
	
	$(window).on( 'load', function(){
		// embed videos
		$('.cvwp-video-player').cvwp_video({
			onLoad: function(){
				customizeVideo( this );
			}			
		});
	});
	
	$(document).ready( function(){
		videoQuery();
		removeVideo();
		aspectRatio();
		embedPosition();
	});
	
	var embedPosition = function(){
		$('#cvwp-embed-position').on( 'change', function(){
			if( 'shortcode' == $(this).val() ){
				$('#cvwp-embed-position-notice').show();
			}else{
				$('#cvwp-embed-position-notice').hide();
			}
			
			if( 'button' == $(this).val() ){
				$('#cvwp-embed-button-notice').show();
			}else{
				$('#cvwp-embed-button-notice').hide();
			}

			if( 'featured_image' == $(this).val() ){
				$('#cvwp-featured-image-position-notice').show();
			}else{
				$('#cvwp-featured-image-position-notice').hide();
			}

			if( 'no_embed' == $(this).val() ){
				$('#cvwp-no-embed-notice').show();
				$('.cvwp-no-embed-hide').hide();
			}else{
				$('#cvwp-no-embed-notice').hide();
				$('.cvwp-no-embed-hide').show();
			}
			
		})
		
	}
	
	/**
	 * Detach a video from a given post
	 */
	var removeVideo = function(){
		
		var messages = cvwp_query.messages,
			querying = false;
				
		$('#cvwp-remove-video').on( 'click', function(e){
			e.preventDefault;
			// a query is already running, bail out
			if( querying ){
				setMessage( messages.querying_video, 'cvwp-loading' );
				return;
			}
			
			querying = true;			
			setMessage( messages.removing_video, 'cvwp-loading' );
			
			// set request data
			var data = {
				'action' 		: cvwp_query.remove_video.action,
				'post_id'		: $('#post_ID').val()				
			};
			// set nonce
			data[ cvwp_query.remove_video.nonce.name ] = cvwp_query.remove_video.nonce.value;
			// make AJAX request
			$.ajax({
				'url' 		: ajaxurl,
				'data' 		: data,
				'dataType' 	: 'json',
				'type' 		: 'POST',
				'success' : function( json ){
					querying = false;
					resetMessages();					
					if( !json.success ){
						setMessage( json.data, 'cvwp-error' );
						return;
					}
					
					$('#cvwp-video-settings').empty().html( messages.video_removed_message );
					$('#cvwp-video-query-container').empty().html( json.data.video_query );
					
				},
				'error': function(){
					querying = false;
					setMessage( messages.query_error, 'cvwp-error' );
				}
			});			
		})		
	}// removeVideo
	
	// video query functionality
	var videoQuery = function(){
		
		var messages	= cvwp_query.messages,
			selectors 	= cvwp_query.selectors,
			querying 	= false;
		
		$(document.body).on( 'click' , '#cvwp-video-query-btn', function(e){
			e.preventDefault();
			// a query is already running, bail out
			if( querying ){
				setMessage( messages.querying_video, 'cvwp-loading' );
				return;
			}
			
			var s = $('#cvwp-video-query input[name=cvwp_video_source]:checked').val() || $('#cvwp_video_source').val(),
				i = $('#cvwp-video-query input[name=cvwp_video_id]').val();
			// reset messages on form submit
			resetMessages();
			
			var url_field = $('#cvwp-video-query input[name=cvwp_video_url]');
			if( url_field.length == 0 ){			
				// error message, fields empty
				if( '' == s || '' == i ){
					setMessage( messages.empty_video_query, 'cvwp-error' );
					return;
				}
			}else{
				if( $(url_field).val() == '' ){
					setMessage( messages.empty_video_url, 'cvwp-error' );
					return;
				}
			}	

			querying = true;			
			setMessage( messages.loading_video, 'cvwp-loading' );
			
			var data = {
				'action' 		: cvwp_query.add_video.action,
				'video_source' 	: s,
				'video_id'		: i,
				'video_url'		: $(url_field).val(),
				'post_id'		: $('#post_ID').val(),
				'set_thumbnail' : $('#cvwp_set_image').is(':checked') ? 1 : 0,
				'set_video'		: $('#cvwp_set_video').is(':checked') ? 1 : 0,
			};
			data[ cvwp_query.add_video.nonce.name ] = cvwp_query.add_video.nonce.value;
			
			$.ajax({
				'url' 		: ajaxurl,
				'data' 		: data,
				'dataType' 	: 'json',
				'type' 		: 'POST',
				'success' 	: function( json ){
					querying = false;
					resetMessages();					
					if( !json.success ){
						setMessage( json.data, 'cvwp-error' );
						return;
					}
					
					// set title
					if( $('#cvwp_set_title').is(':checked') ){
						if( is_gutenberg() /*typeof wp.data != 'undefined'*/ ){
							// update Gutenberg editor title content/block
							wp.data.dispatch( 'core/editor' ).editPost( { title: json.data.video.title } );
						}else{
							// pre 5.0 post title setup
							$('#title-prompt-text').addClass('screen-reader-text');
							$('#title[type=text]').val( json.data.video.title );
						}
					}

					// set content
					if( $('#cvwp_set_content').is(':checked') ){												
						// clear editor contents
						cvwp_clear_editor( json.data.video.description );
					}

					// set the featured image
					if( $('#cvwp_set_image').is(':checked') ){
						if( json.data.thumbnail ){
							if( is_gutenberg() /*typeof wp.data != 'undefined'*/ ){
								// update Gutenberg featured image component
								wp.data.dispatch( 'core/editor' ).editPost( { featured_media: json.data.attachment_id } );
							}else{
								WPSetThumbnailHTML( json.data.thumbnail );
							}
						}						
					}
					
					// display the video settings if successfull
					if( $('#cvwp_set_video').is(':checked') ){
						if( json.data.video_settings ){
							$('#cvwp-video-settings').empty().html( json.data.video_settings );
							$('.cvwp-video-player').cvwp_video({
								onLoad : function( state ){
									customizeVideo( this );									
								}			
							});
						}						
						if( json.data.video_query ){
							$('#cvwp-video-query-container').empty().html( json.data.video_query );
						}
						removeVideo();
					}
					
					// Theme compatibility selectors update
					if( selectors ){
						$.each( selectors, function( key, selector ){
							if( 'static' == key ){
								$.each( selector, function( id, val ){									
									var tag = $( id ).prop('tagName').toLowerCase();									
									switch( tag ){
										case 'select':
											$(id + ' option[value=' + val + ']').attr('selected', 'selected');
											$(id).trigger('change');
										break;
										case 'input':
											var type = $(id).attr('type').toLowerCase();
											switch( type ){
												case 'checkbox':
												case 'radio':	
													$(id).attr('checked', true);
												break;	
											}
											
										break;	
									}
								})
							}else{							
								if( typeof json.data.video[ key ] !== 'undefined' ){
									var val = json.data.video[ key ];
									$( selector ).val( val );
								}
							}	
						})					
					}
					
				},
				'error': function(){
					querying = false;
					setMessage( messages.query_error, 'cvwp-error' );
				}
			});			
		})				
	};	
	
	/**
	 * Set a message and add a CSS class to messages div
	 */
	var setMessage = function( message, addClass ){
		$('#cvwp-video-query-messages')
			.empty()
			.attr({'class' : addClass + ' has-message'})
			.html( message );
	}
	
	/**
	 * Reset the messages box
	 */
	var resetMessages = function(){
		$('#cvwp-video-query-messages')
			.empty()
			.removeAttr('class');			
	}
	
	var customizeVideo = function( ref ){
		var update 	= $('#cvwp-update-player'),
			data 	= $('.cvwp-video-player').data();
		
		$(update).on( 'click', function(e){
			e.preventDefault();
			
			if( 'vimeo' == data.source ){			
				var title 		= $('#cvwp-title').is(':checked') ? 1 : 0,
					byline 		= $('#cvwp-byline').is(':checked') ? 1 : 0,
					portrait 	= $('#cvwp-portrait').is(':checked') ? 1 : 0,
					color 		= $('#cvwp-color').val().replace('#', '');
				
				$(ref).data( 'title', title );
				$(ref).data( 'byline', byline );
				$(ref).data( 'portrait', portrait );
				$(ref).data( 'color', color );
				
				$(ref).empty();
				$(ref).cvwp_video();
			}
			
			if( 'youtube' == data.source ){
				var controls = $('#cvwp-controls').is(':checked') ? 1 : 0,
					autohide = $('#cvwp-autohide').is(':checked') ? 1 : 0,
					iv_load_policy = $('#cvwp-annotations').is(':checked') ? 3 : 1,
					modestbranding = $('#cvwp-modestbranding').is(':checked') ? 1 : 0;
				
				$(ref).data( 'controls', controls );
				$(ref).data( 'autohide', autohide );
				$(ref).data( 'iv_load_policy', iv_load_policy );
				$(ref).data( 'modestbranding', modestbranding );
				
				$(ref).empty();
				$(ref).cvwp_video();				
			}
			
			if( 'dailymotion' == data.source ){
				var logo 	= $('#cvwp-dm_logo').is(':checked') ? 1 : 0,
					info 	= $('#cvwp-dm_info').is(':checked') ? 1 : 0,
					related = $('#cvwp-dm_related').is(':checked') ? 1 : 0;
				$(ref).data( 'dm_logo', logo );
				$(ref).data( 'dm_info', info );
				$(ref).data( 'dm_related', related );
				
				$(ref).empty();
				$(ref).cvwp_video();
			}
			
		})		
	}// customizeVideo
	
	var aspectRatio = function(){
		$(document).on('change', '.cvwp_video_aspect_ratio', function(){
			var aspect_ratio_input 	= this,
				parent				= $(this).parents('.cvwp-player-aspect-options'),
				width_input			= $(parent).find('.cvwp_video_width'),
				height_output		= $(parent).find('.cvwp_video_height');		
			
			var val = $(this).val(),
				w 	= Math.round( parseInt($(width_input).val()) ),
				h 	= 0;
			switch( val ){
				case '4x3':
					h = Math.floor((w*3)/4);
				break;
				case '16x9':
					h = Math.floor((w*9)/16);
				break;
				case '2.35x1':
					h = Math.floor(w/2.35);
				break;	
				case '1x1':
					h = w;
				break;	
			}
			$(height_output).html(h);						
		});
		
		
		$(document).on( 'keyup mouseup', '.cvwp_video_width', function(){
			var parent				= $(this).parents('.cvwp-player-aspect-options'),
				aspect_ratio_input	= $(parent).find('.cvwp_video_aspect_ratio');		
						
			if( '' == $(this).val() ){
				return;				
			}
			var val = Math.round( parseInt( $(this).val() ) );
			$(this).val( val );	
			$(aspect_ratio_input).trigger('change');
		});
		
	}// aspectRatio
	
	var cvwp_clear_editor = function( text ) {

		if( is_gutenberg() /*typeof wp.data !== 'undefined'*/ ){
			clear_gutenberg_editor( text );
		}else{
			clear_tinymce_editor( false, text );
		}


	};

	var clear_tinymce_editor = function( edId, text ){
		var editor,
			hasTinymce = typeof tinymce !== 'undefined',
			hasQuicktags = typeof QTags !== 'undefined';

		if ( ! wpActiveEditor ) {
			if ( hasTinymce && tinymce.activeEditor ) {
				editor = tinymce.activeEditor;
				wpActiveEditor = editor.id;
			} else if ( ! hasQuicktags ) {
				return false;
			}
		} else if ( hasTinymce ) {
			editor = tinymce.get( edId || wpActiveEditor );
		}

		if ( editor && ! editor.isHidden() ) {
			editor.execCommand( 'mceSetContent', false, '' );
		} else {
			document.getElementById( edId || wpActiveEditor ).value = '';
		}

		window.send_to_editor( text );
	}

	var clear_gutenberg_editor = function( text ){
		wp.data.dispatch( 'core/block-editor' ).resetBlocks([]);
		var block = wp.blocks.createBlock( 'core/paragraph', { content: text } );
		wp.data.dispatch( 'core/block-editor' ).insertBlock( block );
	}
	
	var is_gutenberg = function(){
		return '1' == cvwp_query.is_gutenberg ;
	}

})(jQuery);