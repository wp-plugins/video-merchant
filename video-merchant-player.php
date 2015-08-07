<?php
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

defined('ABSPATH') or die('No direct access!');

function video_merchant_enqueue_frontend_player_scripts() {
    global $wp_scripts;
	
    $wp_scripts->queue = array();
	
	wp_enqueue_script('video-merchant-player-js', video_merchant_make_url_protocol_less(plugins_url('video-merchant-html-player-frontend.js', __FILE__)), array('jquery', 'jquery-ui-core', 'jquery-ui-tooltip', 'jquery-ui-slider'));
}

function video_merchant_enqueue_frontend_player_styles() {
    global $wp_styles;
	
    $wp_styles->queue = array();
	
	wp_enqueue_style('jquery-ui-css', video_merchant_make_url_protocol_less(plugins_url('jquery-ui.css', __FILE__)));
	wp_enqueue_style('video-merchant-frontend-css', video_merchant_make_url_protocol_less(plugins_url('video-merchant-html-player-frontend.css', __FILE__)), array('jquery-ui-css'));
}

add_action('wp_print_styles', 'video_merchant_enqueue_frontend_player_styles', 1000);
add_action('wp_print_scripts', 'video_merchant_enqueue_frontend_player_scripts', 1000);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<?php wp_head(); ?>
<script type="text/javascript">
	videoMerchantPlayer.imgPathUrl = '<?php echo video_merchant_make_url_protocol_less(plugins_url('images/', __FILE__)); ?>';
</script>
</head>
<body>
	<div class="video_merchant_player">
		<div class="video_merchant_player_inner">
			<video class="video_player" controls>Your browser does not support HTML5 video.</video>
			<ul class="track_list">
				<?php 
				if(!empty($videoRecords)) 
				{
					foreach($videoRecords as $mediaRecord)
					{
						if(preg_match('@^https?://@i', $mediaRecord['video_file_preview']))
						{
							$previewMediaUrl = video_merchant_make_url_protocol_less($mediaRecord['video_file_preview']);
						}
						else
						{
							$previewMediaUrl = video_merchant_make_url_protocol_less($uploadUrl.'/'.$mediaRecord['video_file_preview']);
						}
				?>
				<li class="video_<?php echo $mediaRecord['video_id']; ?>" onclick="javascript: videoMerchantPlayer.play(<?php echo $mediaRecord['video_id']; ?>);">
					<?php 
					if(!empty($mediaRecord['video_cover_photo'])) 
					{ 
						if(preg_match('@^https?://@i', $mediaRecord['video_cover_photo']))
						{
							$imgUrl = video_merchant_make_url_protocol_less($mediaRecord['video_cover_photo']);
						}
						else
						{
							$imgUrl = video_merchant_make_url_protocol_less($uploadUrl.'/'.$mediaRecord['video_cover_photo']);
						}
					?>
					<img class="video_thumb" src="<?php echo $imgUrl; ?>" alt="" border="0" />
					<?php
					}
					?>
					<?php echo $mediaRecord['video_display_name']; ?>
					<div class="clr"></div>
					<?php if($mediaRecord['video_lease_price'] == 0.00 && $mediaRecord['video_exclusive_price'] == 0.00) { ?>
						<?php if((int)video_merchant_get_setting('download_user_login_required') > 0 && !is_user_logged_in()) { ?>
						<a href="<?php echo wp_login_url($currentUrl); ?>" target="_top" onclick="javascript: event.stopPropagation(); alert('<?php htmlentities(_e('Please login or register to continue... You will now be redirected...', 'video-merchant'), ENT_QUOTES); ?>');" title="<?php _e('Download FREE!', 'video-merchant'); ?>"><img class="download_icon" align="middle" src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/download_icon.png', __FILE__)); ?>" alt="" border="0" /></a>
						<?php } else { ?>
						<a onclick="javascript: event.stopPropagation();" href="<?php echo admin_url('admin-ajax.php?action=video_merchant_download_free&amp;video_id='.(string)$mediaRecord['video_id']); ?>" title="<?php _e('Download FREE!', 'video-merchant'); ?>"><img class="download_icon" align="middle" src="<?php echo video_merchant_make_url_protocol_less(plugins_url('images/download_icon.png', __FILE__)); ?>" alt="" border="0" /></a>
						<?php } ?>
					<?php } ?>
					
					<input type="hidden" name="video_preview_url" value="<?php echo $previewMediaUrl; ?>" />
					<input type="hidden" name="video_id" value="<?php echo $mediaRecord['video_id']; ?>" />
				</li>
				<?php
					}
				?>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						videoMerchantPlayer.autoPlay = <?php echo ((int)@$_GET['autoplay'] > 0) ? 'true' : 'false'; ?>;
					});
				</script>
				<?php
				}
				?>
			</ul>
		</div>
	</div>
<?php wp_footer(); ?>
</body>
</html>