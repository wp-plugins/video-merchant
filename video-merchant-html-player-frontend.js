/**
 * @package Video Merchant
 * @version 5.0.4
 * @author Video Merchant <info@MyVideoMerchant.com>
 * @copyright (C) Copyright 2015 Video Merchant, MyVideoMerchant.com. All rights reserved.
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.txt

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Create Base64 Object
var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

jQuery(document).ready(function() {
    videoMerchantPlayer.init();
});

var videoMerchantPlayer = {
	imgPathUrl: '',
	autoPlay: false,
	
	init: function () {
		jQuery('.video_player')[0].volume = 1;
		jQuery('.video_player').prop('muted', false);
		
		if(jQuery('.track_list li:first-child input[name="video_preview_url"]').val()) {
			jQuery('.track_list li:first-child').addClass('now_playing');
			
			var autoPlayFunc = function () {
				if(videoMerchantPlayer.autoPlay) {
					jQuery('.video_player')[0].play();
				}
				
				jQuery('.video_player').off('loadedmetadata', autoPlayFunc);
			};
			
			jQuery('.video_player').on('loadedmetadata', autoPlayFunc);
			
			jQuery('.video_player').attr('src', jQuery('.track_list li:first-child input[name="video_preview_url"]').val());
			jQuery('.video_player')[0].load();
		}
		
		jQuery('.video_player').on('ended', videoMerchantPlayer.playNextMedia);
		
		jQuery(document).tooltip({
			track: true,
			hide: {effect: 'fade', duration: 1},
			content: function () {
				if (jQuery(this).attr('data-title')) {
					return jQuery(this).attr('data-title');
				} else {
					return jQuery(this).attr('title');
				}
			}
		});
	},
	
	playNextMedia: function () {
		var dontAutoPlay = false;
		var nextMediaId = jQuery('.now_playing').next().find('input[name="video_id"]').val();
		
		if (!nextMediaId) {
			dontAutoPlay = true;
			nextMediaId = jQuery('.track_list li:first-child input[name="video_id"]').val();
		}
		
		videoMerchantPlayer.play(nextMediaId, dontAutoPlay);
	},
	
	play: function (mediaId, dontAutoPlay) {
		jQuery('.track_list li').removeClass('now_playing');
		jQuery('.video_'+String(mediaId)).addClass('now_playing');
		
		if (!dontAutoPlay) {
			var playFunc = function () {
				jQuery('.video_player')[0].play();

				jQuery('.video_player').off('loadedmetadata', playFunc);
			};
			
			jQuery('.video_player').on('loadedmetadata', playFunc);
		}
		
		jQuery('.video_player').attr('src', jQuery('.video_'+String(mediaId)+' input[name="video_preview_url"]').val());
		jQuery('.video_player')[0].load();
	}
};