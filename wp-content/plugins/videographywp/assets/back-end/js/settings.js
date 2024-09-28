/**
 * 
 */
;(function($){
	
	$(document).ready(function(){
		// tabs
		$.each( $('.cvwp-tabs'), function(){
			var storageVar 	= $(this).data('storage_var'),
				allowed		= typeof(Storage) !== 'undefined';
			
			if( !storageVar || !allowed ){
				var data = {};
			}else{
				var data = {
					active : sessionStorage[ storageVar ],
					activate : function( event, ui ){
						$(ui.newTab).find('i')
							.removeClass('dashicons-arrow-right')	
							.addClass('dashicons-arrow-down');
						
						$(ui.oldTab).find('i')
							.addClass('dashicons-arrow-right')	
							.removeClass('dashicons-arrow-down');
						
						sessionStorage[ storageVar ] = ui.newTab.index();
					},
					create: function(event, ui){
						$(ui.tab).find('i')
							.removeClass('dashicons-arrow-right')	
							.addClass('dashicons-arrow-down');
					}
				};
			}			
			$(this).tabs( data );
		});
		
		// end tabs
		
		var checkbox = $('.toggler').find('input[type=checkbox]');
		$.each(checkbox, function(i, ch){
			var tbl 		= $(this).parents('table'),
				selector 	= $(this).data('selector'),
				tr 	= selector ? $(tbl).find(selector) : $(tbl).find('tr.toggle'),
				on 	= $(this).data('action_on') || 'show';
			
			if( !$(this).is(':checked') ){
				if( 'show' == on ){
					$(tr).hide();
				}	
			}else{
				if( 'hide' == on ){
					$(tr).hide();
				}
			}	
			
			$(this).click(function(){
				if( $(ch).is(':checked') ){
					if( 'show' == on ){
						$(tr).show(400);
					}else{
						$(tr).hide(200);
					}	
				}else{
					if( 'show' == on ){
						$(tr).hide(200);
					}else{
						$(tr).show(400);
					}	
				}
			});
		});

		var querying = false,
			ajax = CVWP_SETTINGS_DATA.ajax,
			messages = CVWP_SETTINGS_DATA.messages;

		$('#apis-youtube-key').on( 'keyup', resetMessage )

		$('#cvwp-test-yt-key').on( 'click', function(e) {
			e.preventDefault();

			var apiKey = $('#apis-youtube-key').val();

			if( '' == apiKey ){
				setMessage( messages.empty, 'errorMessage' );
				return;
			}

			// a query is already running, bail out
			if( querying ){
				setMessage( messages.still_loading, 'loading' );
				return;
			}

			// set as processing the query
			querying = true;
			setMessage( messages.loading, 'loading' );

			// set the request data
			var data = { action: ajax.action };
			data[ ajax.nonce.name ] = ajax.nonce.value;
			data.api_key = apiKey;
			
			// make AJAX request
			$.ajax({
				'url' 		: ajaxurl,
				'data' 		: data,
				'dataType' 	: 'json',
				'type' 		: 'POST',
				'success' : function( json ){
					querying = false;
					var text = json.data.message;
						cssClass = 'success';
					if( !json.success ){
						cssClass = 'errorMessage';
						text += '<p>' + json.data.data + '</p>';
					}
					setMessage( text, cssClass );
				},
				'error': function(){
					querying = false;
					setMessage( messages.error, 'errorMessage' );
				}
			});

		})
		
	});

	/**
	 * Set a message and add a CSS class to messages div
	 */
	var setMessage = function( message, addClass ){
		$('#cvwp-test-yt-key-response')
			.empty()
			.attr({'class' : addClass + ' has-message'})
			.html( message );
	}

	var resetMessage = function(){
		setMessage('', 'idle')
	}

})(jQuery);