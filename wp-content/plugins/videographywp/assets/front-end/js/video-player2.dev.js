/**
 * @copyright CodeFlavors (https://codeflavors.com)
 */
;(function($){
	
	$.fn.cvwp_video = function( options ){
		
		if( 0 == this.length ){			
			return false; 
		}
		// support multiple elements
	   	if (this.length > 1){
	        this.each(function( i, e ) {	        	
				$(e).cvwp_video(options);				
			});
	        return this;
	   	}
		
	   	var defaults = {
	   		onLoad : function(){},
	   		onPlay : function(){},
	   		onStop : function(){},
	   		onPause: function(){},
	   		// backwards compatible actions
	   		onFinish: function(){},
	   		stateChange: function(){}
	   	};
	   	
	   	var self 	= this,
	   		options = $.extend({}, defaults, options),
	   		player	= false,
	   		status,
	   		child;
	   	
	   	var init = function(){
	   		
	   		if( self.data('lazy_load') ){	   			
	   			$(self).click(function(e){
	   				e.preventDefault();
	   				child = $(self).children().hide();
	   				load_player();	   				
	   			});
	   			
	   		}else{	   		
	   			load_player();
	   		}	
	   		
	   		// set the video size
	   		__resize();
	   		$(window).resize( __resize );
	   		
	   		return self;
	   	}
	   	
	   	var load_player = function(){
	   		if( !player ){	   		
		   		switch( $(self).data( 'source' ) ){
		   			case 'youtube':
		   				player = $(self).youtubeVideo({
		   					onLoad 	: on_load,
		   					onPlay	: on_play,
		   					onStop 	: on_stop,
		   					onPause : on_pause
		   				});
		   			break;
		   			case 'vimeo':
	   					player = $(self).vimeoVideo({
		   					onLoad 	: on_load,
		   					onPlay	: on_play,
		   					onStop 	: on_stop,
		   					onPause : on_pause
		   				});
		   			break;
		   			default:
		   				var func = $(self).data('source') + 'Video';
		   				if( $.fn[ func ] ){
		   					player = $.fn[func].call( self, {
		   						onLoad 	: on_load,
			   					onPlay	: on_play,
			   					onStop 	: on_stop,
			   					onPause : on_pause
		   					});
		   				}else{
		   					if( console ){
		   						console.warn( 'No implementation for video source "' + self.data('source') + '".' );
		   					}
		   				}
		   			break;	
		   		}
	   		}	   		
	   	}
	   	
	   	/**
         * Calculates ratio for responsive videos
         * @private
         */
        var __resize = function () {
        	var width = $(self).width(),
				height;
			
			switch( $(self).data('aspect') ){
				case '16x9':
				default:
					height = (width *  9) / 16;
				break;
				case '4x3':
					height = (width *  3) / 4;
				break;
				case '2.35x1':
					height = width / 2.35; 
				break;
				case '1x1':
					height = width;
				break;	
			}
			
			$(self).css({ height : Math.floor( height ) } );
        }
	   	
        var __change_status = function( s ){
        	status = s;
        	options.stateChange.call( self, status );        	
        };
        
        var __get_status = function(){
        	return status;
        }
        
	   	// events
	   	var on_load = function(){
	   		if( !player ){
	   			player = this;
	   		}
	   		__change_status( 1 );
	   		if( self.data('lazy_load') ){
	   			if( !cvwp_is_mobile() ){
	   				play();
	   			}	
	   		}	   		
	   		options.onLoad.call( self, status );	   		
	   	};
	   	var on_play = function(){
	   		__change_status( 2 );
	   		options.onPlay.call( self, status );	   		
	   	};
	   	var on_stop = function(){
	   		__change_status( 4 );
	   		options.onStop.call( self, status );
	   		options.onFinish.call( self, status );
	   	};
	   	var on_pause = function(){
	   		__change_status( 3 );
	   		options.onPause.call( self, status );
	   	};
	   	
	   	// actions
	   	var play = function(){
	   		// if loaded with autoplay, check the status in order not to issue errors
	   		if( !status ){
	   			setTimeout( play, 1000 );
	   			return;
	   		}
	   		player.play();	   		
	   		__change_status( 2 );
	   		
	   	};
	   	var pause = function(){
	   		player.pause();
	   		__change_status( 3 );
	   		
	   	};
	   	var stop = function(){
	   		player.stop();
	   		__change_status( 4 );
	   	};
	   	
	   	// methods
	   	this.play = function(){
	   		play();
	   	};
	   	this.pause = function(){
	   		pause();
	   	};
	   	this.stop = function(){
	   		stop();
	   	}
	   	this.getStatus = function(){
	   		return __get_status();
	   	}
	   	this.resize = function(){
	   		__resize();
	   	}
	   	
	   	return init();
	}
	
})(jQuery);

/**
 * YouTube video embed
 */
;(function($){
	
	var yt_api_loaded = false;
	
	$.fn.youtubeVideo = function( options ){
		if( 0 == this.length ){ 
			return false; 
		}
		// support multiple elements
	   	if (this.length > 1){
	        this.each(function( i, e ) {
	        	$(e).youtubeVideo(options);				
			});
	        return this;
	   	}
		
	   	var defaults = {
	   		onLoad : function(){},
	   		onPlay : function(){},
	   		onStop : function(){},
	   		onPause: function(){}
	   	};
		
		var self 	= this,
	   		options = $.extend({}, defaults, options),
	   		player = false,
	   		status,
	   		player_id;
		
		
		var init = function(){ 	
			if( yt_api_loaded ){
				__load_video();
			}else{
				$(window).on( 'youtubeapiready', function(){
					__load_video();								
				})
			}
						
			__load_yt_api();
			return self;
		}
		
		var __load_video = function(){
			if( typeof YT === 'undefined' ){
				setTimeout( __load_video, 1000 );
				return;
			}
			
			self.prepend('<div/>');
			
			var params = {
				'enablejsapi'	: 1,
				'rel'			: 0, // show related
				'showinfo'		: 0, // show info
				'showsearch'	: 0, // show search	
				// optional	
				'modestbranding' : self.data('modestbranding') || 0,
				'iv_load_policy' : self.data('iv_load_policy') || 0,
				'autohide' 		 : self.data('autohide') || 0,
				'controls'		 : self.data('controls') || 0,
				'fs'	 		 : self.data('fullscreen') || 0,
				'loop'			 : /* self.data('loop') || */ 0
			};
			
			var cookieless = self.data('nocookie') ? '-nocookie' : '';

			player = new YT.Player(self.children(':first')[0], {
                height		: '100%',
                width		: '100%',
                videoId		: self.data('video_id'),
                host		: 'https://www.youtube' + cookieless + '.com',
                playerVars	: params,
                events: {
                	'onReady': function( event ){
   						options.onLoad.call( self );
                		set_volume();
                		 //player = event.target;
   						// player.setVolume(options.volume);
   						// self.updateStatus(1); 
   					 },
                    'onStateChange': function (data) {
                        switch ( window.parseInt(data.data, 10) ) {
                        // ended
                        case 0:
                        	if( self.data('loop') == 1 ){
                        		player.playVideo();
                        	}

			    			options.onStop.call( self );
                        break;
                        // playing
                        case 1:
                        	options.onPlay.call( self );
                        break;
                        // paused
                        case 2:
			    			options.onPause.call( self );
			    		break;
                        }
                    } 
                }
            });			
		};
		
		var __load_yt_api = function(){
			if( yt_api_loaded ){
				return;
			}
			
			yt_api_loaded = true;
			
			var element = document.createElement('script'),
	            scriptTag = document.getElementsByTagName('script')[0];
	
	        element.async = true;
	        element.src = "https://www.youtube.com/iframe_api";
	        scriptTag.parentNode.insertBefore(element, scriptTag);

	        // run an interval in case other scripts try to use the onYouTubeIframeAPIReady() callback
	        var max_runs = 25,
	        	interval = setInterval( function(){
	        		// allow the interval to run for maximum 25 times ( 5 seconds )
					if( 0 == max_runs ){
						clearInterval( interval );
					}
					// check for YT variable
					if( typeof YT !== 'undefined' ){
						if( 1 === YT.loaded ){
							$(window).trigger('youtubeapiready');
							clearInterval( interval );
						}
					}
					max_runs--;
				}, 200 );

			// set the API callback; check first if other scripts haven't set YouTube API callback
			if( typeof onYouTubeIframeAPIReady == 'undefined' ){
                window.onYouTubeIframeAPIReady = function () {
                    $(window).trigger('youtubeapiready');
                        clearInterval( interval );
                };
			}
		};
		
		var set_volume = function(){			
			player.setVolume( self.data('volume') );
		};
		
		this.play = function(){
			player.playVideo();			
		};
		this.pause = function(){
			player.pauseVideo();			
		};
		this.stop = function(){
			player.stopVideo();
		};
		
		return init();
	}
	
})(jQuery);

;(function($){
	$(window).on( 'load',
		function(){
			if( typeof cvwp_video_options !== 'undefined' ){
				$( cvwp_video_options.embed ).cvwp_video();
			}
			
			var win_w = $(window).width() - 100;
			
			// video buttons
			$('.cvwp-video-button').click(function(e){
				e.preventDefault();				
				if( !$(this).data('cvwp_id') ){
					var data 	= $(this).data(),					
						div_id 	= 'cvwp-video-modal-' + ( Math.floor(Math.random() * (9999 - 1 + 1)) + 1 );
					
					var player_div = $('<div />', {
						'class' : 'cvwp-video-player',
						'css' : {
							'width' 	: data.width,
							'max-width' : '100%',
							'height' 	: data.height
						}
					}).data( data );
					
					var div = $('<div />', {
						'class' : 'cvwp-video-modal',
						'html' : '<!-- cvwp script generated element -->',
						'id' : div_id,
						'css' :{
							'width' 	: ( data.width > win_w ? win_w : data.width )
						}
					}).data( 'max_width', data.width ).append( player_div );
					// insert div
					$(this).after(div);
					// start player
					var p = $(player_div).cvwp_video();
					
					$(this).data('cvwp_id', div_id);
					$(div).data('cvwp_player', p);
				}
				
				$( '#' + $(this).data('cvwp_id') ).modal({
					modalClass : 'cvwp_modal',
					zIndex:999999
				});				
			});
			
			if( typeof $.modal !== 'undefined' ){
				var cvwp_mod_win;
				$(document).on( $.modal.BEFORE_CLOSE, function(event, modal){
					var el = modal.elm,
						player = $(el).data('cvwp_player');
					player.pause();
					cvwp_mod_win = false;
				});
			
				$(document).on($.modal.OPEN, function(event, modal){
					var el = modal.elm,
						player = $(el).data('cvwp_player');
					player.play();
					cvwp_mod_win = modal;
					
					var win_w = $(window).width() - 100,
						max_w = $(el).data('max_width');
					if( max_w < win_w ){
						$(el).css({ 'width' : max_w });
					}else{
						$(el).css({ 'width' : win_w });
					}
					setTimeout( function(){ $.modal.resize(); }, 2 );
				});
				
				$(window).resize(function(){
					var win_w = $(window).width() - 100;
					if( !cvwp_mod_win ){
						return;
					}
					
					var el = cvwp_mod_win.elm,
						max_w = $(el).data('max_width');
					
					if( max_w < win_w ){
						$(el).css({ 'width' : max_w });
					}else{
						$(el).css({ 'width' : win_w });
					}
					$.modal.resize();
				});				
			}
		}
	);

	window.cvwp_is_mobile = function() {
		var check = false;
		(function(a) {
			if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) check = true
		})(navigator.userAgent || navigator.vendor || window.opera);
		return check;
	}

})(jQuery);